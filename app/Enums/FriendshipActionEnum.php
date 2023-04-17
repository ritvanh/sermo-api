<?php
namespace App\Enums;

enum FriendshipActionEnum:string{
    case Send = 'send';
    case Cancel = 'cancel';
    case Unfriend = 'unfriend';
    case Block = 'block';
    case Unblock = 'unblock';
    case Accept = 'accept';
    case Refuse = 'refuse';
}
