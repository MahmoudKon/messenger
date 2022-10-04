<?php

namespace Messenger\Chat\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = ['label', 'type', 'image', 'user_id', 'last_message_id'];

    public function user()
    {
        return $this->belongsTo(config('messenger.model'))->select('id', 'name', 'email', config('messenger.image_column'))->withDefault(['name' => '', 'email' => '', config('messenger.image_column') => null]);
    }

    public function users()
    {
        return $this->belongsToMany(config('messenger.model'), 'conversation_user')->withPivot(['joined_at', 'role']);
    }

    public function messages()
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'DESC');
    }

    public function lastMessage()
    {
        return $this->belongsTo(Message::class, 'last_message_id')->with('user')->withDefault();
    }

    public function scopeOnlyWithAuth($query)
    {
        return $query->whereHas('users', function($query) {
                    $query->where('user_id', auth()->id())->whereNull('deleted_at');
                })->withCount([
                        'messages as unread' => function($query) {
                            $query->whereHas('users', function($query) {
                                $query->whereNull('read_at')->where('user_id', auth()->id());
                            });
                        },
                        'messages as user_unread' => function($query) {
                            $query->whereHas('users', function($query) {
                                $query->whereNull('read_at')->where('user_id', '<>', auth()->id());
                            });
                        }
                    ]);
    }
}
