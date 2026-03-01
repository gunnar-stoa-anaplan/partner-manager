<?php

namespace App\Enums;

enum TrainingMode: string
{
    case Required = 'required';
    case Recommended = 'recommended';
    case None = 'none';
}
