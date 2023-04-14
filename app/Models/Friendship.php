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
      'requested_by',
      'requested_to',
      'destroyed_by',
      'requested_on',
      'destroyed_on'
    ];
    protected $casts = [
      'status' => FriendshipStatusEnum::class
    ];
}
