<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class AddPermissionToDataRequestAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $roles = Role::whereIn('name', ['data request admin'])->get();

        $users = User::whereIn('id', [6])->get();

        $permission = Permission::where('id', 36)->first();

        foreach ($roles as $role) {
            $role->givePermissionTo($permission->name);
        }

        foreach ($users as $user) {
            $user->givePermissionTo($permission->name);
        }
    }
}
