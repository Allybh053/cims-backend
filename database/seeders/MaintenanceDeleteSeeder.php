<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class MaintenanceDeleteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions for branch (Start)
        $maintenancePermissions = [
            'maintenance delete',
        ];

        foreach ($maintenancePermissions as $permission) {
            Permission::create(['name' => $permission,  'guard_name' => 'web']);
        }

        $roles = Role::whereIn('name', ['system admin'])->get();

        foreach ($roles as $role) {
            $role->givePermissionTo($maintenancePermissions);

            foreach ($role->users as $user) {
                $user->givePermissionTo($maintenancePermissions);
            }
        }

    }
}
