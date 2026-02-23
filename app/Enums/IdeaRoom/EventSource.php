<?php

namespace App\Enums\IdeaRoom;

enum EventSource: string
{
    case User = 'user';
    case Orchestrator = 'orchestrator';
    case RoomAgent = 'room_agent';
}
