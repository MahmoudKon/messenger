<?php

namespace Messenger\Chat\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class Message extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['message', 'type', 'conversation_id', 'user_id'];

    protected $with = ['user'];

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->diffForHumans();
    }

    public function getMessageAttribute($value)
    {
        return $this->type == 'text' ? $value : asset("uploads/messages/$value");
    }

    public function user()
    {
        return $this->belongsTo(User::class)->select('id', 'name', 'email', 'image')->withDefault(['name' => 'User', 'email' => 'User', 'image' => '']);
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class)->withDefault(['label' => '']);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'message_user')->withPivot(['read_at', 'deleted_at']);
    }

    public function scopeUnreadMessages($query)
    {
        return $query->whereHas('users', function($query) {
            $query->whereNull('read_at')->where('user_id', auth()->id());
        });
    }

    public function scopereadMessages($query)
    {
        return $query->whereHas('users', function($query) {
            $query->whereNotNull('read_at')->where('user_id', auth()->id());
        });
    }

    protected static function boot()
    {
        parent::boot();

        static::deleted(function($model) {
            $model->conversation->update(['last_message_id' => $model->conversation->messages()->latest()->id]);
        });
    }
}
