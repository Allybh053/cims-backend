<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DataApplicationHistoryCollection;
use App\Http\Resources\PaginationResource;
use App\Models\DataApplication;
use App\Services\DataApplicationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DataApplicationHistoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:data request history read', ['only' => ['index']]);
        $this->middleware('can:data request history delete', ['only' => ['destroy']]);
    }

    public function index(): JsonResponse
    {
        try {
            abort_if(!auth()->user()->hasRole('data request admin'), 403, 'Unauthorized');

            // Get all data requests
            $dataRequests = (new DataApplicationService)->fetchDataApplicationHistory();

            return response()->json([
                'dataRequests' => new DataApplicationHistoryCollection($dataRequests),
                'pagination' => new PaginationResource($dataRequests)
            ], 200);
        } catch (\Throwable $th) {
            \Log::debug($th);
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            abort_if(!auth()->user()->hasRole('data request admin'), 403, 'Unauthorized');

            DB::beginTransaction();

            // Find data application
            $dataApplication = DataApplication::findOrFail($id);

            // Delete data application
            $dataApplication->delete();

            // Soft delete media (start)
            $mediaDataApps = $dataApplication->getMedia('data application');

            foreach ($mediaDataApps as $media) {
                $media->delete();
            }

            $mediaDataApproval = $dataApplication->getMedia('data application approval');

            foreach ($mediaDataApproval as $media) {
                $media->delete();
            }
            // Soft delete media (end)

            DB::commit();

            return response()->json([
                'message' => 'Data request deleted successfully',
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }
}
