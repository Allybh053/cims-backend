<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PermissionsCollection;
use App\Http\Resources\RolesCollection;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
//    public function __construct()
//    {
//        $this->middleware('can:role read', ['only' => ['index']]);
//    }

    public function index(): JsonResponse
    {
        try {
            // Get roles
            $roles = Role::fetchRoles();

            if (!$roles) {
                return response()->json([
                    'message' => 'Forbidden',
                    'status' => 403,
                ], 403);
            }

            // Get all permissions
//            $permissions = Permission::latest()->get();

            return response()->json([
                'roles' => new RolesCollection($roles),
                'permissions' => new PermissionsCollection([]),
            ], 200);
        } catch (\Throwable $th) {
            \Log::debug($th);
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }
}
