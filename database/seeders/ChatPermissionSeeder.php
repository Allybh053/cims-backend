<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class ChatPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions for branch (Start)
        $chatPermission = [
            'chat update',
            'chat delete',
        ];

        $messagePermission = [
            'message create',
            'message read',
            'message update',
            'message delete',
        ];

        $arrayOfPermissionNames = array_merge($chatPermission, $messagePermission);

        foreach ($arrayOfPermissionNames as $permission) {
            Permission::create(['name' => $permission,  'guard_name' => 'web']);
        }
        // create permissions for branch (End)

        $roles = Role::all();

        foreach ($roles as $role) {
            $role->givePermissionTo($arrayOfPermissionNames);

            foreach ($role->users as $user) {
                $user->givePermissionTo($arrayOfPermissionNames);
            }
        }
    }
}
