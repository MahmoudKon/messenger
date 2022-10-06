<?php

namespace Messenger\Chat;

use Illuminate\Support\ServiceProvider;
use Messenger\Chat\Commands\MessengerCommand;

class MessengerServiceProvider extends ServiceProvider
{
    public function register()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MessengerCommand::class
            ]);
        }

        $this->mergeConfigFrom(
            __DIR__.'/config/messenger.php', 'messenger'
        );
    }

    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/resources/views', 'messenger');
        // $this->loadRoutesFrom(__DIR__.'/routes/channels.php');
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        $this->loadMigrationsFrom(__DIR__.'/migrations');

        $this->publishes([
            __DIR__.'/config' => config_path('/'),
            __DIR__.'/views' => resource_path('views'),
            __DIR__.'/assets' => public_path('assets'),
            __DIR__.'/migrations' => database_path('migrations'),
        ], 'messenger');

        $this->publishes([
            __DIR__.'/migrations' => database_path('migrations'),
        ], 'messenger-migrations');

        $this->publishes([
            __DIR__.'/config' => config_path('/'),
        ], 'messenger-config');

        $this->publishes([
            __DIR__.'/views' => resource_path('views'),
        ], 'messenger-views');

        $this->publishes([
            __DIR__.'/assets' => public_path('assets'),
        ], 'messenger-assets');
    }
}

