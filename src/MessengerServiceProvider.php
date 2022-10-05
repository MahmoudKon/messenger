<?php

namespace Messenger\Chat;

use Illuminate\Support\ServiceProvider;
use Messenger\Chat\Commands\MessengerCommand;
use Illuminate\Support\Facades\Artisan;

class MessengerServiceProvider extends ServiceProvider
{    
    public function register()
    {
        // if ($this->app->runningInConsole()) {
        //     $this->commands([
        //         MessengerCommand::class
        //     ]);
            
        // }
    }

    public function boot()
    {        
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/routes/channels.php');
        $this->loadMigrationsFrom(__DIR__.'/migrations');

        $this->loadViewsFrom(__DIR__.'/views/messenger', 'messenger');

        $this->publishes([
            __DIR__.'/config/messenger.php' => config_path('messenger.php'),
            __DIR__.'/assets/messenger' => public_path('assets/messenger'),
        ], 'messenger');
    }
}

