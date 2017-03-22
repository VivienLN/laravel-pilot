<?php

namespace VivienLN\Pilot;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use VivienLN\Pilot\Console\PilotDenyCommand;
use VivienLN\Pilot\Console\PilotGrantCommand;
use VivienLN\Pilot\Console\PilotInstallCommand;

class PilotServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {

        // Bind services (before controllers and routes)
        $this->app->singleton(Pilot::class, function() {
            return new Pilot(config('pilot'));
        });

        // Register routes
        include __DIR__.'/routes.php';

        // Register controllers
        $this->app->make('VivienLN\Pilot\Controllers\PilotController');

        // Register console commands
        $this->commands([
            PilotInstallCommand::class,
        ]);
        $this->commands([
            PilotGrantCommand::class,
        ]);
        $this->commands([
            PilotDenyCommand::class,
        ]);
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(Pilot $pilot)
    {
        // Get migrations
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        // Get routes
        $this->loadViewsFrom(__DIR__.'/resources/views', 'pilot');

        // Copy config (with tag "config")
        $this->publishes([
            __DIR__.'/config.php' => config_path('pilot.php'),
        ], 'config');

        // Copy CSS/JS files
        $this->publishes([
            __DIR__.'/public/css/pilot.min.css' => public_path('vendor/pilot/css/pilot.min.css'),
            __DIR__.'/public/js/pilot.min.js' => public_path('vendor/pilot/js/pilot.min.js'),
            __DIR__.'/public/img/trumbowyg/icons.svg' => public_path('vendor/pilot/img/trumbowyg.svg'),
        ], 'public');

        // view composer
        View::composer($pilot->getViews(), PilotComposer::class);
    }
}
