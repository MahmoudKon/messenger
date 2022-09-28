<?php

namespace Messenger\Chat;

use Illuminate\Support\ServiceProvider;
use Messenger\Chat\Commands\MessengerCommand;
use Illuminate\Support\Facades\Artisan;

class MessengerServiceProvider extends ServiceProvider
{    
    public function register()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MessengerCommand::class
            ]);
            
        }
    }

    public function boot()
    {        
        //
    }
}

