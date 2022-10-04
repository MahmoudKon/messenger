<?php

namespace Messenger\Chat\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConversationUser extends Model
{
    use SoftDeletes;

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
