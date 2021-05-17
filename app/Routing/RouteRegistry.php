<?php

namespace App\Routing;

use App\Models\Services;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Cache;
use Webpatser\Uuid\Uuid;

/**
 * Class RouteRegistry
 * @package App\Routing
 */
class RouteRegistry
{
    /**
     * @var array
     */
    protected $routes = [];

    /**
     * RouteRegistry constructor.
     */
    public function __construct()
    {
        //$this->parseConfigRoutes();
    }

    /**
     * @param RouteContract $route
     */
    public function addRoute(RouteContract $route)
    {
        $this->routes[] = $route;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->routes);
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getRoutes()
    {
        return collect($this->routes);
    }

    /**
     * @param string $id
     * @return RouteContract
     */
    public function getRoute($id)
    {
        return collect($this->routes)->first(function ($route) use ($id) {
            return $route->getId() == $id;
        });
    }

    /**
     * @param Application $app
     */
    public function bind(Application $app)
    {
        $this->getRoutes()->each(function ($route) use ($app) {
            $method = strtolower($route->getMethod());
            $middleware = ['helper:' . $route->getId()];
           // if (!$route->isPublic()) $middleware[] = 'auth';
            $app->router->{$method}($route->getPath(), [
                'uses' => 'App\Http\Controllers\GatewayController@' . $method,
                'middleware' => $middleware
            ]);
        });
    }

    /**
     * @return $this
     */
    private function parseConfigRoutes()
    {
        $config = config('gateway');
        if (empty($config)) return $this;
        $this->parseRoutes($config['routes']);
        return $this;
    }

    /**
     * @return static
     */
    public static function initFromCache()
    {
        $registry = new self();
        $cache = Cache::get('dynamicRoutes');
        if ($cache !== null) {
            $routes = json_decode($cache, true);
        } else {
            $services = Services::with('routes')
                ->get();
            $routes = collect($services)->map(function ($service) {
                return collect($service->routes)->map(function ($route) use ($service) {
                    return [
                        'id' => $route->uuid,
                        'method' => $route->type,
                        'path' => $route->path,
                        'public' => ($route->security === 'Public'),
                        'actions' => count($route->children) > 0 ? collect($route->children)->map(function ($route) use ($service) {
                            return self::getRouteDetails($route, $service);
                        }) : [
                            [
                                'id' => $route->uuid,
                                'method' => $route->type,
                                'tags' => $route->tags,
                                'summary' => $route->summary,
                                'service' => $service->hostname,
                                'serviceUrl' => ($service->secure > 0 ? 'https://' : 'http://') . $service->url,
                                'path' => $route->path,
                                'critical' => true
                            ]
                        ]
                    ];
                });

            })->toArray();
            Cache::put('dynamicRoutes', json_encode($routes), now()->addMinutes(10));
        }
        return $registry->parseRoutes($routes);
    }

    /**
     * @param $route
     * @param $service
     * @return array
     */
    private static function getRouteDetails($route, $service)
    {
        return [
            'id' => $route->uuid,
            'method' => $route->type,
            'tags' => $route->tags,
            'summary' => $route->summary,
            'service' => $service->hostname,
            'serviceUrl' => $service->url,
            'path' => $route->path,
            'critical' => true
        ];
    }

    /**
     * @param array $routes
     * @return $this
     */
    private function parseRoutes(array $routes, $override = false)
    {
        if ($override) {
            dd($routes);
        }

        collect($routes)->each(function ($route) {
            collect($route)->each(function ($routeDetails) {
                if (!isset($routeDetails['id'])) {
                    $routeDetails['id'] = (string)Uuid::generate(4);
                }

                $route = new Route($routeDetails);
                collect($routeDetails['actions'])->each(function ($action, $alias) use ($route) {
                    $route->addAction(new Action(array_merge($action, ['alias' => $alias])));
                });
                $this->addRoute($route);
            });
        });

        return $this;
    }
}
