<?php

namespace App\Models;

use App\Enums\MessageStatusEnum;
use Illuminate\Database\Eloquent\Concerns\HasRelationships;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory, HasRelationships;

    protected $fillable = [
      'conversation_id',
      'sender_id',
        'receiver_id',
        'reply_to_id',
        'message_content'
    ];
    protected $casts = [
      'status' => MessageStatusEnum::class,
      'sent_on' => 'datetime'
    ];

    public function conversation(){
        return $this->belongsTo(Conversation::class);
    }
    public  function reply(){
        return $this->belongsTo(Message::class);
    }

    public function attachments(){
        return $this->hasMany(MessageAttachment::class);
    }
}
