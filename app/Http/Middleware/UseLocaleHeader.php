<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;

/**
 * Class UseLocaleHeader
 * @package App\Http\Middleware
 */
class UseLocaleHeader
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->hasHeader('locale')) {
            App::setLocale($request->header('locale'));
        }

        return $next($request);
    }
}
