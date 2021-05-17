<?php

use Illuminate\Database\Seeder;

class UserRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $userRoles = [
            [
                'user_id' => 1,
                'role_id' => 1
            ],
            [
                'user_id' => 2,
                'role_id' => 1
            ],
            [
                'user_id' => 3,
                'role_id' => 1
            ],
            [
                'user_id' => 4,
                'role_id' => 1
            ]
        ];
        DB::table('user_roles')->insert($userRoles);
    }
}
