<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class BranchPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions for branch (Start)
        $branchPermissions = [
            'branch create',
            'branch update',
            'branch read',
            'branch delete',
        ];

        foreach ($branchPermissions as $permission) {
            Permission::create(['name' => $permission,  'guard_name' => 'web']);
        }
        // create permissions for branch (End)

        $roles = Role::whereIn('name', ['agency admin', 'super admin'])->get();

        foreach ($roles as $role) {
            $role->givePermissionTo($branchPermissions);

            foreach ($role->users as $user) {
                $user->givePermissionTo($branchPermissions);
            }
        }
    }
}
