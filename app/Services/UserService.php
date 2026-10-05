<?php

namespace App\Services;

use App\Models\Profile;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class UserService
{
    public function storeUserTenant($user, $request, $encryptedPassword): void
    {
        // Create user in tenant db
        $userTenant = User::create([
            'name' => $user->name,
            'email' => $user->email,
            'uuid' => $user->uuid,
            'password' => $encryptedPassword,
        ]);

        // Called function for create profile in tenant db
        $this->storeProfile($userTenant, $request);
    }

    public function storeProfile($user, $request): void
    {
        // Create profile in tenant db
        Profile::create([
            'user_id' => $user->id,
            'branch_id' => $request->branch_name,
            'type_id' => $request->id_type,
            'id_no' => $request->id_no,
            'designation' => $request->designation,
            'dep_unit' => $request->dep_unit,
            'phone_no' => $request->phone_no ?? "",
            'mobile_no' => $request->mobile_no,
            'dob' => $request->dob,
            'gender' => $request->gender,
        ]);
    }

    public function updateUserTenant($user, $request = null): void
    {
        $id = $user instanceof Collection ? $user->get('id') : $user->id;

        User::whereId($id)->update([
            'name' => $request->name ?? $user->get('name'),
            'email' => $request->email ?? $user->get('email'),
            'active_status' => $request->active_status ?? $user->get('active_status'),
        ]);

        // Create profile
        $this->updateProfile($user, $request);
    }

    public function updateProfile($user, $request): void
    {
        $id = $user instanceof Collection ? $user->get('id') : $user->id;

        Profile::whereUserId($id)->update([
            'branch_id' => $this->checkingBranchData($user, $request),
            'type_id' => $request->id_type ?? $user->get('profile')['type_id'],
            'id_no' => $request->id_no ?? $user->get('profile')['id_no'],
            'designation' => $request->designation ?? $user->get('profile')['designation'],
            'dep_unit' => $request->dep_unit ?? $user->get('profile')['dep_unit'],
            'phone_no' => $request->phone_no ?? $user->get('profile')['phone_no'],
            'mobile_no' => $request->mobile_no ?? $user->get('profile')['mobile_no'],
            'dob' => $request->dob ?? $user->get('profile')['dob'],
            'gender' => $request->gender ?? $user->get('profile')['gender'],
        ]);
    }

    public function checkingBranchData($user, $request)
    {
        if ($user instanceof Collection) {
            return $user->get('profile')['branch_id'];
        } else if ($user instanceof Model) {
            return $request->branch_name;
        }

        return null;
    }

    public function getUserTenant($user)
    {
        $userTenant = null;
        if ($user->agency_id) {
            $tenant = Tenant::find($user->agency_id);

            tenancy()->initialize($tenant);

            $userTenant = User::with('profile')->whereEmail($user->email)->first()->toArray();

            tenancy()->end();
        }

        return $userTenant;
    }

    public function updateRole($user, $roleName): void
    {
        if (!$user->hasRole(strtolower($roleName))) {

            // Detach all roles and permissions
            $user->roles()->detach();
            $user->permissions()->detach();

            // Get Role
            $role = Role::where('name', strtolower($roleName))->first();

            $user->assignRole($role->name);
            $user->assignRole('staff');

            // Assign permissions to user
            $user->givePermissionTo($role->permissions->pluck('name'));
        }
    }

    public function destroyUserAgency($user): void
    {
        $tenant = Tenant::find($user->agency_id);
        tenancy()->initialize($tenant);

        $userInTenantDB = User::whereEmail($user->email)->first();

        $userInTenantDB->delete();
        $userInTenantDB->profile->delete();

        tenancy()->end();
    }

    public function storeToNewAgency($user, $request): void
    {
        // Set connection to tenant
        $tenant = Tenant::find($request->agency['id']);

        $encryptedPassword = $user->password;

        // Create user and profile in tenant
        $tenant->run(function () use ($user, $request, $encryptedPassword) {
            DB::beginTransaction();

            $this->storeUserTenant($user, $request, $encryptedPassword);

            DB::commit();
        });
    }

    public function updateWithoutChangeAgency($user, $request): void
    {
        // Set connection to tenant
        $tenant = Tenant::find($user->agency_id);

        // Update user and profile in tenant
        $tenant->run(function () use ($user, $request) {
            DB::beginTransaction();

            $tenantUser = User::whereEmail($user->email)->first();

            $this->updateUserTenant($tenantUser, $request);

            DB::commit();
        });
    }
}
