<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\CaseFile;
use App\Models\IntegratedSearch;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Visitor;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class GenerateDashboardReport extends Command
{
    protected $signature = 'report:dashboard {--start-date=} {--end-date=}';
    protected $description = 'Generate dashboard report CSV for each agency';

    public function handle()
    {
        $this->info('Starting dashboard report generation...');

        // Get date range from options or default to last 6 months
        $startDate = $this->option('start-date')
            ? Carbon::parse($this->option('start-date'))
            : Carbon::now()->subMonths(6)->startOfMonth();

        $endDate = $this->option('end-date')
            ? Carbon::parse($this->option('end-date'))
            : Carbon::now()->endOfMonth();

        // Generate filename with today's date
        $reportDate = Carbon::now()->format('Y-m-d');
        $fileName = "{$reportDate}_dashboard_report.csv";
        $filePath = "reports/{$fileName}";

        // Create reports directory if it doesn't exist
        if (!Storage::exists('reports')) {
            Storage::makeDirectory('reports');
        }

        // Initialize CSV content
        $csvContent = [];

        // Get all tenants excluding specific ones
        $tenants = Tenant::whereNotIn('id', ['cims_p_491', 'cims_central'])->get();

        // Generate months array based on date range
        $months = [];
        $current = $startDate->copy();

        while ($current->lte($endDate)) {
            $months[] = $current->format('M Y');
            $current->addMonth();
        }

        // Reverse to show oldest month first
        $months = array_reverse($months);

        // Add header row for monthly statistics
        $headerColumns = [
            'Agency',
            'Metric Type',
            'Total',
        ];

        // Add month columns to header
        foreach ($months as $month) {
            $headerColumns[] = $month;
        }

        $csvContent[] = ['MONTHLY STATISTICS'];
        $csvContent[] = $headerColumns;

        // Store all integrated searches and case files for detailed section
        $allIntegratedSearches = [];
        $allCaseFiles = [];

        // Process each tenant
        foreach ($tenants as $tenant) {
            $this->info("Processing agency: {$tenant->agency_name}");

            // Initialize tenant connection
            tenancy()->initialize($tenant);

            $searchesByMonths = [];
            $profilingsByMonths = [];

            // Collect monthly data
            foreach ($months as $month) {
                $monthDate = Carbon::parse($month);

                $searchCount = IntegratedSearch::whereYear('created_at', $monthDate->year)
                    ->whereMonth('created_at', $monthDate->month)
                    ->count();

                $profilingCount = CaseFile::whereYear('created_at', $monthDate->year)
                    ->whereMonth('created_at', $monthDate->month)
                    ->count();

                $searchesByMonths[] = $searchCount;
                $profilingsByMonths[] = $profilingCount;
            }

            // Get detailed integrated searches for this tenant
            $integratedSearches = IntegratedSearch::whereNotNull('request_body')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->orderBy('created_at', 'desc')
                ->get(['id', 'user_id', 'request_body', 'agency_id', 'created_at']);

            foreach ($integratedSearches as $search) {
                // Get user from the tenant database
                $user = null;
                if ($search->user_id) {
                    $user = User::find($search->user_id);
                }

                // Extract all data we need before ending tenant connection
                $allIntegratedSearches[] = [
                    'agency' => $tenant->agency_name,
                    'search_id' => $search->id,
                    'user_id' => $search->user_id,
                    'user_name' => $user ? $user->name : 'N/A',
                    'user_email' => $user ? $user->email : 'N/A',
                    'request_body' => $search->request_body,
                    'created_at' => $search->created_at->format('Y-m-d H:i:s'),
                    'created_at_timestamp' => $search->created_at->timestamp
                ];
            }

            // Get detailed case files for this tenant with users
            $caseFiles = CaseFile::with('users')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->orderBy('created_at', 'desc')
                ->get(['id', 'case_ref_id', 'title as case_title', 'case_category', 'case_description', 'created_at']);

            foreach ($caseFiles as $caseFile) {
                // Get the owner user (first user with 'owner' status in pivot)
                $owner = $caseFile->users->where('pivot.status', 'owner')->first();

                // If no owner, get the first user
                if (!$owner && $caseFile->users->count() > 0) {
                    $owner = $caseFile->users->first();
                }

                // Extract all data we need before ending tenant connection
                $allCaseFiles[] = [
                    'agency' => $tenant->agency_name,
                    'case_id' => $caseFile->id,
                    'case_ref_id' => $caseFile->case_ref_id,
                    'case_title' => $caseFile->case_title,
                    'case_category' => $caseFile->case_category,
                    'case_description' => $caseFile->case_description,
                    'owner_name' => $owner ? $owner->name : 'N/A',
                    'owner_email' => $owner ? $owner->email : 'N/A',
                    'created_at' => $caseFile->created_at->format('Y-m-d H:i:s'),
                    'created_at_timestamp' => $caseFile->created_at->timestamp
                ];
            }

            // Add searches row for this agency
            $searchRow = [
                $tenant->agency_name,
                'Searches',
                array_sum($searchesByMonths),
            ];
            foreach ($searchesByMonths as $count) {
                $searchRow[] = $count;
            }
            $csvContent[] = $searchRow;

            // Add profilings row for this agency
            $profilingRow = [
                $tenant->agency_name,
                'Profilings',
                array_sum($profilingsByMonths),
            ];
            foreach ($profilingsByMonths as $count) {
                $profilingRow[] = $count;
            }
            $csvContent[] = $profilingRow;

            // End tenant connection
            tenancy()->end();
        }

        // Add summary section
        $csvContent[] = []; // Empty row for separation
        $csvContent[] = ['SUMMARY STATISTICS'];
        $csvContent[] = ['Period', $startDate->format('Y-m-d'), 'to', $endDate->format('Y-m-d')];
        $csvContent[] = [];

        // Switch to central database for user statistics
        DB::setDefaultConnection('mysql');

        // User statistics
        $totalUsers = User::on('mysql')->count();
        $totalSystemAdmins = User::on('mysql')->role('system admin')->count();
        $totalRegistered = User::on('mysql')->whereBetween('created_at', [$startDate, $endDate])->count();

        $csvContent[] = ['Total Users', $totalUsers];
        $csvContent[] = ['Total System Admins', $totalSystemAdmins];
        $csvContent[] = ['New Registrations (Period)', $totalRegistered];

        // Calculate overall totals
        $totalSearches = 0;
        $totalProfilings = 0;

        foreach ($csvContent as $row) {
            if (isset($row[1]) && $row[1] === 'Searches') {
                $totalSearches += $row[2];
            }
            if (isset($row[1]) && $row[1] === 'Profilings') {
                $totalProfilings += $row[2];
            }
        }

        $csvContent[] = [];
        $csvContent[] = ['Total Searches (All Agencies)', $totalSearches];
        $csvContent[] = ['Total Profilings (All Agencies)', $totalProfilings];

        // User engagement by month
        $csvContent[] = [];
        $csvContent[] = ['USER ENGAGEMENT BY MONTH'];

        $engagementHeader = ['Month', 'Logged In Users', 'Registered Users'];
        $csvContent[] = $engagementHeader;

        $current = $startDate->copy();
        while ($current->lte($endDate)) {
            $loggedIn = Visitor::on('mysql')
                ->whereMonth('created_at', $current->month)
                ->whereYear('created_at', $current->year)
                ->distinct('user_id')
                ->count('user_id');

            $registered = User::on('mysql')
                ->whereDate('created_at', '<=', $current->endOfMonth())
                ->count();

            $csvContent[] = [
                $current->format('M Y'),
                $loggedIn,
                $registered
            ];

            $current->addMonth();
        }

        // Detailed Integrated Search Section
        $csvContent[] = [];
        $csvContent[] = ['DETAILED INTEGRATED SEARCHES'];
        $csvContent[] = [
            'Agency',
            'Search ID',
            'User ID',
            'User Name',
            'User Email',
            'NRIC',
            'Name',
            'Passport',
            'Phone Number',
            'Bank Account',
            'Company Name',
            'Company Reg No',
            'Selected Agencies',
            'Date/Time'
        ];

        // Sort all integrated searches by date
        usort($allIntegratedSearches, function($a, $b) {
            return $b['created_at_timestamp'] - $a['created_at_timestamp'];
        });

        foreach ($allIntegratedSearches as $item) {
            // Parse request body
            $requestData = is_string($item['request_body'])
                ? json_decode($item['request_body'], true)
                : $item['request_body'];

            // Extract selected agencies from the agencies array
            $selectedAgencies = [];
            if (isset($requestData['agencies']) && is_array($requestData['agencies'])) {
                foreach ($requestData['agencies'] as $agencyItem) {
                    if (isset($agencyItem['value']) && $agencyItem['value'] === true) {
                        $selectedAgencies[] = $agencyItem['id'] ?? $agencyItem['title'] ?? 'Unknown';
                    }
                }
            }

            $csvContent[] = [
                $item['agency'],
                $item['search_id'],
                $item['user_id'],
                $item['user_name'],
                $item['user_email'],
                $requestData['nric'] ?? 'N/A',
                $requestData['name'] ?? 'N/A',
                $requestData['passport'] ?? 'N/A',
                $requestData['phoneNumber'] ?? 'N/A',
                $requestData['bankAccount'] ?? 'N/A',
                $requestData['companyBusinessName'] ?? 'N/A',
                $requestData['companyRegistrationNumber'] ?? 'N/A',
                implode(', ', $selectedAgencies),
                $item['created_at']
            ];
        }

        $csvContent[] = [];
        $csvContent[] = ['Total Integrated Searches', count($allIntegratedSearches)];

        // Detailed Case Files Section
        $csvContent[] = [];
        $csvContent[] = ['DETAILED CASE FILES'];
        $csvContent[] = [
            'Agency',
            'Case ID',
            'Case Ref ID',
            'Owner Name',
            'Owner Email',
            'Case Title',
            'Case Category',
            'Case Description',
            'Date/Time'
        ];

        // Sort all case files by date
        usort($allCaseFiles, function($a, $b) {
            return $b['created_at_timestamp'] - $a['created_at_timestamp'];
        });

        foreach ($allCaseFiles as $item) {
            $csvContent[] = [
                $item['agency'],
                $item['case_id'],
                $item['case_ref_id'] ?? 'N/A',
                $item['owner_name'],
                $item['owner_email'],
                $item['case_title'] ?? 'N/A',
                $item['case_category'] ?? 'N/A',
                $item['case_description'] ?? 'N/A',
                $item['created_at']
            ];
        }

        $csvContent[] = [];
        $csvContent[] = ['Total Case Files', count($allCaseFiles)];

        // Write CSV file
        $this->writeCSV($filePath, $csvContent);

        $this->info("Report generated successfully: storage/app/{$filePath}");

        return Command::SUCCESS;
    }

    private function writeCSV($filePath, $data)
    {
        $output = fopen('php://temp', 'r+');

        foreach ($data as $row) {
            fputcsv($output, $row);
        }

        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        Storage::put($filePath, $csvContent);
    }
}
