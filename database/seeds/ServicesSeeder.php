<?php

use Illuminate\Database\Seeder;

/**
 * Class ServicesSeeder
 */
class ServicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $services = [
            [
                'id' => 101,
                'hostname' => 'gateway',
                'url' => 'gateway.zoofy.nl',
                'secure' => 1
            ],
            [
                'id' => 102,
                'hostname' => 'core',
                'url' => 'api.zoofy.nl',
                'secure' => 1
            ],
            [
                'id' => 103,
                'hostname' => 'media',
                'url' => 'meida.zoofy.nl',
                'secure' => 1
            ],
            [
                'id' => 104,
                'hostname' => 'deliverability',
                'url' => 'deliverability.zoofy.nl',
                'secure' => 1
            ],
            [
                'id' => 105,
                'hostname' => 'chat',
                'url' => 'chat.zoofy.nl',
                'secure' => 1
            ],
        ];

        DB::table('services')->insert($services);
    }
}
