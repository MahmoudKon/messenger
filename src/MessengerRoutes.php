<?php

namespace Messenger\Chat;

class MessengerRoutes
{
    public static function routes()
    {
        require __DIR__.'/routes/web.php';
    }

    public static function channels()
    {
        require __DIR__.'/routes/channels.php';
    }
}

