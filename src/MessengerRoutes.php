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
        Route::middleware(['auth', \Messenger\Chat\Middleware\UserLastSeen::class])->prefix('messenger')->group(function() {

            Route::get('/', [ConversationController::class, 'index'])->name('messenger');
            Route::get('users', [ConversationController::class, 'users'])->name('users');

            Route::get('update/last-seen', [ConversationController::class, 'updateLastSeen'])->name('conversations.updateLastSeen');
            Route::get('user/{user}/details', [ConversationController::class, 'userDetails'])->name('user.details');
            Route::delete('conversation/{conversation}/destroy', [ConversationController::class, 'destroy'])->name('conversation.destroy');
            Route::get('single/conversation/{id}', [ConversationController::class, 'singleConversation'])->name('conversation.single.conversation');

            Route::get('conversation/{user}/messages', [MessageController::class, 'index'])->name('conversation.user.messages');
            Route::get('conversation/{conversation}/messages/load-more', [MessageController::class, 'getMessages'])->name('conversation.load.messages');

            Route::post('messages', [MessageController::class, 'store'])->name('message.store');
            Route::get('update/read-at', [MessageController::class, 'updateReadAt'])->name('messages.updateReadAt');
            Route::post('message/{message_id}/delete/{user_id?}', [MessageController::class, 'delete'])->name('messages.delete');

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

