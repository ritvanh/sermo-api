<?php
namespace App\Enums;

enum MessageStatusEnum:string {
    case Sent = 'sent';
    case Received = 'received';
    case Seen = 'seen';
}
