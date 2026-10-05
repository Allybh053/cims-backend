<?php

namespace Database\Seeders;

use App\Models\FileLevel;
use App\Models\FileStatus;
use App\Models\Tenant;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AddLevelAndStatusForCaseFileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            'open',
            'close',
            'archive',
        ];

        $levels = [
            'open',
            'limited',
            'secret',
            'top secret',
        ];

        $tenant = Tenant::find('nacsa');

        $tenant->run(function () use ($statuses, $levels) {
            foreach ($statuses as $status) {
                FileStatus::create([
                    'status_name' => $status,
                ]);
            }

            foreach ($levels as $level) {
                FileLevel::create([
                    'level_name' => $level,
                ]);
            }
        });
    }
}
