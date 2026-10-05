<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Dashboard\IntegratedSearchAgencyResource;
use App\Http\Resources\PaginationResource;
use App\Http\Resources\TemporaryProfilingResource;
use App\Models\IntegratedSearch;
use App\Models\TemporaryProfiling;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardSuperadminController extends Controller
{
    public function socialMedia(Request $request)
    {
        try {
            $tenant = Tenant::find($request->agencyId);

            tenancy()->initialize($tenant);

            $query = TemporaryProfiling::query();
            
            // Apply date filter if provided
            if ($request->has('start_date') && $request->has('end_date')) {
                $startDate = \Carbon\Carbon::parse($request->start_date)->startOfDay();
                $endDate = \Carbon\Carbon::parse($request->end_date)->endOfDay();
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }
            
            $temporaryProfiling = $query->orderBy('created_at', 'desc')->paginate(5);

            return response()->json([
                'searchResultSocialMedia' => TemporaryProfilingResource::collection($temporaryProfiling),
                'pagination' => new PaginationResource($temporaryProfiling)
            ], 200);

        } catch (\Throwable $th) {
            \Log::info($th);
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }

    public function agency(Request $request)
    {
        try {
            $tenant = Tenant::find($request->agencyId);

            tenancy()->initialize($tenant);

            $query = IntegratedSearch::whereNotNull('request_body->agencies');
            
            // Apply date filter if provided
            if ($request->has('start_date') && $request->has('end_date')) {
                $startDate = \Carbon\Carbon::parse($request->start_date)->startOfDay();
                $endDate = \Carbon\Carbon::parse($request->end_date)->endOfDay();
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }
            
            $searchLogs = $query->orderBy('created_at', 'desc')->paginate(5);

            return response()->json([
                'searchResultAgency' => IntegratedSearchAgencyResource::collection($searchLogs),
                'pagination' => new PaginationResource($searchLogs)
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
