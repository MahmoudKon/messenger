<?php

namespace Messenger\Chat\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use SoftDeletes;

    protected $fillable = ['message', 'type', 'conversation_id', 'user_id'];

    protected $with = ['user', 'users'];

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->diffForHumans();
    }

    public function getMessageAttribute($value)
    {
        return $this->type == 'text' ? $value : asset(config('messenger.upload_path').$value);
    }

    public function user()
    {
        return $this->belongsTo(config('messenger.model'))->select('id', 'name', 'email', config('messenger.image_column'))->withDefault(['name' => 'User', 'email' => 'User', config('messenger.image_column') => null]);
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class)->withDefault(['label' => '']);
    }

    public function users()
    {
        return $this->belongsToMany(config('messenger.model'), 'message_user')->withPivot(['read_at', 'deleted_at']);
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
