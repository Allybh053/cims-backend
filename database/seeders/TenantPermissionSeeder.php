<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Profile;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class TenantPermissionSeeder extends Seeder
{
    /**
     * Create the initial roles and permissions.
     *
     * @return void
     */
    public function run()
    {
        $tenant = Tenant::find('nacsa');

        $tenant->run(function () {

            // agency admin
            $agencyAdminUser = User::factory()->create([
                'name' => 'Agency Admin',
                'email' => 'agencyadmin@cims.com',
            ]);

            $branch = Branch::create([
                'name' => 'Head Office',
            ]);

            Profile::create([
                'user_id' => $agencyAdminUser->id,
                'branch_id' => $branch->id,
                'type_id' => 1,
                'id_no' => '930509102245',
                'gender' => 1,
                'designation' => 'Head of Agency',
                'dep_unit' => 'Human Resource',
                'phone_no' => '0323456789',
                'mobile_no' => '0123456789',
                'dob' => '1993-05-09',
            ]);

            // report admin
            $reportAdminUser = User::factory()->create([
                'name' => 'Report Admin',
                'email' => 'reportadmin@cims.com',
            ]);

            Profile::create([
                'user_id' => $reportAdminUser->id,
                'branch_id' => $branch->id,
                'type_id' => 1,
                'id_no' => '850416078017',
                'gender' => 1,
                'designation' => 'Supervisor',
                'dep_unit' => 'Audit',
                'phone_no' => '0323456789',
                'mobile_no' => '0123456789',
                'dob' => '1985-04-16',
            ]);

            // investigator
            $investigatorUser = User::factory()->create([
                'name' => 'Investigator',
                'email' => 'investigator@cims.com',
            ]);

            Profile::create([
                'user_id' => $investigatorUser->id,
                'branch_id' => $branch->id,
                'type_id' => 1,
                'id_no' => '951125028417',
                'gender' => 1,
                'designation' => 'Supervisor',
                'dep_unit' => 'Security',
                'phone_no' => '0323456789',
                'mobile_no' => '0123456789',
                'dob' => '1995-11-25',
            ]);

            // data request admin
            $dataRequestUser = User::factory()->create([
                'name' => 'Data Request Admin',
                'email' => 'datarequestadmin@cims.com',
            ]);

            Profile::create([
                'user_id' => $dataRequestUser->id,
                'branch_id' => $branch->id,
                'type_id' => 1,
                'id_no' => '931104104554',
                'gender' => 1,
                'designation' => 'General Manager',
                'dep_unit' => 'General Affairs',
                'phone_no' => '0323456789',
                'mobile_no' => '0123456789',
                'dob' => '1995-11-25',
            ]);
        });
    }
}
