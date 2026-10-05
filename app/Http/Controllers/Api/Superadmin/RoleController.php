<?php
namespace App\Http\Controllers\Api\Superadmin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaginationResource;
use App\Http\Resources\PermissionsCollection;
use App\Models\Permission;
use App\Models\Role;
use App\Http\Resources\RolesCollection;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as FacadesRequest;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:role read', ['only' => ['index']]);
        $this->middleware('can:role create', ['only' => ['store']]);
        $this->middleware('can:role update', ['only' => ['update']]);
        $this->middleware('can:role delete', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            abort_if(!auth()->user()->hasRole('system admin') && !auth()->user()->hasRole('super admin'), 403, 'Unauthorized');

            // Filter Role
            $roles = Role::query()->whereNotIn('name', ['staff']);

            // Search Role
            $roles = $roles
                ->when(FacadesRequest::input('search'), function ($queryWhen, $searchValue) {
                    return $queryWhen->where('name', 'like', "%{$searchValue}%");
                })->latest('created_at', 'desc')->paginate(10);

            // Get all permissions
            $permissions = Permission::orderBy('name')->get();

            return response()->json([
                'roles' => new RolesCollection($roles),
                'permissions' => new PermissionsCollection($permissions),
                'pagination' => new PaginationResource($roles)
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
            abort_if(!auth()->user()->hasRole('system admin') && !auth()->user()->hasRole('super admin'), 403, 'Unauthorized');

            // Transform permissions to lowercase
            $permissions = array_map('strtolower', $request->permissions);

            if (Role::where('name', strtolower($request->name))->exists()) {
                return response()->json([
                    'message' => 'Role already exists',
                    'status' => 422,
                ], 422);
            }

            // Create role
            $role = Role::create([
                'name' => strtolower($request->name),
                'description' => $request->description,
                'guard_name' => 'web',
            ]);

            // Attach permissions to role
            $role->syncPermissions($permissions);

            return response()->json([
                'message' => 'Role created successfully',
                'role' => $role,
            ], 201);

        } catch (\Throwable $th) {
            \Log::error($th);
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            abort_if(!auth()->user()->hasRole('system admin') && !auth()->user()->hasRole('super admin'), 403, 'Unauthorized');

            // Transform permissions to lowercase
            $permissions = array_map('strtolower', $request->permissions);

            $role = Role::findOrFail($id);

            if (Role::where('name', strtolower($request->name))->exists() && $role->name !== strtolower($request->name)) {
                return response()->json([
                    'message' => 'Role already exists',
                    'status' => 422,
                ], 422);
            }

            $users = User::role($role->name)->get();

            // Update role
            $role->update([
                'name' => strtolower($request->name),
                'description' => $request->description,
            ]);

            // Sync permissions to users
            foreach ($users as $user) {
                $user->syncPermissions($permissions);
            }

            // Sync permissions to role
            $role->syncPermissions($permissions);

            return response()->json([
                'message' => 'Role updated successfully',
                'role' => $role,
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
            abort_if(!auth()->user()->hasRole('system admin') && !auth()->user()->hasRole('super admin'), 403, 'Unauthorized');

            // Find role
            $role = Role::findOrFail($id);

            // Check if role is associated with users
            if ($role->users->count() > 0) {
                return response()->json([
                    'message' => 'Role cannot be deleted because it is associated with users',
                    'status' => 422,
                ], 422);
            }

            // Detach permissions from role
            $role->permissions()->detach();

            // Delete role
            $role->delete();

            return response()->json([
                'message' => 'Role deleted successfully',
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }
}
