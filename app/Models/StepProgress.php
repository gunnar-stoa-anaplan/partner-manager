<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StepProgress extends Model
{
    /** @use HasFactory<\Database\Factories\StepProgressFactory> */
    use HasFactory;

    protected $table = 'step_progress';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'activation_step_id',
        'current_value',
        'target_value',
        'completed',
        'completed_at',
        'metadata',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'current_value' => 'decimal:2',
            'target_value' => 'decimal:2',
            'completed' => 'boolean',
            'completed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function activationStep(): BelongsTo
    {
        return $this->belongsTo(ActivationStep::class);
    }
}
