<?php

namespace Messenger\Chat\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;
use Messenger\Chat\Models\Conversation;
use Messenger\Chat\Models\Message;
use Messenger\Chat\Models\MessageUser;

trait Messageable
{
    protected function avatar(): Attribute
    {
        $column = config('messenger.image_column');
        return Attribute::make(
            get: fn ($value) => $this->$column
                                        ? trim(config('messenger.img_url'), '/').'/'.$this->$column
                                        : config('messenger.default_image'),
        );
    }

    protected function lastSeen(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Carbon::parse($value)->diffForHumans(),
        );
    }

    public function unreadMessages()
    {
        return MessageUser::whereNull('read_at')->where('user_id', auth()->id())->count();
    }
    public function scopeExceptAuth($query)
    {
        return $query->where('id', '!=', auth()->id());
    }

    public function messages()
    {
        return $this->belongsToMany(Message::class, 'message_user')->withPivot(['read_at', 'deleted_at']);
    }

    public function conversations()
    {
        return $this->belongsToMany(Conversation::class, 'conversation_user')->latest('last_message_id')->withPivot(['joined_at', 'role'])->with('lastMessage');
    }

    public function isOnline()
    {
        return Cache::has('user-is-online-' . $this->id);
    }

    public function scopeSearch($query)
    {
        return $query->when(request('search'), function($query) {
                        $query->where('name', 'LIKE', '%'.request('search').'%')->orWhere('email', 'LIKE', '%'.request('search').'%');
                    });
    }

    public function scopeHasConversationWithAuth($query)
    {
        return $query->whereHas('conversations', function($query) {
                            $query->whereHas('users', function($query) {
                                $query->where('user_id', auth()->id())->whereNull('deleted_at');
                            });
                        });
    }
}
