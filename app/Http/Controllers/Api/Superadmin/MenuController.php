<?php

namespace App\Http\Controllers\Api\Superadmin;

use App\Http\Controllers\Controller;
use App\Http\Resources\MenusCollection;
use App\Http\Resources\PaginationResource;
use App\Models\Menu;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as FacadesRequest;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            // Get all menus
//            $menus = (new Menu())->newQuery();
//            $menus = $menus->latest()->get();

            abort_if(!auth()->user()->hasRole('system admin') && !auth()->user()->hasRole('super admin'), 403, 'Unauthorized');

            $menus = Menu::query()->when(FacadesRequest::input('search'), function ($query, $searchValue) {
                return $query->where('title', 'like', "%{$searchValue}%")
                    ->orWhere('slug', 'like', "%{$searchValue}%")
                    ->orWhere('icon', 'like', "%{$searchValue}%")
                    ->orWhere('route_name', 'like', "%{$searchValue}%")
                    ->orWhere('layout', 'like', "%{$searchValue}%");
            })->orderBy('created_at', 'desc')->paginate(10);

            $roles = Role::whereNotIn('name', ['staff'])->pluck('name')->toArray();

            $rolesUpperCase = array_map('ucwords', $roles);

            return response()->json([
                'roles' => $rolesUpperCase,
                'menus' => new MenusCollection($menus),
                'pagination' => new PaginationResource($menus)
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

            $role = Role::where('name', strtolower($request->role))->first();

            // Create menu
            Menu::create([
                'roles_id' => $role->id,
                'parent_id' => $request->parent,
                'title' => $request->title,
                'slug' => $request->slug,
                'icon' => $request->icon,
                'route_name' => $request->route_name,
                'layout' => $request->layout,
                'lvl' => $request->level,
                'position' => $request->position,
            ]);

            return response()->json([
                'message' => 'Menu created successfully',
//                'maintenance' => $menu,
            ], 201);

        } catch (\Throwable $th) {
            \Log::debug($th);
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

            // Update role
            $role = Role::where('name', strtolower($request->role))->first();

            $menu = Menu::findOrFail($id);
            $menu->update([
                'roles_id' => $role->id,
                'parent_id' => $request->parent,
                'title' => $request->title,
                'slug' => $request->slug,
                'icon' => $request->icon,
                'route_name' => $request->route_name,
                'layout' => $request->layout,
                'lvl' => $request->level,
                'position' => $request->position,
            ]);

            return response()->json([
                'message' => 'Menu updated successfully',
                'maintenance' => $menu,
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
            $menu = Menu::findOrFail($id);

            // Delete role
            $menu->delete();

            return response()->json([
                'message' => 'Menu deleted successfully',
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }
}
