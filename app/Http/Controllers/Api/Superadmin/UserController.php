<?php

namespace App\Http\Controllers\Api\Superadmin;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\Branch\BranchesCollection;
use App\Http\Resources\PaginationResource;
use App\Http\Resources\User\UserResource;
use App\Http\Resources\User\UsersCollection;
use App\Jobs\SendFirstTimeMail;
use App\Mail\FirstTimeMail;
use App\Models\Branch;
use App\Models\Profile;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use App\Services\Auth\CentagateService;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:user read', ['only' => ['index']]);
        $this->middleware('can:user create', ['only' => ['store']]);
        $this->middleware('can:user update', ['only' => ['edit', 'update']]);
        $this->middleware('can:user delete', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(): JsonResponse
    {
        try {
            abort_if(!auth()->user()->hasRole('super admin') && !auth()->user()->hasRole('system admin') && !auth()->user()->hasRole('agency admin'), 403, 'Unauthorized');

            $users = User::query()->userList(Request::input('search'));

            if (!$users) {
                return response()->json([
                    'message' => 'Forbidden',
                    'status' => 403,
                ], 403);
            }

            return response()->json([
                'users' => new UsersCollection($users),
                'pagination' => new PaginationResource($users)
            ], 200);

        } catch (\Throwable $th) {
            \Log::debug($th->getMessage());
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    public function store(CreateUserRequest $request, CentagateService $centagateService): JsonResponse
    {
        $tenant = null;
        $email = null;

        try {
            abort_if(!auth()->user()->hasRole('super admin') && !auth()->user()->hasRole('system admin') && !auth()->user()->hasRole('agency admin'), 403, 'Unauthorized');

            DB::beginTransaction();

            $uuid = Str::uuid();

            $randomPassword = Str::random(40);

            // Encrypt password
            $encryptedPassword = bcrypt($randomPassword);

            // Create user in central db
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'uuid' => $uuid,
                'password' => $encryptedPassword,
                'agency_id' => $request->agency['id'] ?? auth()->user()->agency_id ?? null, // $request is from system admin, while auth()->user() is from agency admin
                'agency_name' => $request->agency['name'] ?? auth()->user()->agency_name ?? null, // $request is from system admin, while auth()->user() is from agency admin
            ]);

            // Set email
            $email = $request->email;

            // Get Role
            $role = Role::where('name', strtolower($request->role['name']))->first();

            // Assign role to user
            $user->assignRole($role->name);

            // Assign permissions to user
            $user->givePermissionTo($role->permissions->pluck('name'));

            if ($user->agency_id) { // Conditional for agency users

                // Assign role to user
                $user->assignRole('staff');

                // Set connection to tenant
                $tenant = Tenant::find($user->agency_id);

                // Create user and profile in tenant
                $tenant->run(function () use ($user, $request, $encryptedPassword) {
                    DB::beginTransaction();

                    (new UserService)->storeUserTenant($user, $request, $encryptedPassword);

                    DB::commit();
                });

            } else { // Conditional for central users
                // Create profile
                (new UserService)->storeProfile($user, $request);
            }

            DB::commit();

            //create a new token to be sent to the user.
            $token = Password::createToken($user);

            Mail::to($user)->send(new FirstTimeMail($user, $token));

            // Onboard user on centagate
            $registerResponse = $centagateService->registerUser($user, $request->mobile_no);

            // Reset password on centagate - to have standard access to get user id
            $resetPassResponse = $centagateService->resetPassword($user->email);

            // if ($registerResponse['code'] != 0) {
            //     return response()->json([
            //         'message' => $registerResponse['message'],
            //         'status' => 400,
            //     ], 400);
            // }

            // Send first time login to created user
            //  SendFirstTimeMail::dispatch($user, $token);

            return response()->json([
                'message' => 'User created successfully',
                'status' => '201',
            ], 201);

        } catch (\Throwable $th) {
            \Log::debug($th);
            DB::rollBack();

            // Delete user in tenant if data in central created
            if ($email && $tenant) {

                $tenant->run(function () use ($email) {
                    $user = User::where('email', $email)->first();

                    if ($user) {
                        $user->forceDelete();
                    }
                });
            }

            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    public function edit($id): JsonResponse
    {
        try {
            abort_if(!auth()->user()->hasRole('super admin') && !auth()->user()->hasRole('system admin') && !auth()->user()->hasRole('agency admin'), 403, 'Unauthorized');

            $user = User::findOrFail($id);

            $branches = Branch::all();

            // Check if user is in tenant
            if ($user->agency_id) {
                $roles = $user->roles;
                $permissions = $user->permissions;

                $tenant = Tenant::find($user->agency_id);

                tenancy()->initialize($tenant);

                $user = User::with('profile')->whereEmail($user->email)->first();
                $branches = Branch::all();

                $user->roles = $roles;
                $user->permissions = $permissions;
                $user->tenant = $tenant;
            }

            return response()->json([
                'user' => new UserResource($user),
                'branches' => new BranchesCollection($branches),
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    public function update(UpdateUserRequest $request, $id): JsonResponse
    {
        // Change connection back to central because before this it was set to tenant (refer to the edit function)
        tenancy()->end();

        $user = User::findOrFail($id)->makeVisible(['password']);
        $tenant = null;

        try {
            abort_if(!auth()->user()->hasRole('super admin') && !auth()->user()->hasRole('system admin') && !auth()->user()->hasRole('agency admin'), 403, 'Unauthorized');

            DB::beginTransaction();

            // Update user
            $user->update([
                'name' => $request->name,
                'active_status' => $request->active_status,
            ]);

            // Refresh the data
            $user = $user->refresh();

            // update role (Start)
            $roleName = is_array($request->role) ? $request->role['name'] : $request->role;

            (new UserService)->updateRole($user, $roleName);
            // update role (End)

//            $tenantUser = (new UserService)->getUserTenant($user);

            // Update user and profile data in tenant DB
            if ($user->agency_id) {

                if ($user->agency_id == $request->agency['id']) {
                    // Update without changing the agency
                    (new UserService)->updateWithoutChangeAgency($user, $request);
                } else {
                    // Update and change the agency
                    (new UserService)->destroyUserAgency($user);
                    (new UserService)->storeToNewAgency($user, $request);

                    // Update agency id and agency name in central DB
                    $user->update([
                        'agency_id' => $request->agency['id'],
                        'agency_name' => $request->agency['name'],
                    ]);
                }
            } else {
                // Update profile for central user (e.g. super admin, system admin)
                (new UserService)->updateProfile($user, $request);
            }

            // Update email central here to synchronize with the email in tenant DB
            $user->update([
                'email' => $request->email,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'User updated successfully',
                'status' => '200',
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();

            \Log::debug($th);

            // Restore user in tenant if data in central does not commit
//            if ($tenantUser && $tenant) {
//                $tenant->run(function () use ($tenantUser) {
//                    $this->updateUserTenant(collect($tenantUser));
//                });
//            }

            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        abort_if(!auth()->user()->hasRole('super admin') && !auth()->user()->hasRole('system admin') && !auth()->user()->hasRole('agency admin'), 403, 'Unauthorized');

        $user = User::findOrFail($id);
        $tenant = null;

        try {
            DB::beginTransaction();

            $userWithSameEmail = User::withTrashed()->where('email', 'like', "%{$user->email}%")->get();

            $deletedEmail = $user->email;
            if ($userWithSameEmail->count() > 1) {
                foreach ($userWithSameEmail as $user) {
                    $deletedEmail = $deletedEmail . '_deleted';
                }
            } else {
                $deletedEmail = $deletedEmail . '_deleted';
            }

            // Delete user
            $emailBeforeDelete = $user->email;
            $user->email = $deletedEmail;
            $user->save();

            // Delete profile
            if($user->profile){
                $userProfile = $user->profile;
                $nricBeforeDelete = $user->profile->id_no;

                $userProfile->update([
                    'id_no' => $nricBeforeDelete . '_deleted',
                ]);

                $userProfile->delete();
            }
            // $user->profile()->delete();
            $user->delete();

            if ($user->agency_id) {
                // Set connection to tenant
                $tenant = Tenant::find($user->agency_id);

                // Create user and profile in tenant
                $tenant->run(function () use ($user, $emailBeforeDelete, $deletedEmail) {
                    DB::beginTransaction();

                    $userTenant = User::whereEmail($emailBeforeDelete)->first();

                    // Delete user
                    $userTenant->email = $deletedEmail;
                    $userTenant->save();

                    // Delete profile
                    // $userTenant->profile()->delete();

                    if($user->profile){
                        $userProfileTenant = $user->profile;
                        $nricBeforeDeleteTenant = $user->profile->id_no;

                        $userProfileTenant->update([
                            'id_no' => $nricBeforeDeleteTenant . '_deleted',
                        ]);

                        $userProfileTenant->delete();
                    }

                    $userTenant->delete();

                    DB::commit();
                });
            }

            DB::commit();

            return response()->json([
                'message' => 'User deleted successfully',
                'status' => '200',
            ], 200);
        } catch (\Throwable $th) {

            \Log::debug($th);
            DB::rollBack();

            // Restore user in tenant if data in central does not commit
            if ($user && $tenant) {
                $tenant->run(function () use ($user) {
                    $user->withTrashed()->restore();

                    Profile::withTrashed()->whereUserId($user->id)->restore();
                });
            }

            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    public function trashed(): JsonResponse
    {
        try {
            $users = User::query()->deletedUserList(Request::input('search'));

            if (!$users) {
                return response()->json([
                    'message' => 'Forbidden',
                    'status' => 403,
                ], 403);
            }

            return response()->json([
                'users' => new UsersCollection($users),
                'pagination' => new PaginationResource($users)
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 500,
            ], 500);
        }
    }
}
