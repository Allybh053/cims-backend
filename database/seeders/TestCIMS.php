<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TestCIMS extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

//        $superAdminRole = Role::where('name', 'super admin')->first();
        $systemAdminRole = Role::where('name', 'system admin')->first();
        $investigatorRole = Role::where('name', 'investigator')->first();
        $reportAdminRole = Role::where('name', 'report admin')->first();
        $dataRequestAdminRole = Role::where('name', 'data request admin')->first();
        $agencyAdminRole = Role::where('name', 'agency admin')->first();

        // Penyelaras Sistem
        $systemAdmin = User::factory()->create([
            'name' => 'Mazlina bt Zammani',
            'email' => 'mazlina@mkn.gov.my',
        ]);

        Profile::create([
            'user_id' => $systemAdmin->id,
            'type_id' => 1,
            'id_no' => '910416078010',
            'gender' => 1,
            'designation' => 'System Admin',
            'dep_unit' => 'IT',
            'phone_no' => '0376521123',
            'mobile_no' => '0176523023',
            'dob' => '1991-04-16',
        ]);

        $systemAdmin->assignRole($systemAdminRole);
        $systemAdmin->givePermissionTo($systemAdminRole->permissions->pluck('name'));


        // Investigator
        $investigator = User::factory()->create([
            'name' => 'Haziq Kamal Bin Mukhelas',
            'email' => 'haziqkamal@mkn.gov.my',
            'agency_id' => 'nacsa',
            'agency_name' => 'National Cyber Security Agency (NACSA)',
        ]);

        $investigator->assignRole($investigatorRole);
        $investigator->givePermissionTo($investigatorRole->permissions->pluck('name'));

        // Investigator
        $investigator2 = User::factory()->create([
            'name' => 'Mohamed Kheirulnaim',
            'email' => 'kheirulnaim@mkn.gov.my',
            'agency_id' => 'nacsa',
            'agency_name' => 'National Cyber Security Agency (NACSA)',
        ]);

        $investigator2->assignRole($investigatorRole);
        $investigator2->givePermissionTo($investigatorRole->permissions->pluck('name'));

        // Agency Admin
        $agencyAdmin = User::factory()->create([
            'name' => 'Aieda',
            'email' => 'nooraieda@mkn.gov.my',
            'agency_id' => 'nacsa',
            'agency_name' => 'National Cyber Security Agency (NACSA)',
        ]);

        $agencyAdmin->assignRole($agencyAdminRole);
        $agencyAdmin->givePermissionTo($agencyAdminRole->permissions->pluck('name'));

        // Data Request Admin
        $dataRequestAdmin = User::factory()->create([
            'name' => 'Mardoni Anak Bani',
            'email' => 'mardoni@mkn.gov.my',
            'agency_id' => 'nacsa',
            'agency_name' => 'National Cyber Security Agency (NACSA)',
        ]);

        $dataRequestAdmin->assignRole($dataRequestAdminRole);
        $dataRequestAdmin->givePermissionTo($dataRequestAdminRole->permissions->pluck('name'));

        // Penyelaras Laporan
        $reportAdmin = User::factory()->create([
            'name' => 'Nur Dinie Izzati binti Zulkapli',
            'email' => 'nurdinie@mkn.gov.my',
            'agency_id' => 'nacsa',
            'agency_name' => 'National Cyber Security Agency (NACSA)',
        ]);

        $reportAdmin->assignRole($reportAdminRole);
        $reportAdmin->givePermissionTo($reportAdminRole->permissions->pluck('name'));

        // Initialize Tenant
        $tenant = Tenant::find('nacsa');

        tenancy()->initialize($tenant);

        // Investigator
        $investigator = User::factory()->create([
            'name' => 'Haziq Kamal Bin Mukhelas',
            'email' => 'haziqkamal@mkn.gov.my',
        ]);

        // Investigator
        $investigator2 = User::factory()->create([
            'name' => 'Mohamed Kheirulnaim',
            'email' => 'kheirulnaim@mkn.gov.my',
        ]);

        // Agency Admin
        $agencyAdmin = User::factory()->create([
            'name' => 'Aieda',
            'email' => 'nooraieda@mkn.gov.my',
        ]);

        // Data Request Admin
        $dataRequestAdmin = User::factory()->create([
            'name' => 'Mardoni Anak Bani',
            'email' => 'mardoni@mkn.gov.my',
        ]);

        // Penyelaras Laporan
        $reportAdmin = User::factory()->create([
            'name' => 'Nur Dinie Izzati binti Zulkapli',
            'email' => 'nurdinie@mkn.gov.my',
        ]);

        Profile::create([
            'user_id' => $investigator->id,
            'type_id' => 1,
            'id_no' => '850416078010',
            'gender' => 1,
            'designation' => 'Investigator',
            'dep_unit' => 'IT',
            'phone_no' => '0376521123',
            'mobile_no' => '0176523023',
            'dob' => '1985-04-16',
        ]);
        Profile::create([
            'user_id' => $investigator2->id,
            'type_id' => 1,
            'id_no' => '870416078010',
            'gender' => 1,
            'designation' => 'Investigator',
            'dep_unit' => 'IT',
            'phone_no' => '0376521123',
            'mobile_no' => '0176523023',
            'dob' => '1987-04-16',
        ]);
        Profile::create([
            'user_id' => $agencyAdmin->id,
            'type_id' => 1,
            'id_no' => '890416078010',
            'gender' => 1,
            'designation' => 'Agency Admin',
            'dep_unit' => 'IT',
            'phone_no' => '0376521123',
            'mobile_no' => '0176523023',
            'dob' => '1989-04-16',
        ]);
        Profile::create([
            'user_id' => $dataRequestAdmin->id,
            'type_id' => 1,
            'id_no' => '930416078010',
            'gender' => 1,
            'designation' => 'Data Request Admin',
            'dep_unit' => 'IT',
            'phone_no' => '0376521123',
            'mobile_no' => '0176523023',
            'dob' => '1993-04-16',
        ]);
        Profile::create([
            'user_id' => $reportAdmin->id,
            'type_id' => 1,
            'id_no' => '990416078010',
            'gender' => 1,
            'designation' => 'Data Request Admin',
            'dep_unit' => 'IT',
            'phone_no' => '0376521123',
            'mobile_no' => '0176523023',
            'dob' => '1999-04-16',
        ]);


    }
}
