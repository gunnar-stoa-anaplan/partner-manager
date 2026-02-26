<?php

namespace App\Enums;

enum UserSourceMode: string
{
    case Local = 'local';
    case Directory = 'directory';
    case Hybrid = 'hybrid';
}
