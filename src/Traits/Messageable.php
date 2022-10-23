<?php

namespace Messenger\Chat\Traits;

use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Messenger\Chat\Models\Conversation;
use Messenger\Chat\Models\Message;
use Messenger\Chat\Models\MessageUser;

trait Messageable
{    
    public function getAvatarAttribute()
    {
        $column = config('messenger.image_column');
        $path = trim(config('messenger.img_url'), '/');
        return $this->$column
                ? "$path/{$this->$column}"
                : config('messenger.default_image');
    }

    public function getLastSeenAttribute($value)
    {
        return Carbon::parse($value)->diffForHumans();
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

    public function makeOflline()
    {
        DB::statement("UPDATE `{$this->getTable()}` SET `last_seen` = '".now()."' WHERE `id` = $this->id");
        Cache::forget('user-is-online-' . $this->id);
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