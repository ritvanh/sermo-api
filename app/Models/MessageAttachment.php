<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasRelationships;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageAttachment extends Model
{
    use HasFactory,HasRelationships;
    public $timestamps = false;
    protected $fillable = [
        'message_id',
        'filename',
        'mime_type',
        'file_data'
    ];

    public function message(){
        return $this->belongsTo(Message::class);
    }
}
