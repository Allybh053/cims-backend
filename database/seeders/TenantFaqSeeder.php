<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class TenantFaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $roles = Role::whereNotIn('name', ['system admin', 'staff'])->get();

        $users = User::whereNotIn('id', [2])->get();

        $permission = Permission::where('id', 66)->first();

        foreach ($roles as $role) {
            $role->givePermissionTo($permission->name);
        }

        foreach ($users as $user) {
            $user->givePermissionTo($permission->name);
        }
    }
}
