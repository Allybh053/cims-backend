<?php

namespace App\Http\Controllers\Api\Superadmin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Branch\BranchesCollection;
use App\Http\Resources\PaginationResource;
use App\Models\Branch;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as FacadesRequest;

class BranchController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:branch read', ['only' => ['index']]);
        $this->middleware('can:branch create', ['only' => ['store']]);
        $this->middleware('can:branch update', ['only' => ['update']]);
        $this->middleware('can:branch delete', ['only' => ['destroy']]);
    }

    public function index(): JsonResponse
    {
        try {
            initializeTenant();

            $branches = Branch::query()->when(FacadesRequest::input('search'), function ($query, $searchValue) {
                return $query->where('name', 'like', "%{$searchValue}%");
            })->orderBy('created_at', 'desc')->paginate(10);

            endConnectionTenant();

            return response()->json([
                'branches' => new BranchesCollection($branches),
                'pagination' => new PaginationResource($branches)
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

            // Create announcement
            Branch::create([
                'name' => $request->name,
            ]);

            endConnectionTenant();

            return response()->json([
                'message' => 'Branch created successfully',
            ], 201);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $tenant = Tenant::find($id);

            tenancy()->initialize($tenant);

            $branches = Branch::all();

            tenancy()->end();

            return response()->json([
                'branches' => new BranchesCollection($branches),
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
            initializeTenant();

            // Update branch
            $branch = Branch::findOrFail($id);

            $branch->update([
                'name' => $request->name,
            ]);

            endConnectionTenant();

            return response()->json([
                'message' => 'Branch updated successfully',
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
            $branch = Branch::findOrFail($id);

            if ($branch->profiles->count() > 0) {
                return response()->json([
                    'message' => 'Branch cannot be deleted because it has associated with profiles',
                    'status' => 422,
                ], 422);
            }

            // Delete branch
            $branch->delete();

            return response()->json([
                'message' => 'Branch deleted successfully',
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }
}
