<?php

namespace Database\Seeders;

use App\Models\State;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SSMStateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $states = [
            'Johor' =>['J'],
            'Kedah' => ['K'],
            'Kelantan' => ['D'],
            'Melaka' => ['M'],
            'Negeri Sembilan' => ['N'],
            'Pahang' => ['C'],
            'Perak' => ['A'],
            'Perlis' => ['R'],
            'Pulau Pinang' => ['P'],
            'Sabah' => ['S','X'],
            'Sarawak' => ['E','Y'],
            'Selangor' => ['B'],
            'Terengganu' => ['T'],
            'Wilayah Persekutuan Kuala Lumpur' => ['W'],
            'Wilayah Persekutuan Labuan' => ['L'],
            'Wilayah Persekutuan Putrajaya' => ['U'],
        ];

        // Update or create data in the State table
        foreach ($states as $state => $ssmCodes) {
            if (is_array($ssmCodes)) {
                // Convert codes to JSON array
                $codesArray = [];
                foreach ($ssmCodes as $code) {
                    $codesArray[] = ['id' => count($codesArray) + 1, 'code' => $code];
                }
                $ssmCodes = json_encode($codesArray);
            }

            State::updateOrCreate(
                ['name' => $state],
                ['ssm_code' => $ssmCodes, 'updated_at' => now()]
            );
        }
    }
}
