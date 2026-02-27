<?php

namespace App\Enums\IdeaRoom;

enum EventType: string
{
    case Created = 'created';
    case NoteAdded = 'note_added';
    case BucketProposed = 'bucket_proposed';
    case StateChanged = 'state_changed';
    case TagAdded = 'tag_added';
    case Updated = 'updated';
}
