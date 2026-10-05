<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TenantPermissionForSuperAdmin extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenants = Permission::whereIn('id', [14, 15, 16, 17])->get();

        $role = Role::where('name', 'super admin')->first();

        foreach ($tenants as $tenant) {
            $role->givePermissionTo($tenant->name);

            foreach ($role->users as $user) {
                $user->givePermissionTo($tenant->name);
            }
        }
    }
}
