<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaginationResource;
use App\Http\Resources\Profiling\ProfilingResource;
use App\Http\Resources\Profiling\ProfilingsCollection;
use App\Models\CaseProfile;
use App\Models\Profiling;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as FacadesRequest;

class ProfilingController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:profiling read', ['only' => ['index']]);
        $this->middleware('can:profiling create', ['only' => ['store']]);
        $this->middleware('can:profiling update', ['only' => ['update']]);
        $this->middleware('can:profiling delete', ['only' => ['destroy']]);
    }

    public function show($id): JsonResponse
    {
        try {
            initializeTenant();

            $profilings = CaseProfile::query()
                ->where('case_id', $id)
//                ->when(FacadesRequest::input('search'), function ($query, $searchValue) {
//                    return $query->where('agency_name', 'like', "%{$searchValue}%");
//                })
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            return response()->json([
                'profilings' => new ProfilingsCollection($profilings),
                'pagination' => new PaginationResource($profilings)
            ], 200);
        } catch (\Throwable $th) {
            \Log::debug($th->getMessage());
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }

    public function detail($id): JsonResponse
    {
        try {
            initializeTenant();

            $profiling = CaseProfile::find($id);

            return response()->json([
                'profiling' => new ProfilingResource($profiling),
            ], 200);
        } catch (\Throwable $th) {
            \Log::debug($th);
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

            $dataArray = array();
            foreach ($request->data as $data) {
                if (isset($data[0]->agency) || isset($data[0]->organization_name)) {

                    $socialMediaPostsArray = array();
                    if (isset($data['posts'])) {
                        foreach ($data['posts'] as $post) {
                            if ($post['is_choose']) {
                                $socialMediaPostsArray[] = $post;
                            }
                        }
                    }

                    $socialMediaUserProfilesArray = array();
                    if (isset($data['user_profiles'])) {
                        foreach ($data['user_profiles'] as $post) {
                            if ($post['is_choose']) {
                                $socialMediaUserProfilesArray[] = $post;
                            }
                        }
                    }

                    $dataArray[] = [
                        'website_link' => $data['website_link'],
                        'social_media' => $data['social_media'],
                        'posts' => $socialMediaPostsArray,
                        'user_profiles' => $socialMediaUserProfilesArray,
                        'search_term' => isset($data['search_term']) ? $data['search_term'] : 'N/A',
                        'count_posts' => isset($data['posts']) ? count($data['posts']) : 0,
                        'count_user_profiles' => isset($data['user_profiles']) ? count($data['user_profiles']) : 0,
                    ];

                } else {
                    $dataArray[] = $data;
                }
            }

            // Create profiling
            CaseProfile::create([
                'case_id' => $request->case_id,
                'is_create_from_user' => $request->is_create_from_user ?? false,
                'data' => json_encode($dataArray),
            ]);

            return response()->json([
                'message' => 'Profiling created successfully',
            ], 201);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            initializeTenant();

            $profiling = CaseProfile::findOrFail($id);

            $arrayProfiling = array();
            $data = [
                'name' => $request->name,
                'id_passport' => $request->id_number,
                'phone_number' => $request->phone_number,
                'business_registration' => $request->business_registration,
                'bank_account' => $request->bank_account,
                'socmed' => $request->socmed,
                'other' => $request->other,
            ];

            $arrayProfiling[] = $data;

            $profiling->update([
                'data' => $arrayProfiling,
            ]);

            return response()->json([
                'message' => 'Profiling updated successfully',
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
            initializeTenant();

            $profiling = CaseProfile::findOrFail($id);

            $profiling->delete();

            return response()->json([
                'message' => 'Profiling deleted successfully',
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }
}
