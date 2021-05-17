<?php

namespace App\Providers;

use App\Http\Request;
use App\Routing\RouteRegistry;
use App\Services\DNSRegistry;
use App\Services\ServiceRegistryContract;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use App\Services\SocialUserResolver;
use Coderello\SocialGrant\Resolvers\SocialUserResolverInterface;
/**
 * Class AppServiceProvider
 * @package App\Providers
 */
class AppServiceProvider extends ServiceProvider
{

    /**
     * All of the container bindings that should be registered.
     *
     * @var array
     */
    public $bindings = [
        SocialUserResolverInterface::class => SocialUserResolver::class,
    ];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        //TODO : This need to be up don't know why it is not working. :(
        // TODO: @Rizwan
        $this->app->singleton(RouteRegistry::class, function () {
            return RouteRegistry::initFromCache();
        });

        $this->app->singleton(Request::class, function () {
            return $this->prepareRequest(Request::capture());
        });

        $this->app->bind(ServiceRegistryContract::class, DNSRegistry::class);

        $this->app->singleton(Client::class, function () {
            return new Client([
                'timeout' => Config::get('gateway.global.timeout'),
                'connect_timeout' => Config::get('gateway.global.connect_timeout', Config::get('gateway.global.timeout'))
            ]);
        });

        $this->app->alias('request', Request::class);
        $this->registerRoutes();
    }

    /**
     * Prepare the given request instance for use with the application.
     *
     * @param Request $request
     * @return  Request
     */
    protected function prepareRequest(Request $request)
    {
        $request->setUserResolver(function () {
            return $this->app->make('auth')->user();
        })->setRouteResolver(function () {
            return $this->app->currentRoute;
        })->setTrustedProxies([
            '10.7.0.0/16', // Docker Cloud
            '103.21.244.0/22', // Cloud Flare
            '103.22.200.0/22',
            '103.31.4.0/22',
            '104.16.0.0/12',
            '108.162.192.0/18',
            '131.0.72.0/22',
            '141.101.64.0/18',
            '162.158.0.0/15',
            '172.64.0.0/13',
            '173.245.48.0/20',
            '188.114.96.0/20',
            '190.93.240.0/20',
            '197.234.240.0/22',
            '198.41.128.0/17',
            '199.27.128.0/21',
            '172.31.0.0/16', // Rancher
            '10.42.0.0/16' // Rancher
        ], Request::HEADER_X_FORWARDED_ALL);
        return $request;
    }

    /**
     * @return void
     */
    protected function registerRoutes()
    {
        $registry = $this->app->make(RouteRegistry::class);
        if ($registry->isEmpty()) {
            Log::info('Not adding any service routes - route file is missing');
            return;
        }
        $registry->bind(app());
    }
}
