<?php

namespace App\Models;

use App\Enums\FriendshipStatusEnum;
use Illuminate\Database\Eloquent\Concerns\HasRelationships;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Friendship extends Model
{
    use HasFactory,HasRelationships;

    protected $fillable = [
      'by_user',
      'to_user'
    ];
    protected $casts = [
      'status' => FriendshipStatusEnum::class,
        'created_on' => 'datetime'
    ];
    public function byUser(){
        return $this->hasOne(User::class);
    }
}
