<?php namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;

/**
 * Class ProxyServiceProvider
 * @package App\Providers
 */
class ProxyServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $request = $this->app['request'];
        $proxies = [$request->getClientIp()];
        $request->setTrustedProxies($proxies,  Request::HEADER_X_FORWARDED_ALL);
    }
}