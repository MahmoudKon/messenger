<?php

namespace Messenger\Chat\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Conversation extends Model
{
    protected $fillable = ['label', 'type', 'image', 'user_id', 'last_message_id'];

    public function user()
    {
        return $this->belongsTo(User::class)->select('id', 'name', 'email', 'image')->withDefault(['name' => '', 'email' => '', 'image' => '']);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'conversation_user')->withPivot(['joined_at', 'role']);
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
                    $query->where('user_id', auth()->id());
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
