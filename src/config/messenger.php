<?php

    return [
        // Model class 
        'model'         => config('auth.providers.users.model'),

        // The name of column image
        'image_column'  => 'image',

        // The url full path to append
        'append_url'    => env('APP_URL').'/'.env('APP_NAME').'/public',

        // Set default image if user not have image
        'default_image' => 'http://cdn.onlinewebfonts.com/svg/img_568657.png',
        
        'pusher_log' => true,
    ];