<?php

namespace Messenger\Chat\Models;

use Illuminate\Database\Eloquent\Model;

class ConversationUser extends Model
{
    public $table = 'conversation_user';

    public $timestamps = false;

    protected $casts = ['joined_at' => 'datetime'];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function user()
    {
        return $this->belongsTo(config('messenger.model'));
    }
}
