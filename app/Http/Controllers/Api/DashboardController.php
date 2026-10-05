<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\CaseFile;
use App\Models\CaseProfile;
use App\Models\DataApplication;
use App\Models\IntegratedSearch;
use App\Models\ReportFile;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Visitor;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use OwenIt\Auditing\Models\Audit;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:dashboard read', ['only' => ['getInvestigator', 'getCoordinatorData', 'getCoordinatorSystem', 'getCoordinatorAgency', 'getStatistic', 'getSuperadmin']]);
    }

    public function getInvestigator(): JsonResponse
    {
        try {

            $confirmDataApplication = DataApplication::where('requestor_id', Auth::user()->id)->where('data_application_status_id', 1)->count();
            $processDataApplication = DataApplication::where('requestor_id', Auth::user()->id)->where('data_application_status_id', 5)->count();

            initializeTenant();

            $user = User::whereEmail(auth()->user()->email)->first();

            $topCards = [
                [
                    'id' => 1,
                    'theme' => 'primary',
                    'link' => '/intelligence-management',
                    'svg' => 'icon-briefcase',
                    'icon' => 'mdi-file',
                    'title' => 'Open File',
                    'number' => $user->caseFiles()->where('status_id', 1)->wherePivot('status', 'owner')->count(),
                ],
                [
                    'id' => 2,
                    'theme' => 'error',
                    'link' => '/intelligence-management',
                    'svg' => 'icon-briefcase',
                    'icon' => 'mdi-archive',
                    'title' => 'Closed File',
                    'number' => $user->caseFiles()->where('status_id', 2)->wherePivot('status', 'owner')->count(),
                ],
                [
                    'id' => 3,
                    'theme' => 'primary',
                    'link' => '/intelligence-management',
                    'svg' => 'icon-mailbox',
                    'icon' => 'mdi-file-plus',
                    'title' => 'New Application',
                    'number' => $confirmDataApplication,
                ],
                [
                    'id' => 4,
                    'theme' => 'warning',
                    'link' => '/intelligence-management',
                    'svg' => 'icon-mailbox',
                    'icon' => 'mdi-file-send',
                    'title' => 'Processed Application',
                    'number' => $processDataApplication,
                ],
            ];

            // Investigator : List of RecentActivity
            $recentactivity = AuditLog::where('user_id', Auth::user()->id)
                                ->orderBy('created_at', 'desc')
                                ->take(4)
                                ->get();

            return response()->json([
                'topcards' => $topCards,
                'recentactivity' => $recentactivity,
                'success' => 'OK'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }

    public function getCoordinatorData(): JsonResponse
    {
        try {
            $topCards = [
                [
                    'id' => 1,
                    'theme' => 'primary',
                    'link' => '/data-request',
                    'svg' => 'icon-mailbox',
                    'icon' => 'mdi-file-plus',
                    'title' => 'New Application',
                    'number' => DataApplication::where('receiver_agency_id', auth()->user()->agency_id)->where('data_application_status_id', 1)->count(),
                ],
                [
                    'id' => 2,
                    'theme' => 'warning',
                    'link' => '/data-request',
                    'svg' => 'icon-mailbox',
                    'icon' => 'mdi-file-send',
                    'title' => 'Processed Application',
                    'number' => DataApplication::where('receiver_agency_id', auth()->user()->agency_id)->where('receiver_id', auth()->user()->id)->where('data_application_status_id', 5)->count(),
                ],
                [
                    'id' => 3,
                    'theme' => 'success',
                    'link' => '/data-request',
                    'svg' => 'icon-mailbox',
                    'icon' => 'mdi-file-check',
                    'title' => 'Approved Application',
                    'number' => DataApplication::where('receiver_agency_id', auth()->user()->agency_id)->where('receiver_id', auth()->user()->id)->where('data_application_status_id', 3)->count(),
                ],
                [
                    'id' => 4,
                    'theme' => 'error',
                    'link' => '/data-request',
                    'svg' => 'icon-mailbox',
                    'icon' => 'mdi-file-cancel',
                    'title' => 'Rejected Application',
                    'number' => DataApplication::where('receiver_agency_id', auth()->user()->agency_id)->where('receiver_id', auth()->user()->id)->where('data_application_status_id', 4)->count(),
                ],
            ];

            // DataRequestAdmin : List of RecentActivity
            $recentactivity = AuditLog::where('user_id', Auth::user()->id)
                                ->orderBy('created_at', 'desc')
                                ->take(4)
                                ->get();

            return response()->json([
                'topcards' => $topCards,
                'recentactivity' => $recentactivity,
                'success' => 'OK'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }

    public function getCoordinatorSystem(Request $request): JsonResponse
    {
        try {

            $currentMonth = Carbon::now()->month;
            $totalAuthenticatedVisitors = Visitor::whereMonth('created_at', $currentMonth)
                ->distinct('user_id')
                ->count('user_id');

            $topCards = [
                [
                    'id' => 1,
                    'theme' => 'primary',
                    'link' => '/manage/users',
                    'svg' => 'icon-user-male',
                    'icon' => 'mdi-account-multiple',
                    'title' => 'Total Visitor',
                    'number' => $totalAuthenticatedVisitors,
                ],
                [
                    'id' => 2,
                    'theme' => 'secondary',
                    'link' => '/manage/users',
                    'svg' => 'icon-user-male',
                    'icon' => 'mdi-account',
                    'title' => 'Registered User',
                    'number' => User::get()->count(),
                ],
                [
                    'id' => 3,
                    'theme' => 'success',
                    'link' => '/manage/users',
                    'svg' => 'icon-user-male',
                    'icon' => 'mdi-account-check',
                    'title' => 'Active User',
                    'number' => User::where('active_status', 1)->count(), // Active Account
                ],
                [
                    'id' => 4,
                    'theme' => 'error',
                    'link' => '/manage/users',
                    'svg' => 'icon-user-male',
                    'icon' => 'mdi-account-cancel',
                    'title' => 'Inactive User',
                    'number' => User::where('active_status', 0)->count(), // Inactive Account
                ],
            ];

            // SysAdmin : List of RecentActivity (current user's activities)
            $recentactivity = AuditLog::where('user_id', Auth::user()->id)
                                ->orderBy('created_at', 'desc')
                                ->take(10)
                                ->get();

            // SysAdmin : List of UserActivity (other users' activities, excluding superadmins)
            // Get superadmin user IDs to exclude them
            $superadminIds = User::role('super admin')->pluck('id')->toArray();
            
            $useractivity = AuditLog::whereNot('user_id', Auth::user()->id)
                                ->whereNotIn('user_id', $superadminIds)
                                ->orderBy('created_at', 'desc')
                                ->with('user')
                                ->take(10)
                                ->get();

            // Get date range from request or default to last 6 months
            $startDate = $request->has('start_date') ? Carbon::parse($request->start_date) : Carbon::now()->subMonths(6)->startOfMonth();
            $endDate = $request->has('end_date') ? Carbon::parse($request->end_date) : Carbon::now()->endOfMonth();

            // Get monthly user engagement data
            $months = [];
            $loggedInUsers = [];
            $registeredUsers = [];

            $current = $startDate->copy();
            while ($current->lte($endDate)) {
                $months[] = $current->format('M');

                // Count logged-in users (visitors) for the month
                $loggedIn = Visitor::whereMonth('created_at', $current->month)
                    ->whereYear('created_at', $current->year)
                    ->distinct('user_id')
                    ->count('user_id');
                $loggedInUsers[] = $loggedIn;

                // Count registered users up to that month
                $registered = User::whereDate('created_at', '<=', $current->endOfMonth())
                    ->count();
                $registeredUsers[] = $registered;

                $current->addMonth();
            }

            // Calculate totals for the labels
            $totalLoggedIn = array_sum($loggedInUsers);
            // Count registered users only within the date range
            $totalRegistered = User::whereBetween('created_at', [$startDate, $endDate])->count();

            // Note: Intelligent search data is tenant-specific and cannot be accessed from central database
            // For system admin, we'll show aggregate data across all tenants if needed in the future

            return response()->json([
                'topcards' => $topCards,
                'recentactivity' => $recentactivity,
                'useractivity' => $useractivity,
                'userEngagement' => [
                    'months' => $months,
                    'loggedInUsers' => $loggedInUsers,
                    'registeredUsers' => $registeredUsers,
                    'totalLoggedIn' => $totalLoggedIn,
                    'totalRegistered' => $totalRegistered
                ],
                'success' => 'OK'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }

    public function getCoordinatorAgency(): JsonResponse
    {
        try {
            initializeTenant();

            $topCards = [
                [
                    'id' => 1,
                    'theme' => 'primary',
                    'link' => '/manage/users',
                    'svg' => 'icon-user-male',
                    'icon' => 'mdi-account',
                    'title' => 'Registered User',
                    'number' => User::get()->count(), // select from same agency
                ],
                [
                    'id' => 2,
                    'theme' => 'success',
                    'link' => '/manage/users',
                    'svg' => 'icon-user-male',
                    'icon' => 'mdi-account-check',
                    'title' => 'Active User',
                    'number' => User::where('active_status', 1)->count(), // Active Account from same agency
                ],
                [
                    'id' => 3,
                    'theme' => 'error',
                    'link' => '/manage/users',
                    'svg' => 'icon-user-male',
                    'icon' => 'mdi-account-cancel',
                    'title' => 'Inactive User',
                    'number' => User::where('active_status', 0)->count(), // Inactive Account from same agency
                ],
            ];

            // AgencyAdmin : List of RecentActivity
            $recentactivity = AuditLog::where('user_id', Auth::user()->id)
                                ->orderBy('created_at', 'desc')
                                ->take(4)
                                ->get();

            return response()->json([
                'topcards' => $topCards,
                'recentactivity' => $recentactivity,
                'success' => 'OK'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }

    public function getStatistic(): JsonResponse
    {
        try {
            initializeTenant();
            $topCards = [
                [
                    'id' => 1,
                    'theme' => 'primary',
                    'link' => '/reports',
                    'svg' => 'icon-connect',
                    'icon' => 'mdi-file-multiple',
                    'title' => 'Total Report',
                    'number' => ReportFile::get()->count(),
                ],
                [
                    'id' => 2,
                    'theme' => 'secondary',
                    'link' => '/reports',
                    'svg' => 'icon-connect',
                    'icon' => 'mdi-file-download',
                    'title' => 'Downloaded Report',
                    'number' => 0, // No data yet
                ],
                [
                    'id' => 3,
                    'theme' => 'success',
                    'link' => '/reports',
                    'svg' => 'icon-connect',
                    'icon' => 'mdi-file-export',
                    'title' => 'Published Report',
                    'number' => ReportFile::where('status', 1)->count(),
                ],
                [
                    'id' => 4,
                    'theme' => 'warning',
                    'link' => '/reports',
                    'svg' => 'icon-connect',
                    'icon' => 'mdi-file-edit',
                    'title' => 'Draf Report',
                    'number' => ReportFile::where('status', 0)->count(),
                ],
            ];

            // ReportAdmin: List of UserActivity
            $useractivity = AuditLog::whereNot('user_id', Auth::user()->id)
                                ->orderBy('created_at', 'desc')
                                ->with('user')
                                ->take(4)
                                ->get();

            // ReportAdmin: List of RecentActivity
            $recentactivity = AuditLog::where('user_id', Auth::user()->id)
                                ->orderBy('created_at', 'desc')
                                ->take(4)
                                ->get();

            return response()->json([
                'topcards' => $topCards,
                'recentactivity' => $recentactivity,
                'success' => 'OK'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }

    public function getSuperadmin(Request $request): JsonResponse
    {
        try {
            $topCards = [
                [
                    'id' => 1,
                    'theme' => 'primary',
                    'link' => '/manage/users',
                    'svg' => 'icon-user-male',
                    'icon' => 'mdi-account-tie',
                    'title' => 'Total System Admin',
                    'number' => User::on('mysql')->role('system admin')->count(),
                ],
                [
                    'id' => 2,
                    'theme' => 'secondary',
                    'link' => '/manage/users',
                    'svg' => 'icon-user-male',
                    'icon' => 'mdi-account',
                    'title' => 'Total User',
                    'number' => User::on('mysql')->get()->count(),
                ],
                [
                    'id' => 3,
                    'theme' => 'success',
                    'link' => '#',
                    'svg' => 'icon-user-male',
                    'icon' => 'mdi-magnify-plus',
                    'title' => 'Total Searches by Agencies',
                    'number' => 0, // Will be calculated below
                ],
                [
                    'id' => 4,
                    'theme' => 'warning',
                    'link' => '#',
                    'svg' => 'icon-user-male',
                    'icon' => 'mdi-account-search',
                    'title' => 'Total Profiling Generated',
                    'number' => 0, // Will be calculated below
                ],
            ];

            // SuperAdmin: List of SystemAdmin
            $sysadmins = User::on('mysql')->role('system admin')->get();

            // SuperAdmin: List of UserActivity
            $useractivity = AuditLog::on('mysql')->whereNot('user_id', Auth::user()->id)
                                ->orderBy('created_at', 'desc')
                                ->with('user')
                                ->take(4)
                                ->get();

            // SuperAdmin: List of RecentActivity
            $recentactivity = AuditLog::on('mysql')->where('user_id', Auth::user()->id)
                                ->orderBy('created_at', 'desc')
                                ->take(4)
                                ->get();

            // Raw sql queries for statistic by its database
            // jim = cims_jimj_356
            // jpn = cims_jpnj_757
            // kpdn = cims_kpdndkshk_533
            // pdrm = cims_pdmp_574
            // skmm = cims_skdmms_945
            // ssm = cims_ssms_517

            #$tenants = Tenant::select('id', 'agency_name')->get();
            $tenants = Tenant::whereNotIn('id', ['cims_p_491', 'cims_central'])->get();

            $searches = [];
            $profilings = [];

            // Get date range from request or default to last 6 months
            $startDate = $request->has('start_date') ? Carbon::parse($request->start_date) : Carbon::now()->subMonths(6);
            $endDate = $request->has('end_date') ? Carbon::parse($request->end_date) : Carbon::now();

            // Generate months array based on date range
            $months = [];
            $current = $startDate->copy();

            while ($current->lte($endDate)) {
                $months[] = $current->format('M Y');
                $current->addMonth();
            }

            // Reverse to show oldest month first
            $months = array_reverse($months);

            foreach ($tenants as $tenant) {
                // $tenant->makeCurrent();
                tenancy()->initialize($tenant);

                $searchesByMonths = [];
                $profilingsByMonths = [];

                foreach ($months as $month) {
                    $monthDate = Carbon::parse($month);
                    $searchesByMonths[] = IntegratedSearch::whereYear('created_at', $monthDate->year)
                                                  ->whereMonth('created_at', $monthDate->month)
                                                  ->count();
                    $profilingsByMonths[] = CaseFile::whereYear('created_at', $monthDate->year)
                                                       ->whereMonth('created_at', $monthDate->month)
                                                       ->count();
                }

                array_push($searches, [
                    'name' => $tenant->agency_name,
                    'data' => $searchesByMonths
                ]);
                array_push($profilings, [
                    'name' => $tenant->agency_name,
                    'data' => $profilingsByMonths
                ]);

                endConnectionTenant();
            }

            // List of User Activity
            // $useractivity = Audit::where('user_id', Auth::user()->id)
            //                     ->orderBy('created_at', 'desc')
            //                     ->with('user')
            //                     ->get();

            // List of Recent Activity
            // $user_from_same_agency = User::where('agency_id', Auth::user()->agency_id)->pluck('id');
            // $recentactivity = Audit::whereIn('user_id', $user_from_same_agency)
            //                     ->orderBy('created_at', 'desc')
            //                     ->get();
            // \Log::info($searches);
            // \Log::info($profilings);
            // \Log::info($months);

            // Calculate totals
            $totalSearches = 0;
            $totalProfilings = 0;

            foreach ($searches as $search) {
                $totalSearches += array_sum($search['data']);
            }

            foreach ($profilings as $profiling) {
                $totalProfilings += array_sum($profiling['data']);
            }

            // Update topCards with calculated values
            $topCards[2]['number'] = $totalSearches;
            $topCards[3]['number'] = $totalProfilings;

            // Ensure we're using central database for user engagement data
            \DB::setDefaultConnection('mysql');

            // Get user engagement data for superadmin (aggregated across all users)
            $userEngagementMonths = [];
            $loggedInUsers = [];
            $registeredUsers = [];

            // Get date range from request or default to last 6 months
            $startDate = $request->has('start_date') ? Carbon::parse($request->start_date) : Carbon::now()->subMonths(6)->startOfMonth();
            $endDate = $request->has('end_date') ? Carbon::parse($request->end_date) : Carbon::now()->endOfMonth();

            $current = $startDate->copy();
            while ($current->lte($endDate)) {
                $userEngagementMonths[] = $current->format('M');

                // Count logged-in users (visitors) for the month across all users
                $loggedIn = Visitor::on('mysql')->whereMonth('created_at', $current->month)
                    ->whereYear('created_at', $current->year)
                    ->distinct('user_id')
                    ->count('user_id');
                $loggedInUsers[] = $loggedIn;

                // Count registered users up to that month
                $registered = User::on('mysql')->whereDate('created_at', '<=', $current->endOfMonth())
                    ->count();
                $registeredUsers[] = $registered;

                $current->addMonth();
            }

            $totalLoggedIn = array_sum($loggedInUsers);
            // Count registered users only within the date range
            $totalRegistered = User::on('mysql')->whereBetween('created_at', [$startDate, $endDate])->count();

            // Get detailed visitor information with user relationships
            $visitors = Visitor::on('mysql')
                ->with('user:id,name,email')
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get(['id', 'user_id', 'ip_address', 'user_agent', 'created_at']);

            // Get recent integrated searches and case files from each tenant
            $recentSearches = [];
            $recentCaseFiles = [];
            
            foreach ($tenants as $tenant) {
                tenancy()->initialize($tenant);
                
                // Get recent integrated searches with request body data
                $tenantSearches = IntegratedSearch::select('id', 'request_body', 'agency_id', 'created_at')
                    ->whereNotNull('request_body')
                    ->orderBy('created_at', 'desc')
                    ->take(5)
                    ->get()
                    ->map(function($search) use ($tenant) {
                        // Parse the request_body to extract search information
                        $requestData = is_string($search->request_body) ? json_decode($search->request_body, true) : $search->request_body;
                        $searchQuery = $requestData['search'] ?? $requestData['query'] ?? $requestData['keyword'] ?? 'N/A';
                        
                        return [
                            'id' => $search->id,
                            'title' => is_array($searchQuery) ? implode(', ', $searchQuery) : $searchQuery,
                            'description' => 'Search performed on ' . $search->created_at->format('Y-m-d H:i:s'),
                            'agency' => $tenant->agency_name,
                            'created_at' => $search->created_at
                        ];
                    });
                
                foreach ($tenantSearches as $search) {
                    $recentSearches[] = $search;
                }
                
                // Get recent case files with specific details
                $tenantCaseFiles = CaseFile::select('id', 'case_ref_id', 'title as case_title', 'case_category', 'created_at')
                    ->orderBy('created_at', 'desc')
                    ->take(5)
                    ->get()
                    ->map(function($caseFile) use ($tenant) {
                        return [
                            'id' => $caseFile->id,
                            'case_ref_id' => $caseFile->case_ref_id,
                            'case_title' => $caseFile->case_title,
                            'case_category' => $caseFile->case_category,
                            'agency' => $tenant->agency_name,
                            'created_at' => $caseFile->created_at
                        ];
                    });
                
                foreach ($tenantCaseFiles as $caseFile) {
                    $recentCaseFiles[] = $caseFile;
                }
                
                endConnectionTenant();
            }
            
            // Sort and limit the combined results
            usort($recentSearches, function($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });
            $recentSearches = array_slice($recentSearches, 0, 10);
            
            usort($recentCaseFiles, function($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });
            $recentCaseFiles = array_slice($recentCaseFiles, 0, 10);

            return response()->json([
                'topcards' => $topCards,
                'sysadmins' => $sysadmins,
                'useractivity' => $useractivity,
                'recentactivity' => $recentactivity,
                'visitors' => $visitors,
                'recentSearches' => $recentSearches,
                'recentCaseFiles' => $recentCaseFiles,
                'tenants' => $tenants,
                'xaxis' => $months,
                'searches' => $searches,
                'profilings' => $profilings,
                'totalSearches' => $totalSearches,
                'totalProfilings' => $totalProfilings,
                'userEngagement' => [
                    'months' => $userEngagementMonths,
                    'loggedInUsers' => $loggedInUsers,
                    'registeredUsers' => $registeredUsers,
                    'totalLoggedIn' => $totalLoggedIn,
                    'totalRegistered' => $totalRegistered
                ],
                'success' => 'OK'
            ], 200);
        } catch (\Throwable $th) {
        return response()->json([
            'message' => $th->getMessage(),
            'status' => 500,
        ]);
    }
}




}
