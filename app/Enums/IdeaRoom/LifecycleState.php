<?php

namespace App\Enums\IdeaRoom;

enum LifecycleState: string
{
    case Spark = 'spark';
    case Growing = 'growing';
    case Developed = 'developed';
    case Archived = 'archived';
    case Dropped = 'dropped';
}
