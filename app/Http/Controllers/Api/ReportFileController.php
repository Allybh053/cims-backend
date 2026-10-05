<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaginationResource;
use App\Http\Resources\ReportFilesCollections;
use App\Http\Resources\RolesCollection;
use App\Models\Media;
use App\Models\ReportFile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\FlareClient\Report;

class ReportFileController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:report read', ['only' => ['index']]);
        $this->middleware('can:report create', ['only' => ['store']]);
        $this->middleware('can:report update', ['only' => ['update']]);
        $this->middleware('can:report delete', ['only' => ['destroy']]);
    }

    public function index(Request $request): JsonResponse
    {
        try {
            initializeTenant();

    $reportFilesQuery = ReportFile::fetchReports();

    // If there's a search parameter, filter the report files
    if ($request->has('search')) {
        $searchValue = $request->input('search');
        $reportFilesQuery = $reportFilesQuery->where('title', 'like', "%{$searchValue}%");
    }

    // Get paginated results
    $reportFiles = $reportFilesQuery->paginate(10);

    // Log the report files if needed for debugging
    \Log::debug($reportFiles);

            return response()->json([
                'reportFiles' => new ReportFilesCollections($reportFiles),
                'pagination' => new PaginationResource($reportFiles)
            ], 200);

        } catch (\Throwable $th) {
            \Log::debug($th->getMessage());
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }

    public function roles(): JsonResponse
    {
        try {
            // Get roles
            $roles = Role::fetchRoles();

            return response()->json([
                'roles' => new RolesCollection($roles),
            ], 200);

        } catch (\Throwable $th) {
            \Log::error($th->getMessage());
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
//            DB::beginTransaction();

            initializeTenant();

            $visibilityRoles = array_map('strtolower', $request->roles);

            $user = User::where('email', auth()->user()->email)->get()->first();

            $status = $request->status == 'true' ? true : false;

            // Create report files
            $report = ReportFile::create([
                'user_id' => $user->id,
                'title' => $request->title,
                'description' => $request->description,
                'visibility' => json_encode($visibilityRoles),
                'status' => $status,
            ]);

            // Upload file
            $document = $report->addMedia($request->file)
                ->withCustomProperties(['created_at' => now()])
                ->toMediaCollection('report', 'local');

            $document->setCustomProperty('mime_type', $document->mime_type);
            $document->setCustomProperty('id', $document->id);
            $document->save();

//            DB::commit();

            return response()->json([
                'message' => 'Report created successfully',
            ], 201);

        } catch (\Throwable $th) {
            \Log::error($th);
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
//            DB::beginTransaction();
            initializeTenant();

            // Update report
            $user = User::where('email', auth()->user()->email)->get()->first();

            $report = ReportFile::findOrFail($id);

            $status = $request->status == 'true' ? true : false;

            $report->update([
                'user_id' => $user->id,
                'title' => $request->title,
                'description' => $request->description,
                'visibility' => json_encode($request->roles),
                'status' => $status,
            ]);

            if ($request->file) {
                $report->clearMediaCollection('report', 'local');

                $document = $report->addMedia($request->file)
                    ->withCustomProperties(['created_at' => now()])
                    ->toMediaCollection('report', 'local');

                $document->setCustomProperty('mime_type', $document->mime_type);
                $document->setCustomProperty('id', $document->id);
                $document->save();
            }
//            DB::commit();

            return response()->json([
                'message' => 'Report updated successfully',
                'report' => $report,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }

    public function destroy($id)
    {
        try {
            initializeTenant();

            $report = ReportFile::findOrFail($id);

            if ($report) {
                // Soft Delete media
                $report->getFirstMedia('report')->delete();

                // Delete report
                $report->delete();
            } else {
                return response()->json([
                    'message' => 'Report not found',
                ], 400);
            }

            return response()->json([
                'message' => 'Report deleted successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }
}
