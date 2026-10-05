<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class SysAdminMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $roles = Role::whereIn('name', ['system admin'])->get();

        $users = User::whereIn('id', [2])->get();

        $permissions = Permission::whereIn('id', [62, 63, 64, 65])->pluck('name')->toArray();

        foreach ($roles as $role) {
            $role->givePermissionTo($permissions);
        }

        foreach ($users as $user) {
            $user->givePermissionTo($permissions);
        }
    }
}
