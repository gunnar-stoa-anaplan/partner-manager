<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SignalEngineRun extends Model
{
    protected $fillable = [
        'processor',
        'signals_created',
        'signals_expired',
        'signals_correlated',
        'partners_processed',
        'duration_ms',
        'status',
        'error',
    ];
}
