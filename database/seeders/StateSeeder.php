<?php

namespace Database\Seeders;

use App\Models\State;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        State::insert([
            ['name' => 'Johor', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Kedah', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Kelantan', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Melaka', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Negeri Sembilan', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Pahang', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Perak', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Perlis', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Pulau Pinang', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sabah', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sarawak', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Selangor', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Terengganu', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Wilayah Persekutuan Kuala Lumpur', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Wilayah Persekutuan Labuan', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Wilayah Persekutuan Putrajaya', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
