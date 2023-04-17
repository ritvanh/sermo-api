<?php
namespace App\Enums;

enum MessageStatusEnum:string {
    case Sent = 'sent';
    case Seen = 'seen';
}
