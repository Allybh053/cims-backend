<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\TenantInfo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get a list of all databases
        $db_all = DB::connection()->select('SHOW DATABASES');

        // Databases to exclude
        $db_excluded = ['cims_central'];

        foreach ($db_all as $db) {
            $db_name = $db->Database;

            // Check if the database starts with 'cims_' and is not in the excluded list
            if (strpos($db_name, 'cims_') === 0 && !in_array($db_name, $db_excluded)) {
                // Drop all the cims_tenant database
                DB::statement("DROP DATABASE $db_name");
                // Log::info("Database '$db_name' dropped.");
            }
        }

        $tenant = Tenant::create([
            'id' => 'nacsa',
            'agency_name' => 'National Cyber Security Agency (NACSA)',
            'status' => true,
        ]);


        $tenant->domains()->create(['domain' => 'nacsa.localhost']);

        TenantInfo::create([
            'tenant_id' => $tenant->id,
            'address_1' => 'National Security Council',
            'address_2' => 'Federal Government Administrative Center,',
            'postcode' => '62502',
            'city' => 'Putrajaya',
            'state' => 'Wilayah Persekutuan Putrajaya',
            'country' => 'Malaysia',
            'phone_no' => '0322662222',
            'fax_no' => '0320707500',
            'website' => 'https://www.rmp.gov.my',
            'email' => 'admin[at]nacsa[dot]gov[dot]my',
            'rep_type_id' => 1,
            'rep_id_no' => '800101-01-0001',
            'rep_name' => 'Admin',
            'rep_designation' => 'Pengarah',
            'rep_dept_unit' => 'Bahagian Pentadbiran',
            'rep_email' => 'admin[at]nacsa[dot]gov[dot]my',
            'rep_phone_no' => '0322662222',
            'rep_mobile_no' => '0123456789',
        ]);
    }
}
