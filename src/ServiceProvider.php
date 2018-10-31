<?php

namespace Fureev\Services;

use Illuminate\Support\ServiceProvider as SP;

/**
 * Class ServiceProvider
 *
 * @package App\Services
 */
class ServiceProvider extends SP
{
    /**
     * Register services.
     *
     * @throws \Php\Support\Exceptions\InvalidConfigException
     * @throws \Php\Support\Exceptions\MissingConfigException
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/di.php', ServiceManager::configSection());

        $this->publishes([
            __DIR__ . '/config/di.php' => config_path('di.php'),
        ]);

        $this->app->singleton(app('config')->get(ServiceManager::configSection() . '.name'), function ($app) {
            return new ServiceManager($app);
        });
    }
}
