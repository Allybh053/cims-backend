<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CaseFile\CaseFilesCollection;
use App\Http\Resources\CaseFileShare\CaseFileShareDashboardCollection;
use App\Http\Resources\CaseFileShare\CaseFileSharesCollection;
use App\Http\Resources\CaseFileShare\CaseFileSharesInvestigatorCollection;
use App\Http\Resources\PaginationResource;
use App\Models\CaseFile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as FacadesRequest;

class CaseFileShareController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:intelligence management share', ['only' => ['index', 'show', 'store', 'update']]);
    }

    public function index(Request $request): JsonResponse
    {
        try {
            // $id is case file id
            initializeTenant();

            $user = User::whereEmail(auth()->user()->email)->first();

            $caseFiles = $user->caseFiles()->wherePivot('status', '!=', 'owner')
                ->when(FacadesRequest::input('search'), function ($query, $searchValue) {
                    return $query->where('title', 'like', "%{$searchValue}%")
                        ->orWhere('case_ref_id', 'like', "%{$searchValue}%");
                });

            if ($request->confidential) {
                $caseFiles = $caseFiles->where('level_id', $request->confidential);
            }

            if ($request->status) {
                $caseFiles = $caseFiles->where('status_id', $request->status);
            }

            $caseFilesShare = array();
            if ($caseFiles->count() > 0) {
                $caseFilesShare = $caseFiles->wherePivot('expired_at', null)
                    ->orWhere(function ($query) {
                        if ($query->where('expired_at', '!=', null)) {
                            $query->where('expired_at', '>=', now());
                        }
                    })->orderBy('created_at', 'desc')->paginate(10);
            } else {
                $caseFilesShare = $caseFiles->orderBy('created_at', 'desc')->paginate(10);
            }

            return response()->json([
                'sharedCaseFiles' => new CaseFilesCollection($caseFilesShare),
                'pagination' => new PaginationResource($caseFilesShare)
            ], 200);

        } catch (\Throwable $th) {
            \Log::info($th);
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            // $id is case file id
            initializeTenant();

            // Get users that are not yet shared to this case file
            $users = User::sharedList($id);

            // Get users that already have access to this case file
            $usersHasAccess = User::hasAccess($id);

            return response()->json([
                'users' => new CaseFileSharesCollection($users, $id),
                'usersHasAccess' => new CaseFileSharesCollection($usersHasAccess, $id),
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }

    // Filter user by investigator role that does not have access to case file
    public function filterRole(Request $request): JsonResponse
    {
        try {
            $userArray = [];
            foreach ($request->users as $user) {
                $userModel = User::whereEmail($user['email'])->first();

                if ($userModel->hasRole('investigator')) {
                    $userArray[] = $user;
                }
            }

            return response()->json([
                'users' => new CaseFileSharesInvestigatorCollection($userArray),
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            initializeTenant();

            $user = User::find($request->user_id);
            $caseFile = CaseFile::find($request->case_file_id);

            // Attach user to case file
            $caseFile->users()->attach($user->id, [
                'status' => 'viewer',
            ]);

            return response()->json([
                'message' => 'Case file shared successfully',
                'status' => 200,
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            // $id is case file id
            initializeTenant();

            $users = $request->all();

            foreach ($users as $user) {
                if (is_array($user)) {
                    $userShared = User::find($user['id']);
                    $userShared->caseFiles()->syncWithPivotValues($id, ['status' => $user['status']]);
                }
            }

            return response()->json([
                'message' => 'Case file share updated successfully',
                'status' => 200,
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }

    public function destroy(Request $request): JsonResponse
    {
        try {
            // $id is case file id
            initializeTenant();

            $user = User::find($request->user_id);

            // Detach user from case file
            $user->caseFiles()->detach($request->case_file_id);

            return response()->json([
                'message' => 'Case file share remove successfully',
                'status' => 200,
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }

    public function expire(Request $request): JsonResponse
    {
        try {
            // $id is case file id
            initializeTenant();

            $user = User::find($request->user_id);

            // Add expired date to case file
            $user->caseFiles()->syncWithPivotValues($request->case_file_id, ['expired_at' => $request->expired]);

            return response()->json([
                'message' => 'Case file share updated successfully',
                'status' => 200,
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }

    public function allCaseFilesShare(): JsonResponse
    {
        try {
            initializeTenant();

            $caseFilesShare = User::with('caseFiles')->whereHas('caseFiles', function ($query) {
                $query->where('status', '!=', 'owner');
            })->orderBy('name')->paginate(5);

            return response()->json([
                'sharedCaseFiles' => new CaseFileShareDashboardCollection($caseFilesShare),
                'pagination' => new PaginationResource($caseFilesShare)
            ], 200);

        } catch (\Throwable $th) {
            \Log::error($th);
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }
}
