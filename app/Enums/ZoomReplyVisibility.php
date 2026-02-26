<?php

namespace App\Enums;

enum ZoomReplyVisibility: string
{
    case Everyone = 'everyone';
    case SenderOnly = 'sender_only';
}
