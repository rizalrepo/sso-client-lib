<?php

namespace Rizalrepo\SsoClient;

use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use Rizalrepo\SsoClient\Console\Commands\SSOInstallCommand;

class SSOClientServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->mergeConfigFrom(__DIR__ . '/sso.php', 'sso');

        if (config('sso.register_routes', true)) {
            $this->loadRoutesFrom(__DIR__ . '/routes/sso.php');
        }

        if ($this->app->runningInConsole()) {
            $this->commands([
                SSOInstallCommand::class,
            ]);
        }

        $this->publishes([
            __DIR__ . '/../stubs/SSOController.php' => App::path('Http/Controllers/SSO/SSOController.php'),
            __DIR__ . '/sso.php' => config_path('sso.php'),
        ], 'sso-config');
    }
}
