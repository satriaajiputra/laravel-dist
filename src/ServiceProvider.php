<?php

namespace Satmaxt\LaravelDist;

use Illuminate\Support\ServiceProvider as MainServiceProvider;
use Satmaxt\LaravelDist\Commands\RunLaravelDist;

class ServiceProvider extends MainServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/laravel-dist.php',
            'laravel-dist'
        );

        $this->publishes([
            __DIR__ . '/config/laravel-dist.php' => config_path('laravel-dist.php')
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                RunLaravelDist::class,
            ]);
        }
    }
}
