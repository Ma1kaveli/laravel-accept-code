<?php

namespace AcceptCode\Providers;

use AcceptCode\Console\Commands\CMigrateAcceptCode;

use Illuminate\Support\ServiceProvider;

class AcceptCodeServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register config
        $this->mergeConfigFrom(
            __DIR__.'/../../config/accept-code.php',
            'accept-code'
        );
    }

    public function boot()
    {
        // Register migration without publication
        $this->loadMigrationsFrom(__DIR__.'/../Database/migrations');

        // Publisg config
        $this->publishes([
            __DIR__.'/../../config/accept-code.php' => config_path('accept-code.php'),
        ], 'accept-code-config');

        // Registret console commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                CMigrateAcceptCode::class
            ]);
        }
    }
}