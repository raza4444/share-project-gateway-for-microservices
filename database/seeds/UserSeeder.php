<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
            [
                'name' => 'Zoofy Technology',
                'email' => 'developer@zoofy.nl',
                'password' => Hash::make('#IAMzoofytech123')
            ],
            [
                'name' => 'Nadeem Akhtar',
                'email' => 'cto@zoofy.nl',
                'password' => Hash::make('#IAMcto@zoofy123')
            ],
            [
                'name' => 'Arthur de Leeuw',
                'email' => 'arthur@zoofy.nl',
                'password' => Hash::make('#IAMceo@zoofy123')
            ],
            [
                'name' => 'Martijn Mik',
                'email' => 'martijn.mik@zoofy.nl',
                'password' => Hash::make('#IAMmarketing@zoofy123')
            ],
        ];

        DB::table('users')
            ->insert($users);
    }
}
