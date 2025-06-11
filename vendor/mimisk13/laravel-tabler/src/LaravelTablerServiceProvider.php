<?php

namespace MimisK13\LaravelTabler;

use Illuminate\Support\ServiceProvider;

class LaravelTablerServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'mimisk13');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'mimisk13');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        $this->commands([
            Console\InstallCommand::class,
        ]);

        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
        //$this->mergeConfigFrom(__DIR__.'/../config/laravel-tabler.php', 'laravel-tabler');

        // Register the service the package provides.
        /**
        $this->app->singleton('laravel-tabler', function ($app) {
            return new LaravelTabler;
        });
        */
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        //return ['laravel-tabler'];
        return [
            Console\InstallCommand::class
        ];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole(): void
    {
        /*
        $this->publishes([
            __DIR__.'/../config/laravel-tabler.php' => config_path('laravel-tabler.php'),
        ], 'laravel-tabler.config');

        $this->publishes([
            __DIR__.'/../stubs/default/resources/views/layouts' => base_path('resources/views/mimisk13/tabler/views/layouts'),
        ], 'tabler-layout-default');

        $this->publishes([
            __DIR__.'/../stubs/default/resources/views/errors' => base_path('resources/views/mimisk13/tabler/views/errors'),
        ], 'tabler-errors-default');

        // Components
        $this->publishes([
            __DIR__.'/../stubs/default/resources/views/components' => base_path('resources/views/mimisk13/tabler/views/components'),
        ], 'tabler.components');

        $this->publishes([
            __DIR__.'/../stubs/default/vite.config.js' => base_path(''),
        ], 'tabler.vite-config');
        */

        // Publishing assets.
//        $this->publishes([
//            __DIR__.'/../resources/assets' => public_path('vendor/mimisk13'),
//        ], 'laravel-tabler.assets');


        // Registering package commands.
        // $this->commands([]);
    }
}
