<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaginationResource;
use App\Http\Resources\UserManualCollection;
use App\Models\UserManual;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as FacadesRequest;

class UserManualController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:user manual read', ['only' => ['index']]);
    }

    public function index(): JsonResponse
    {
        try {
            if (auth()->user()->hasRole('system admin')) {
                return response()->json([
                    'message' => 'Forbidden',
                    'status' => 403,
                ], 403);
            }

            // Get all user manuals
            $userManuals = UserManual::query()
                ->when(FacadesRequest::input('search'), function ($query, $searchValue) {
                    return $query->where('title', 'like', "%{$searchValue}%")
                        ->orWhere('description', 'like', "%{$searchValue}%");
                })
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            $latestUpdate = UserManual::orderBy('updated_at','DESC')->first()->updated_at;

            return response()->json([
                'userManuals' => new UserManualCollection($userManuals),
                'latestUpdate' => $latestUpdate ? $latestUpdate->format('d F Y') : null,
                'pagination' => new PaginationResource($userManuals)
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }
}
