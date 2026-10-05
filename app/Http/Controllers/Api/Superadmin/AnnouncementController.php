<?php

namespace App\Http\Controllers\Api\Superadmin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaginationResource;
use App\Http\Resources\RolesCollection;
use App\Models\Role;
use Illuminate\Http\Request;

use App\Models\Announcement;
use App\Http\Resources\AnnouncementsCollection;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Request as FacadesRequest;

class AnnouncementController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:announcement read', ['only' => ['index', 'show']]);
        $this->middleware('can:announcement create', ['only' => ['create', 'store']]);
        $this->middleware('can:announcement update', ['only' => ['edit', 'update']]);
        $this->middleware('can:announcement delete', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            abort_if(!auth()->user()->hasRole('system admin'), 403, 'Unauthorized');

            // Get all announcements
            $announcements = Announcement::query()->when(FacadesRequest::input('search'), function ($query, $searchValue) {
                return $query->where('title', 'like', "%{$searchValue}%")
                    ->orWhere('description', 'like', "%{$searchValue}%")
                    ->orWhere('status', 'like', "%{$searchValue}%");
            })->orderBy('date_start', 'desc')->paginate(10);

            $roles = Role::whereNotIn('name', ['staff'])->pluck('name')->toArray();

            $rolesUpperCase = array_map('ucwords', $roles);

            return response()->json([
                'roles' => $rolesUpperCase,
                'announcements' => new AnnouncementsCollection($announcements),
                'pagination' => new PaginationResource($announcements)
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

            $visibility = $request->visibility;
            if ($request->all_visible) {
                $roles = Role::whereNotIn('name', ['staff'])->pluck('name')->toArray();

                $visibility = array_map('ucwords', $roles);
            }

            // Create announcement
            $announcement = Announcement::create([
                'title' => $request->title,
                'user_id' => auth()->user()->id,
                'status' => strtolower($request->status),
                'description' => $request->description,
                'date_start' => $request->date_start . ' 00:00:00',
                'date_end' => $request->date_end . ' 23:59:00',
                'visibility' => json_encode($visibility),
            ]);

            foreach ($request->visibility as $key => $role) {
                $userUnderAgencyByRole = User::role(strtolower($role))->get()->pluck('id');

                if($userUnderAgencyByRole->first()){
                    $announcement->users()->attach($userUnderAgencyByRole);
                }
            }

            return response()->json([
                'message' => 'Announcement created successfully',
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
            abort_if(!auth()->user()->hasRole('system admin'), 403, 'Unauthorized');

            // Update announcement
            $announcement = Announcement::findOrFail($id);

            $announcement->update([
                'status' => strtolower($request->status),
                'description' => $request->description,
                'title' => $request->title,
                'date_start' => $request->date_start . ' 00:00:00',
                'date_end' => $request->date_end . ' 23:59:00',
                'visibility' => json_encode($request->visibility),
            ]);

            return response()->json([
                'message' => 'Announcement updated successfully',
                'announcement' => $announcement,
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

            // Find role
            $announcement = Announcement::findOrFail($id);

            // Delete role
            $announcement->delete();

            return response()->json([
                'message' => 'Announcement deleted successfully',
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ]);
        }
    }
}
