<?php

namespace rizalrepo\SsoClient;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;

class SSOClientServiceProvider extends ServiceProvider
{
    // public function boot()
    // {
    //     $this->publishes([
    //         __DIR__ . '/app.blade.php' => app_path('../resources/views/sso/config.blade.php'),
    //     ], 'sso-config');
    //     $this->publishes([
    //         __DIR__ . '/Authenticate.php' => app_path('Http/Middleware/Authenticate.php'),
    //     ], 'sso-config');
    //     $this->publishes([
    //         __DIR__ . '/routes.php' => app_path('../routes/sso.php'),
    //     ], 'sso-config');
    //     $this->publishes([
    //         __DIR__ . '/SSOController.php' => app_path('Http/Controllers/SSO/SSOController.php'),
    //     ], 'sso-config');
    //     $this->publishes([
    //         __DIR__ . '/users_table.php' => app_path('../database/sso/users_table_structure.php'),
    //     ], 'sso-config');
    // }

    public function boot()
    {
        $this->publishViews();
        $this->publishMiddleware();
        $this->publishRoutes();
        $this->publishControllers();
        $this->publishDatabaseStructure();
    }

    protected function publishViews()
    {
        $this->publishes([
            __DIR__ . '/app.blade.php' => App::resourcePath('views/sso/config.blade.php'),
        ], 'sso-config');
    }

    protected function publishMiddleware()
    {
        $this->publishes([
            __DIR__ . '/Authenticate.php' => App::path('Http/Middleware/Authenticate.php'),
        ], 'sso-config');
    }

    protected function publishRoutes()
    {
        $this->publishes([
            __DIR__ . '/routes.php' => App::basePath('routes/sso.php'),
        ], 'sso-config');
    }

    protected function publishControllers()
    {
        $this->publishes([
            __DIR__ . '/SSOController.php' => App::path('Http/Controllers/SSO/SSOController.php'),
        ], 'sso-config');
    }

    protected function publishDatabaseStructure()
    {
        $this->publishes([
            __DIR__ . '/users_table.php' => App::basePath('database/sso/users_table_structure.php'),
        ], 'sso-config');
    }

    public function register()
    {
        //
    }
}
