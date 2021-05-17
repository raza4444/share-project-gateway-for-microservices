<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;

/**
 * Class VerifySuperAdmin
 * @package App\Http\Middleware
 */
class VerifySuperAdmin
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        // TODO: Need to check here is user is super admin or not.
        /*if ($this->auth->guard($guard)->guest())
        {
            throw new UnauthorizedHttpException(401, 'Sorry, You can not perform this request.');
        }*/
        return $next($request);
    }
}
