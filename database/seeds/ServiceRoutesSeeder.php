<?php

use Faker\Provider\Uuid as Uuid;
use Illuminate\Database\Seeder;

class ServiceRoutesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $service_routes = [
            [
                'uuid' => Uuid::uuid(),
                'parent_id' => null,
                'service_id' => 102,
                'tags' => 'api, location',
                'summary' => 'Fetch tasks rates',
                'type' => 'GET',
                'path' => '/task-rates/{task_id}/locations/{lat},{lng}?from={from_datetime}',
                'params' => json_encode(
                    [
                        ['in' => 'url', 'name' => 'task_id', 'type' => 'integer', 'required' => true, 'description' => ''],
                        ['in' => 'url', 'name' => 'lat', 'type' => 'double', 'required' => true, 'description' => ''],
                        ['in' => 'url', 'name' => 'lng', 'type' => 'double', 'required' => true, 'description' => ''],
                        ['in' => 'url', 'name' => 'from_datetime', 'type' => 'datetime', 'required' => true, 'description' => ''],
                    ]
                ),
                'security' => 'OAuth2',
                'scope' => null,
            ],
            [
                'uuid' => Uuid::uuid(),
                'parent_id' => null,
                'service_id' => 102,
                'tags' => 'api',
                'summary' => 'Verify Address',
                'type' => 'POST',
                'path' => '/api/cs/v1/verify/address',
                'params' => json_encode(
                    [
                        ['in' => 'body', 'name' => 'postal_code', 'type' => 'string', 'required' => true, 'description' => ''],
                        ['in' => 'body', 'name' => 'house_number', 'type' => 'integer', 'required' => true, 'description' => ''],
                    ]
                ),
                'security' => 'OAuth2',
                'scope' => null,
            ],
            [
                'uuid' => Uuid::uuid(),
                'parent_id' => null,
                'service_id' => 102,
                'tags' => 'api',
                'summary' => 'Create Direct Appointment',
                'type' => 'POST',
                'path' => '/appointments',
                'params' => json_encode(
                    [
                        ['in' => 'body', 'name' => 'data', 'type' => 'array', 'required' => true, 'description' => '', 'items' =>
                            [
                                ['in' => 'body', 'name' => 'task_id', 'type' => 'integer', 'required' => true, 'description' => ''],
                                ['in' => 'body', 'name' => 'rate', 'type' => 'double', 'required' => true, 'description' => ''],
                                ['in' => 'body', 'name' => 'day', 'type' => 'datetime', 'required' => true, 'description' => ''],
                                ['in' => 'body', 'name' => 'user', 'type' => 'array', 'required' => true, 'description' => '', 'items' =>
                                    [
                                        ['in' => 'body', 'name' => 'first_name', 'type' => 'string', 'required' => true, 'description' => ''],
                                        ['in' => 'body', 'name' => 'last_name', 'type' => 'string', 'required' => true, 'description' => ''],
                                        ['in' => 'body', 'name' => 'telephone', 'type' => 'string', 'required' => true, 'description' => ''],
                                        ['in' => 'body', 'name' => 'email', 'type' => 'string', 'required' => true, 'description' => ''],
                                    ]
                                ],
                                ['in' => 'body', 'name' => 'comment', 'type' => 'string', 'required' => true, 'description' => ''],
                                ['in' => 'body', 'name' => 'partner_id', 'type' => 'string', 'required' => true, 'description' => ''],
                                ['in' => 'body', 'name' => 'caseNumber', 'type' => 'string', 'required' => true, 'description' => ''],
                                ['in' => 'body', 'name' => 'address', 'type' => 'array', 'required' => true, 'description' => '', 'items' =>
                                    [
                                        ['in' => 'body', 'name' => 'postal_code', 'type' => 'string', 'required' => true, 'description' => ''],
                                        ['in' => 'body', 'name' => 'suffix', 'type' => 'string', 'required' => true, 'description' => ''],
                                    ]
                                ],
                                ['in' => 'body', 'name' => 'options', 'type' => 'array', 'required' => true, 'description' => '', 'items' =>
                                    []
                                ],
                                ['in' => 'task', 'name' => 'caseNumber', 'type' => 'string', 'required' => true, 'description' => ''],
                                ['in' => 'service_fee', 'name' => 'caseNumber', 'type' => 'string', 'required' => true, 'description' => ''],
                                ['in' => 'reschedule_id', 'name' => 'caseNumber', 'type' => 'string', 'required' => true, 'description' => ''],
                                ['in' => 'coupon_code', 'name' => 'caseNumber', 'type' => 'string', 'required' => true, 'description' => ''],
                                ['in' => 'job_sources_id', 'name' => 'caseNumber', 'type' => 'integer', 'required' => true, 'description' => ''],

                            ],
                        ],
                    ]
                ),
                'security' => 'OAuth2',
                'scope' => null,
            ],
            [
                'uuid' => Uuid::uuid(),
                'parent_id' => null,
                'service_id' => 102,
                'tags' => 'tasks, api',
                'summary' => 'Get Tasks list',
                'type' => 'GET',
                'path' => '/tasks-bare-minimum',
                'params' => '{}',
                'security' => 'OAuth2',
                'scope' => null,
            ],
            [
                'uuid' => Uuid::uuid(),
                'parent_id' => null,
                'service_id' => 102,
                'tags' => 'appointments, api',
                'summary' => 'Fetch filtered appointments',
                'type' => 'GET',
                'path' => '/api/cs/v1/appointments/filter',
                'params' => json_encode(
                    ['in' => 'formData', 'name' => 'customer_id', 'type' => 'integer', 'required' => false, 'description' => '']
                ),
                'security' => 'OAuth2',
                'scope' => null,
            ],

        ];
        DB::table('service_routes')->insert($service_routes);
    }
}
