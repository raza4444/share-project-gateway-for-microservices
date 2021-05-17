<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\Services;
use App\Models\ServiceRoutes;

class SwaggerGenerate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gateway:swagger {--service=} {--only=}';

    /**
     * The console command option.
     *
     * @var string
     */
    protected $service;
    protected $only;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate specific/all swagger documentation for API';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->service = !empty($this->option('service')) ? $this->option('service') : 'all';
        $this->only = !empty($this->option('only')) ? $this->option('only') : 'all';

        $service = [];
        $routes = [];

        if ($this->service == 'all') {
            $this->service = 'zoofy';
            $routes = ServiceRoutes::all();
        } else {
            $service = Services::where('hostname', $this->service)->first();
            if (!empty($service)) {
                $query = ServiceRoutes::whereIn('service_id', [101, $service->id]);
                if ($this->only != 'all') {
                    $query->where('tags', 'LIKE', '%' . $this->only . '%');
                }
                $routes = $query->get();
            } else {
                print 'No service found with this hostname.';
                return false;
            }
        }

        /**
         * Create json file in storage
         */
        if (!empty($routes)) {
            $routes = $this->generate($service, $routes);
            $file = $this->service . '-' . $this->only . '-services.json';

            // check if file already exists
            if (Storage::disk('swagger')->exists($file)) {
                Storage::disk('swagger')->delete($file);
            }

            Storage::disk('swagger')->put($file, $routes);
            print Storage::disk('swagger')->path($file);
        } else {
            print 'No APIs found for this service.';
            return false;
        }

        return true;
    }

    /**
     * Generate swagger readable json
     * @param array $data
     */
    public function generate($service = [], $routes = [])
    {
        $swaggerData = [
            'swagger' => '2.0',
            'info' => [
                'description' => 'Zoofy platform services file generated at ' . Carbon::now(),
                'title' => !empty($service) ? strtoupper($service->hostname . ' ' . $this->only) : 'Zoofy',
                'version' => 'v1',
            ],
            'securityDefinitions' => [
                'bearerAuth' => [
                    'description' => "Standard Authorization header using the Bearer scheme. Example: \"Bearer {token}\"",
                    'type' => 'apiKey',
                    'in' => 'header',
                    'name' => 'Authorization'
                ]
            ],
            'host' => env('SWAGGER_HOST', 'gateway-dev.zoofy.nl'),
            'schemes' => !empty($service) && $service->secure == 1 ? ['https'] : ['http'],
            'basePath' => '/'
        ];

        foreach ($routes as $row) {
            if (strtolower($row->security) == 'public') {
                $security = [];
            } elseif (strtolower($row->security) == 'oauth2') {
                $security = [['bearerAuth' => !empty($row->scope) ? [$row->scope] : []]];
            } else {
                $security = [[$row->security => !empty($row->scope) ? [$row->scope] : []]];
            }
            $swaggerData['paths'][$row->path][strtolower($row->type)] = [
                'security' => $security,
                'summary' => !empty($row->summary) ? $row->summary : '',
                'parameters' => !empty($row->params) ? $row->params : [],
                'produces' => !empty($row->produces) ? [$row->produces] : [],
                'responses' => [
                    200 => [
                        'description' => 'OK'
                    ],
                    400 => [
                        'description' => 'Bad Request'
                    ],
                    401 => [
                        'description' => 'Authorization Failed'
                    ],
                    404 => [
                        'description' => 'Not Found'
                    ]
                ],
                'tags' => $this->only == 'all' ? array_map('trim', explode(',', $row->tags)) : [$this->only]
            ];
        }

        return json_encode($swaggerData);
    }
}
