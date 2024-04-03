<?php

namespace rizalrepo\SsoClient;

use Illuminate\Support\ServiceProvider;

class SSOClientServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/sso.php', 'sso');
        $this->loadRoutesFrom(__DIR__ . '/../config/sso.php', 'web');

        $this->publishes([
            __DIR__ . '/SSOController.php' => app_path('Http/Controllers/SSO/SSOController.php'),
        ], 'sso-controller');
    }

    public function register()
    {
        //
    }
}
