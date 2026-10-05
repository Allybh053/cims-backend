<?php

namespace App\Http\Resources\User;

use App\Http\Resources\PermissionsCollection;
use App\Models\Branch;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class UsersCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request)
    {
        return $this->collection->map(function ($user) {

            $permissions = $user->permissions->pluck('name')->toArray();

            $branch = $this->findBranch($user);

            $media = $user->getFirstMedia('profile_image');

            $path = null;
            if ($media) {
                $path = $media->getFullUrl();
            }

            $roleName = '-';
            foreach ($user->roles as $role) {
                if ($role->name != 'staff') {
                    $roleName = $role->name;
                }
            }

            $email = $user->email;
            $emailExplode = explode('_deleted', $email);

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $emailExplode[0] ?? $user->email,
                'image' => $path,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
                'role' => ucwords($roleName),
                'roles' => $user->roles->pluck('name')->toArray(),
                'permissions' =>$permissions,
                'agency_id' => $user->agency_id ?? 'central',
                'agency_name' => $user->agency_name ?? 'Central',
                'branch_name' => $branch->name ?? '-',
                'date_deleted' => date_format(date_create($user->deleted_at ), 'd-m-Y'),
//                'path' => $path,
            ];
        });
    }

    // Function where we find the branch of the user
    private function findBranch($user)
    {
        $branch = null;
        // Change connection based on where the data is stored in db
        if ($user->agency_id) {
            $tenant = Tenant::find($user->agency_id);
            tenancy()->initialize($tenant);
        }

        // This code determine if the user is a tenant or not. If the user is in tenant db, it will get id in tenant db
        // in order to get the name of the branch that is stored in tenant db
        $user = User::with('profile')->whereEmail($user->email)->first();

        // Find Branch
        if ($user && $user->profile) {
            $branch = Branch::find($user->profile->branch_id);
        }

        tenancy()->end();

        return $branch;
    }
}
