<?php

namespace App\Models;

use App\Enums\MessageStatusEnum;
use Illuminate\Database\Eloquent\Concerns\HasRelationships;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory, HasRelationships;
    public $timestamps = false;
    protected $fillable = [
      'sender_id',
        'receiver_id',
        'reply_to_id',
        'message_content',
        'status',
        'sent_on',
        'seen_on'
    ];
    protected $casts = [
      'status' => MessageStatusEnum::class,
      'sent_on' => 'datetime',
        'seen_on' => 'datetime'
    ];

    public  function reply(){
        return $this->belongsTo(Message::class);
    }
    public function sender(){
        return $this->hasOne(User::class,'sender_id');
    }
    public function receiver(){
        return $this->hasOne(User::class,'receiver_id');
    }

    public function attachments(){
        return $this->hasMany(MessageAttachment::class);
    }
}
