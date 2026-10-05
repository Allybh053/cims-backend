<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TenantUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::whereIn('id', [3, 4, 5, 6])->get();
        $tenant = Tenant::find('nacsa');

        foreach ($users as $user) {
            $user->agency_id = $tenant->id;
            $user->agency_name = $tenant->agency_name;
            $user->save();
        }
    }
}
