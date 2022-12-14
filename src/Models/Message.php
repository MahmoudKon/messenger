<?php

namespace Messenger\Chat\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['message', 'type', 'conversation_id', 'user_id'];

    protected $with = ['user', 'users'];

    protected function createdAt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Carbon::parse($value)->diffForHumans(),
        );
    }

    protected function message(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->type == 'text' ? $value : asset("uploads/messages/$value"),
        );
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
        return $this->belongsToMany(config('messenger.model'), 'message_user')->select('id')->withPivot(['read_at', 'deleted_at']);
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
