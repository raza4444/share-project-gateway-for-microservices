<?php

namespace App\Http\Middleware;

use App\Services\AppClientsService;
use Closure;
use Illuminate\Validation\UnauthorizedException;

class VerifyAppId
{
    public static $APP_ID = 'APP-ID';

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $client_id = $request->header(self::$APP_ID);
        if (!$request->hasHeader(self::$APP_ID) || (new AppClientsService($client_id))->getClient() === null) {
            throw new UnauthorizedException();
        }

        return $next($request);
    }
}
