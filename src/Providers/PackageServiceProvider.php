<?php

namespace JDD\Workflow\Providers;

use App\Facades\JDD;
use Illuminate\Support\ServiceProvider;
use JDD\Workflow\Bpmn\Manager;

class PackageServiceProvider extends ServiceProvider
{
    const PluginName = 'jdd/workflow';

    /**
     * If your plugin will provide any services, you can register them here.
     * See: https://laravel.com/docs/5.6/providers#the-register-method
     */
    public function register()
    {
        // Nothing is registered at this time
    }

    /**
     * After all service provider's register methods have been called, your boot method
     * will be called. You can perform any initialization code that is dependent on
     * other service providers at this time.  We've included some example behavior
     * to get you started.
     *
     * See: https://laravel.com/docs/5.6/providers#the-boot-method
     */
    public function boot()
    {
        // Workflow Engine
        $this->app->bind('workflow.engine',
            function () {
            return new Manager;
        });
        $this->publishes([
            __DIR__ . '/../../dist' => public_path('modules/' . self::PluginName),
        ], self::PluginName);
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        app('config')->prepend('plugins.javascript', '/modules/' . self::PluginName . '/vue-jdd-flow.umd.min.js');
        app('config')->push('jsonapi.models', 'JDD\Workflow\Models');
        app('config')->push('l5-swagger.paths.annotations', __DIR__ . '/../../swagger');
        app('config')->push('l5-swagger.paths.annotations', __DIR__ . '/../Models');
    }
}
