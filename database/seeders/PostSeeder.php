<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PostSeeder extends Seeder
{
    /**
     * Create the initial roles and permissions.
     *
     * @return void
     */
    public function run()
    {
        $faker = \Faker\Factory::create();
        $user = User::first();
        for($count=0;$count<10;$count++) {
            DB::table('posts')->insert([
                'title' => $faker->text($maxNbChars = 50),
                'text' => $faker->text($maxNbChars = 50),
                'user_id' => $user->id,
            ]);
        }

    }
}
