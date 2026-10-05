<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Visitor;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class GenerateVisitorReport extends Command
{
    protected $signature = 'report:visitors {--start-date=} {--end-date=} {--limit=5000}';
    protected $description = 'Generate detailed visitor report with user information';

    public function handle()
    {
        $this->info('Starting visitor report generation...');
        
        // Get date range from options or default to last 30 days
        $startDate = $this->option('start-date') 
            ? Carbon::parse($this->option('start-date')) 
            : Carbon::now()->subDays(30)->startOfDay();
        
        $endDate = $this->option('end-date') 
            ? Carbon::parse($this->option('end-date')) 
            : Carbon::now()->endOfDay();
            
        $limit = $this->option('limit') ?: 5000;
        
        // Generate filename with today's date
        $reportDate = Carbon::now()->format('Y-m-d');
        $fileName = "{$reportDate}_visitor_report.csv";
        $filePath = "reports/{$fileName}";
        
        // Create reports directory if it doesn't exist
        if (!Storage::exists('reports')) {
            Storage::makeDirectory('reports');
        }
        
        // Initialize CSV content
        $csvContent = [];
        
        // Add report header
        $csvContent[] = ['VISITOR REPORT'];
        $csvContent[] = ['Generated On', Carbon::now()->format('Y-m-d H:i:s')];
        $csvContent[] = ['Period', $startDate->format('Y-m-d'), 'to', $endDate->format('Y-m-d')];
        $csvContent[] = [];
        
        // Summary Statistics
        $csvContent[] = ['SUMMARY STATISTICS'];
        
        $totalVisits = Visitor::whereBetween('created_at', [$startDate, $endDate])
            ->count();
            
        $uniqueVisitors = Visitor::whereBetween('created_at', [$startDate, $endDate])
            ->distinct('user_id')
            ->count('user_id');
            
        $uniqueIPs = Visitor::whereBetween('created_at', [$startDate, $endDate])
            ->distinct('ip_address')
            ->count('ip_address');
        
        $csvContent[] = ['Total Visits', $totalVisits];
        $csvContent[] = ['Unique Visitors', $uniqueVisitors];
        $csvContent[] = ['Unique IP Addresses', $uniqueIPs];
        $csvContent[] = [];
        
        // Daily Visit Breakdown
        $csvContent[] = ['DAILY VISIT BREAKDOWN'];
        $csvContent[] = ['Date', 'Total Visits', 'Unique Visitors'];
        
        $current = $startDate->copy();
        while ($current->lte($endDate)) {
            $dayStart = $current->copy()->startOfDay();
            $dayEnd = $current->copy()->endOfDay();
            
            $dailyVisits = Visitor::whereBetween('created_at', [$dayStart, $dayEnd])
                ->count();
                
            $dailyUnique = Visitor::whereBetween('created_at', [$dayStart, $dayEnd])
                ->distinct('user_id')
                ->count('user_id');
            
            if ($dailyVisits > 0) {
                $csvContent[] = [
                    $current->format('Y-m-d'),
                    $dailyVisits,
                    $dailyUnique
                ];
            }
            
            $current->addDay();
        }
        
        $csvContent[] = [];
        
        // Top Visitors by Visit Count
        $csvContent[] = ['TOP VISITORS BY VISIT COUNT'];
        $csvContent[] = ['User ID', 'Name', 'Email', 'Visit Count', 'Last Visit'];
        
        $topVisitors = Visitor::select('user_id', DB::raw('COUNT(*) as visit_count'), DB::raw('MAX(created_at) as last_visit'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('user_id')
            ->orderBy('visit_count', 'desc')
            ->limit(50)
            ->get();
        
        foreach ($topVisitors as $visitor) {
            $user = User::find($visitor->user_id);
            $csvContent[] = [
                $visitor->user_id,
                $user ? $user->name : 'N/A',
                $user ? $user->email : 'N/A',
                $visitor->visit_count,
                Carbon::parse($visitor->last_visit)->format('Y-m-d H:i:s')
            ];
        }
        
        $csvContent[] = [];
        
        // Detailed Visitor Log
        $csvContent[] = ['DETAILED VISITOR LOG'];
        $csvContent[] = ['Visit ID', 'User ID', 'Name', 'Email', 'IP Address', 'User Agent', 'Visit Date/Time'];
        
        // Get visitor details with user information
        $visitors = Visitor::with('user:id,name,email')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get(['id', 'user_id', 'ip_address', 'user_agent', 'created_at']);
        
        foreach ($visitors as $visitor) {
            $csvContent[] = [
                $visitor->id,
                $visitor->user_id,
                $visitor->user ? $visitor->user->name : 'N/A',
                $visitor->user ? $visitor->user->email : 'N/A',
                $visitor->ip_address,
                $this->truncateUserAgent($visitor->user_agent),
                $visitor->created_at->format('Y-m-d H:i:s')
            ];
        }
        
        if ($visitors->count() >= $limit) {
            $csvContent[] = [];
            $csvContent[] = ["Note: Limited to {$limit} records. Use --limit option to increase."];
        }
        
        // IP Address Statistics
        $csvContent[] = [];
        $csvContent[] = ['TOP IP ADDRESSES'];
        $csvContent[] = ['IP Address', 'Visit Count', 'Unique Users'];
        
        $topIPs = Visitor::select('ip_address', DB::raw('COUNT(*) as visit_count'), DB::raw('COUNT(DISTINCT user_id) as unique_users'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('ip_address')
            ->orderBy('visit_count', 'desc')
            ->limit(20)
            ->get();
        
        foreach ($topIPs as $ip) {
            $csvContent[] = [
                $ip->ip_address,
                $ip->visit_count,
                $ip->unique_users
            ];
        }
        
        // User Agent Analysis
        $csvContent[] = [];
        $csvContent[] = ['BROWSER/DEVICE ANALYSIS'];
        $csvContent[] = ['User Agent', 'Visit Count'];
        
        $userAgents = Visitor::select('user_agent', DB::raw('COUNT(*) as count'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('user_agent')
            ->groupBy('user_agent')
            ->orderBy('count', 'desc')
            ->limit(20)
            ->get();
        
        foreach ($userAgents as $ua) {
            $csvContent[] = [
                $this->truncateUserAgent($ua->user_agent),
                $ua->count
            ];
        }
        
        // Write CSV file
        $this->writeCSV($filePath, $csvContent);
        
        $this->info("Visitor report generated successfully: storage/app/{$filePath}");
        $this->info("Total visits processed: {$totalVisits}");
        $this->info("Unique visitors: {$uniqueVisitors}");
        
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
    
    private function truncateUserAgent($userAgent)
    {
        if (!$userAgent) {
            return 'N/A';
        }
        
        // Truncate long user agent strings for readability
        return strlen($userAgent) > 100 
            ? substr($userAgent, 0, 97) . '...' 
            : $userAgent;
    }
}