<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabCheckpoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'lab_id',
        'title',
        'description',
        'sort_order',
        'checkpoint_type',
        'validation_config',
        'is_required',
        'points',
        'hint',
    ];

    const CHECKPOINT_TYPES = [
        'formula' => 'Formula Validation',
        'structure' => 'Structure Validation',
        'config' => 'Configuration Validation',
        'value' => 'Value Validation',
        'list' => 'List Validation',
    ];

    const TYPE_COLORS = [
        'formula' => '#7B1FA2',
        'structure' => '#2196F3',
        'config' => '#fd7e14',
        'value' => '#28a745',
        'list' => '#00BCD4',
    ];

    const TYPE_ICONS = [
        'formula' => 'function',
        'structure' => 'sitemap',
        'config' => 'cog',
        'value' => 'calculator',
        'list' => 'list',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'validation_config' => 'array',
            'is_required' => 'boolean',
            'points' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function lab(): BelongsTo
    {
        return $this->belongsTo(Lab::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(LabCheckpointResult::class);
    }

    public function passRate(): float
    {
        $latestResults = $this->results()
            ->whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')
                    ->from('lab_checkpoint_results')
                    ->where('lab_checkpoint_id', $this->id)
                    ->groupBy('lab_participant_id');
            })
            ->get();

        if ($latestResults->isEmpty()) {
            return 0;
        }

        return round(($latestResults->where('passed', true)->count() / $latestResults->count()) * 100, 1);
    }

    public function averageAttempts(): float
    {
        $avgAttempts = $this->results()
            ->where('passed', true)
            ->avg('attempt_number');

        return round($avgAttempts ?? 0, 1);
    }
}
