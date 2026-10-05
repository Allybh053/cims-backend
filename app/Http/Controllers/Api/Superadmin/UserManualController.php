<?php

namespace App\Http\Controllers\Api\Superadmin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaginationResource;
use App\Http\Resources\UserManualCollection;
use App\Models\UserManual;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request as FacadesRequest;

class UserManualController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:user manual read', ['only' => ['index']]);
        $this->middleware('can:user manual create', ['only' => ['store']]);
        $this->middleware('can:user manual update', ['only' => ['update']]);
        $this->middleware('can:user manual delete', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            if (!auth()->user()->hasRole('system admin')) {
                return response()->json([
                    'message' => 'Forbidden',
                    'status' => 403,
                ], 403);
            }

            // Get all user manuals
            $userManuals = UserManual::query()->when(FacadesRequest::input('search'), function ($query, $searchValue) {
                return $query->where('title', 'like', "%{$searchValue}%")
                    ->orWhere('description', 'like', "%{$searchValue}%");
            })->orderBy('created_at', 'desc')->paginate(10);

            return response()->json([
                'userManuals' => new UserManualCollection($userManuals),
                'pagination' => new PaginationResource($userManuals)
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
            abort_if(!auth()->user()->hasRole('system admin'), 403, 'Unauthorized');

            DB::beginTransaction();

            $user = auth()->user();

            // Create user manual
            $userManual = UserManual::create([
                'user_id' => $user->id,
                'title' => $request->title,
                'description' => $request->description,
            ]);

            $document = $userManual->addMedia($request->files_0)
                ->withCustomProperties(['created_at' => now()])
                ->toMediaCollection('user manual', 'local');

            $document->setCustomProperty('mime_type', $document->mime_type);
            $document->setCustomProperty('id', $document->id);
            $document->save();

            DB::commit();

            return response()->json([
                'message' => 'User Manual created successfully',
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
            abort_if(!auth()->user()->hasRole('system admin'), 403, 'Unauthorized');

            DB::beginTransaction();

            // Update user manual
            $userManual = UserManual::findOrFail($id);
            $userManual->update([
                'title' => $request->title,
                'description' => $request->description,
            ]);

            if ($request->files_0){
                $userManual->clearMediaCollection('user manual', 'local');

                $document = $userManual->addMedia($request->files_0)
                    ->withCustomProperties(['created_at' => now()])
                    ->toMediaCollection('user manual', 'local');

                $document->setCustomProperty('mime_type', $document->mime_type);
                $document->setCustomProperty('id', $document->id);
                $document->save();
            }

            DB::commit();

            return response()->json([
                'message' => 'User Manual updated successfully',
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
            abort_if(!auth()->user()->hasRole('system admin'), 403, 'Unauthorized');

            DB::beginTransaction();

            // Find user manual
            $userManual = UserManual::findOrFail($id);

            // Delete user manual
            $userManual->delete();

            // Soft delete media (start)
            $userManual->getFirstMedia('user manual')->delete();
            // Soft delete media (end)

            DB::commit();

            return response()->json([
                'message' => 'User Manual deleted successfully',
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }
}
