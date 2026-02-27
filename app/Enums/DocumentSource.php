<?php

namespace App\Enums;

enum DocumentSource: string
{
    case Upload = 'upload';
    case Directory = 'directory';
    case StorageDisk = 'storage_disk';
}
