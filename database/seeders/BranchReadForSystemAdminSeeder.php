<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class BranchReadForSystemAdminSeeder extends Seeder
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
            'branch read',
        ];

        $roles = Role::whereIn('name', ['system admin'])->get();

        foreach ($roles as $role) {
            $role->givePermissionTo($branchPermissions);

            foreach ($role->users as $user) {
                $user->givePermissionTo($branchPermissions);
            }
        }
    }
}
