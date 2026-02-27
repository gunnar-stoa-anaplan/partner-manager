<?php

namespace App\Models;

use App\Enums\StepType;
use App\Enums\TrainingPhase;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'content_body',
        'objectives',
        'tour_route',
        'tour_steps',
        'step_type',
        'criteria',
        'is_required',
        'phase',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'step_type' => StepType::class,
            'criteria' => 'array',
            'objectives' => 'array',
            'tour_steps' => 'array',
            'is_required' => 'boolean',
            'phase' => TrainingPhase::class,
        ];
    }

    public function activationPath(): BelongsTo
    {
        return $this->belongsTo(ActivationPath::class);
    }

    public function allProgress(): HasMany
    {
        return $this->hasMany(StepProgress::class, 'activation_step_id');
    }

    public function progressFor(User $user): HasOne
    {
        return $this->hasOne(StepProgress::class, 'activation_step_id')
            ->where('user_id', $user->id);
    }
}
