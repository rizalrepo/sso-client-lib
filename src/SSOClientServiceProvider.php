<?php

namespace rizalrepo\SsoClient;

use Illuminate\Support\ServiceProvider;

class SSOClientServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishConfig();
    }

    protected function publishConfig()
    {
        $this->publishes([
            __DIR__ . '/SSOController.php' => app_path('Http/Controllers/SSO/SSOController.php'),
            __DIR__ . '/sso.php' => config_path('sso.php'),
        ], 'sso-config');
    }

    public function register()
    {
        //
    }
}
