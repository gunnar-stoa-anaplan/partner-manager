<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnaplanList extends Model
{
    use HasFactory;

    protected $fillable = [
        'anaplan_id',
        'name',
        'anaplan_model_id',
        'item_count',
        'has_selective_access',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'item_count' => 'integer',
            'has_selective_access' => 'boolean',
        ];
    }

    public function model(): BelongsTo
    {
        return $this->belongsTo(AnaplanModel::class, 'anaplan_model_id');
    }
}
