<?php

namespace Messenger\Chat;

use Illuminate\Support\ServiceProvider;
use Messenger\Chat\Commands\MessengerCommand;
use Illuminate\Support\Facades\File;

class MessengerServiceProvider extends ServiceProvider
{
    public function register()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MessengerCommand::class
            ]);
        }

        $this->publisheRoutes();

        $this->mergeConfigFrom(
            __DIR__.'/config/messenger.php', 'messenger'
        );
    }

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/routes/channels.php');

        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        $this->loadMigrationsFrom(__DIR__.'/migrations');

        $this->publishes([
            __DIR__.'/views' => resource_path('views'),
            __DIR__.'/assets' => public_path('assets'),
        ], 'messenger');

        $this->publishes([
            __DIR__.'/config' => config_path('/'),
        ], 'messenger-config');

        $this->publishes([
            __DIR__.'/migrations' => database_path('migrations'),
        ], 'messenger-migrations');


        $this->publishes([
            __DIR__.'/views' => resource_path('views'),
        ], 'messenger-views');

        $this->publishes([
            __DIR__.'/assets' => public_path('assets'),
        ], 'messenger-assets');
    }

    public function publishRoutes()
    {
        if (stripos(file_get_contents(base_path('routes/web.php')), 'Messenger\Chat\MessengerRoutes::routes()') === false) {
            $routes = "Messenger\Chat\MessengerRoutes::routes();\n\n";
            File::append(base_path('routes/web.php'), $routes);
        }

        if (stripos(file_get_contents(base_path('routes/channels.php')), 'Messenger\Chat\MessengerRoutes::channels()') === false) {
            $channels = "Messenger\Chat\MessengerRoutes::channels();\n\n";
            File::append(base_path('routes/channels.php'), $channels);
        }
    }
}

