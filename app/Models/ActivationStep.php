<?php

namespace App\Models;

use App\Enums\StepType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ActivationStep extends Model
{
    /** @use HasFactory<\Database\Factories\ActivationStepFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'activation_path_id',
        'order',
        'name',
        'description',
        'step_type',
        'criteria',
        'is_required',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'step_type' => StepType::class,
            'criteria' => 'array',
            'is_required' => 'boolean',
        ];
    }

    public function activationPath(): BelongsTo
    {
        return $this->belongsTo(ActivationPath::class);
    }

    public function progress(): HasOne
    {
        return $this->hasOne(StepProgress::class, 'activation_step_id');
    }
}
