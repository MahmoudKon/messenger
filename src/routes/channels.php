<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('new-message.{id}', function ($user, $id) {
    return (int) $user->id == (int) $id;
});

Broadcast::channel('chat', function ($user) {
    return $user;
});