<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): Response|JsonResponse
    {
        $request->checkMaintenance($request);
        $request->checkAgencyRemoval($request);
        $request->checkActiveStatus($request);
        $request->authenticate();
        $request->session()->regenerate();

        // Check if this is an API request
        if ($request->wantsJson() || $request->is('api/*')) {
            $user = Auth::user();
            
            // Load relationships
            $user->load(['roles', 'permissions']);
            
            // Bypass FIDO2 in local development
            $fidoRequired = false;
            if (!app()->environment('local')) {
                $fidoRequired = $user->fido_login_at !== null;
            }
            
            return response()->json([
                'message' => 'Login successful',
                'user' => $user,
                'fido_required' => $fidoRequired,
                'bypass_fido' => app()->environment('local'),
                'roles' => $user->roles->pluck('name') ?? [],
                'permissions' => $user->permissions->pluck('name') ?? [],
            ], 200);
        }

        return response()->noContent();
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): Response|JsonResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Logged out successfully'
            ], 200);
        }

        return response()->noContent();
    }

    /**
     * Destroy an authenticated session for tenant.
     */
    public function destroyTenant(Request $request): Response|JsonResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Logged out successfully'
            ], 200);
        }

        return response()->noContent();
    }
}