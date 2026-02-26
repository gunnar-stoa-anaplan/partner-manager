<?php

namespace App\Enums;

enum ZoomMeetingStatus: string
{
    case Pending = 'pending';
    case Scheduled = 'scheduled';
    case Started = 'started';
    case Ended = 'ended';
    case Cancelled = 'cancelled';
}
