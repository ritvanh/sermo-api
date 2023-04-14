<?php
namespace App\Enums;

enum FriendshipStatusEnum:string {
    case Pending = 'pending';
    case Active = 'active';
    case Blocked = 'blocked';
}
