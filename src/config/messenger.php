<?php

return [
    // Model class  \App\Models\User::class
    'model'         => config('auth.providers.users.model'),

    // the image column name in the database of the model table
    'image_column'  => 'image',

    // put the full link to access about user image
    // when use the user image and the path is  uploads/users/avatar.png
    // then set this key full path excloude image path  =>  http://localhost:8000/
    //-----------
    // if image return only name like   =>  avatar.png
    // then this key will be   [asset helper function]  =>  http://localhost:8000/uploads/users/
    'img_url'    => env('APP_URL').'/public',

    // Set default image if user not have image  [full link]
    'default_image' => 'http://cdn.onlinewebfonts.com/svg/img_568657.png',

    // To enable or disable the pusher logs in browser console
    'pusher_log' => true,
];