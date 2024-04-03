<?php

namespace rizalrepo\LaravelSSOClient;

use Illuminate\Support\ServiceProvider;

class SSOClientServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/routes.php');
        $this->publishes([
            __DIR__ . '/SSOController.php' => $this->getControllerPath('SSOController.php'),
        ], 'sso-controller');
    }

    public function register()
    {
        //
    }

    protected function getControllerPath($fileName)
    {
        return realpath(__DIR__ . '/../../app/Http/Controllers/') . '/' . $fileName;
    }
}
