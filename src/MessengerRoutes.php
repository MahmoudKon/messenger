<?php

namespace Messenger\Chat;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;
use Messenger\Chat\Controllers\ConversationController;
use Messenger\Chat\Controllers\MessageController;

class MessengerRoutes
{
    public static function routes()
    {
        Route::middleware(config('messenger.middleware'))->prefix(config('messenger.route_prefix'))->group(function() {
            Route::controller(ConversationController::class)->group(function () {
                Route::get('/', 'index')->name('messenger');
                Route::get('users', 'users')->name('users');
                Route::get('update/last-seen', 'updateLastSeen')->name('conversations.updateLastSeen');
                Route::get('user/{user}/details', 'userDetails')->name('user.details');
                Route::delete('conversation/{conversation}/destroy', 'destroy')->name('conversation.destroy');
                Route::get('single/conversation/{id}', 'singleConversation')->name('conversation.single.conversation');
            });
        
            Route::controller(MessageController::class)->group(function () {
                Route::get('conversation/{user}/messages', 'index')->name('conversation.user.messages');
                Route::get('conversation/{conversation}/messages/load-more', 'getMessages')->name('conversation.load.messages');
                Route::post('messages', 'store')->name('message.store');
                Route::get('update/read-at', 'updateReadAt')->name('messages.updateReadAt');
                Route::post('message/{message_id}/delete/{user_id?}', 'delete')->name('messages.delete');
            });
        });
    }

    public static function channels()
    {
        Broadcast::channel('new-message.{id}', function ($user, $id) {
            return (int) $user->id == (int) $id;
        });

        Broadcast::channel('chat', function ($user) {
            return $user;
        });
    }
}

