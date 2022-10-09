<?php

use Illuminate\Support\Facades\Route;
use Messenger\Chat\Controllers\ConversationController;
use Messenger\Chat\Controllers\MessageController;

Route::middleware(config('messenger.middleware'))->prefix(config('messenger.route_prefix'))->group(function() {
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