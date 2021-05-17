<?php

return (static function() {

    $configTemplate = [

        'services' => [],
        'routes' => [
//            [
//                'aggregate' => true,
//                'method' => 'GET',
//                'path' => '/v1/account/compact-profile',
//                'public' => false,
//                'actions' => [
//                    'profile' => [
//                        'service' => 'account',
//                        'method' => 'GET',
//                        'output_key' => '',
//                        'path' => 'v1/account/profile',
//                        'sequence' => 0,
//                        'critical' => true
//                    ],
//                    'orders' => [
//                        'service' => 'account',
//                        'method' => 'GET',
//                        'output_key' => 'orders',
//                        'path' => 'v1/order/orders',
//                        'sequence' => 0,
//                        'critical' => false
//                    ],
//                    'currentPackage' => [
//                        'service' => 'account',
//                        'method' => 'GET',
//                        'output_key' => 'currentPackage',
//                        'path' => 'v1/order/orders/current',
//                        'sequence' => 0,
//                        'critical' => false
//                    ]
//                ]
//            ]
        ],
        'global' => [
            'prefix' => '',
            'timeout' => 5.0,
            'doc_point' => '/docs',
            'domain' => env('APP_DNS', 'zoofy.nl'),
            "ssl" => env('SSL_ENABLED', false)
        ],
    ];

    $sections = ['services', 'routes', 'global'];

    foreach ($sections as $section) {
        $config = env('GATEWAY_' . strtoupper($section), false);
        ${$section} = $config ? json_decode($config, true) : $configTemplate[$section];
        if (${$section} === null) throw new \Exception('Unable to decode GATEWAY_' . strtoupper($section) . ' variable');
    }
    return compact($sections);
})();
