<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Route;

class VerifyCsrfToken extends BaseVerifier
{

    protected $ignore_prefix= [
        'api'
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     * @throws TokenMismatchException
     */
    public function handle($request, Closure $next)
    {
        if ($this->isReading($request) || $this->tokensMatch($request) || $this->isDisabledCSRF($request)) {
            return $this->addCookieToResponse($request, $next($request));
        }

        throw new TokenMismatchException;
    }

    /**
     * Determine if the route needs to disable CSRF
     *
     * @param $request
     *
     * @return bool
     */
    protected function isDisabledCSRF($request)
    {
        return in_array($request->segment(1), $this->ignore_prefix);
    }
}
