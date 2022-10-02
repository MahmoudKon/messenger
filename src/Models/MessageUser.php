<?php

namespace Messenger\Chat\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class MessageUser extends Pivot
{
    public $table = 'message_user';

    public $timestamps = false;

    protected $casts = ['joined_at' => 'datetime'];

    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    public function user()
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }
}
