<?php
namespace App\Enums;

enum FriendshipStatusEnum:string {
    case None = 'none';
    case Pending = 'pending';
    case Active = 'active';
    case Blocked = 'blocked';
}
