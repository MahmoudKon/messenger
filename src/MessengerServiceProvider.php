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
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/routes/channels.php');

        $this->publishes([
            __DIR__.'/config/messenger.php' => config_path('messenger.php'),
            __DIR__.'/assets/messenger' => public_path('assets/messenger'),
            __DIR__.'/migrations' => database_path('migrations'),
            __DIR__.'/views' => resource_path('views'),
        ], 'messenger');
    }
}

