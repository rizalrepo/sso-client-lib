<?php

namespace rizalrepo\SsoClient;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class SSOClientServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishControllers();
        $this->publishConfig();
    }

    protected function publishControllers()
    {
        $this->publishes([
            __DIR__ . '/SSOController.php' => App::path('Http/Controllers/SSO/SSOController.php'),
        ], 'sso-config');
    }

    protected function publishConfig()
    {
        $this->publishes([
            __DIR__ . '/sso.php' => Config::path('sso.php'),
        ], 'sso-config');
    }
}
