<?php

namespace App\Http\Controllers\Api\Superadmin;

use App\Http\Controllers\Controller;
use App\Http\Resources\MaintenanceCollection;
use App\Http\Resources\PaginationResource;
use App\Models\Maintenance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as FacadesRequest;

class MaintenanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:maintenance read', ['only' => ['index', 'show']]);
        $this->middleware('can:maintenance create', ['only' => ['create', 'store']]);
        $this->middleware('can:maintenance update', ['only' => ['edit', 'update']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            // Get all maintenances
//            $maintenances = (new Maintenance())->newQuery();
//            $maintenances = $maintenances->latest()->get();

            $maintenances = Maintenance::query()->when(FacadesRequest::input('search'), function ($queryWhen, $searchValue) {
                return $queryWhen->where('description', 'like', "%{$searchValue}%")
                    ->orWhere('status', 'like', "%{$searchValue}%");
            })->orderBy('created_at', 'desc')->paginate(10);

            return response()->json([
                'maintenances' => new MaintenanceCollection($maintenances),
                'pagination' => new PaginationResource($maintenances)
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
            $combinedStart = date('Y-m-d H:i:s', strtotime("$request->date_start $request->time_start"));
            $combinedEnd = date('Y-m-d H:i:s', strtotime("$request->date_end $request->time_end"));

            $status = 'disable';

            if (in_array(strtolower($request->status), ['papar', 'enabled'])) {
                $status = 'enable';
            }

            if (in_array(strtolower($request->status), ['dilumpuhkan', 'disabled'])) {
                $status = 'disable';
            }

            // Create maintenance
            Maintenance::create([
                'user_id' => auth()->user()->id,
                'status' => $status,
                'description' => $request->description,
                'date_start' => $combinedStart,
                'date_end' => $combinedEnd,
            ]);

            $userUnderAgency = User::get()->pluck('id')->toArray();
            $message = 'Maintenance will be held on ' . Carbon::parse($request->date_start)->format('d/m/Y') . ' until ' . Carbon::parse($request->date_end)->format('d/m/Y') . ' for ' . $request->description . '';

            sendNotification($message, $userUnderAgency);

            return response()->json([
                'message' => 'Maintenance created successfully',
            ], 201);

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
            $combinedStart = date('Y-m-d H:i:s', strtotime("$request->date_start $request->time_start"));
            $combinedEnd = date('Y-m-d H:i:s', strtotime("$request->date_end $request->time_end"));

            // Update maintenance
            $maintenance = Maintenance::findOrFail($id);
            $maintenance->update([
                'status' => strtolower($request->status),
                'description' => $request->description,
                'date_start' => $combinedStart,
                'date_end' => $combinedEnd,
            ]);

            return response()->json([
                'message' => 'Maintenance updated successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            // Find role
            $maintenance = Maintenance::findOrFail($id);

            // Delete role
            $maintenance->delete();

            return response()->json([
                'message' => 'Maintenance deleted successfully',
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }
}
