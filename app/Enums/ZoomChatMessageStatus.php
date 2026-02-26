<?php

namespace App\Enums;

enum ZoomChatMessageStatus: string
{
    case Received = 'received';
    case Processing = 'processing';
    case Responded = 'responded';
    case Failed = 'failed';
    case Skipped = 'skipped';
}
