<?php

namespace Javaabu\Customs;

use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;

class CustomsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->app->singleton(Customs::class, function () {
            $config = $this->app['config']['services.customs'];
            $username = Arr::get($config, 'username');
            $password = Arr::get($config, 'password');
            $url = Arr::get($config, 'url');
            $client_options = Arr::get($config, 'client_options');

            return new Customs($username ?: '', $password ?: '', $url ?: null, $client_options ?: []);
        });

        // Register the main class to use with the facade
        $this->app->singleton('customs', function () {
            return $this->app->make(Customs::class);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [Customs::class];
    }
}
