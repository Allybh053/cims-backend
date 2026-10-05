<?php

namespace Database\Seeders;

use App\Models\DataApplicationStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DataApplicationStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            'confirm',
            'draft',
            'approved',
            'rejected',
            'complete',
            'incomplete',
        ];

        foreach ($statuses as $status) {
            DataApplicationStatus::create(['status_name' => $status]);
        }
    }
}
