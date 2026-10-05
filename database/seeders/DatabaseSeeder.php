<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        $this->call(CentralPermissionSeeder::class);

//        $this->call(PostSeeder::class);
        $this->call(TenantSeeder::class);
        $this->call(TenantUserSeeder::class);
        $this->call(TenantPermissionSeeder::class);

        // New Seeder
        $this->call(TenantUserManualSeeder::class);
        $this->call(TenantFaqSeeder::class);
        $this->call(SysAdminMenuSeeder::class);
        $this->call(DataApplicationStatusSeeder::class);
        $this->call(AddPermissionToDataRequestAdminSeeder::class);

        // Second Batch Seeder
        $this->call(AddLevelAndStatusForCaseFileSeeder::class);

        // Third Batch Seeder
        $this->call(BranchPermissionSeeder::class);

        // Fourth Batch Seeder
        $this->call(BranchReadForSystemAdminSeeder::class);

        // Fifth Batch Seeder
        $this->call(ChatPermissionSeeder::class);

        // Sixth Batch Seeder
        $this->call(MaintenanceDeleteSeeder::class);

        // Seventh Batch Seeder
        $this->call(TenantPermissionForSuperAdmin::class);
    }
}
