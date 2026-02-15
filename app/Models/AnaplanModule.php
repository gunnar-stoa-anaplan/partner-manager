<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnaplanModule extends Model
{
    use HasFactory;

    protected $fillable = [
        'anaplan_id',
        'name',
        'anaplan_model_id',
    ];

    public function model(): BelongsTo
    {
        return $this->belongsTo(AnaplanModel::class, 'anaplan_model_id');
    }
}
