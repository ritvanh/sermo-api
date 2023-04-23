<?php

namespace App\Models;

use App\Enums\FriendshipStatusEnum;
use Illuminate\Database\Eloquent\Concerns\HasRelationships;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Friendship extends Model
{
    use HasFactory,HasRelationships;
    public $timestamps = false;
    protected $fillable = [
      'by_user',
      'to_user',
        'created_on',
        'status'
    ];
    protected $casts = [
      'status' => FriendshipStatusEnum::class,
        'created_on' => 'datetime'
    ];
    public function byUser(){
        return $this->hasOne(User::class,'by_user');
    }
    public function toUser(){
        return $this->hasOne(User::class,'to_user');
    }
}
