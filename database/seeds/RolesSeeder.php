<?php

use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = [
            [
                'title' => 'Admin',
            ],
            [
                'title' => 'Agent',
            ],
            [
                'title' => 'Professional',
            ],
            [
                'title' => 'Consumer',
            ],
            [
                'title' => 'Partner',
            ],
        ];
        DB::table('roles')->insert($roles);
    }
}
