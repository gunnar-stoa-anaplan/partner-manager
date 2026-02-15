<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LaunchProgramMilestone extends Model
{
    use HasFactory;

    protected $fillable = [
        'launch_program_id',
        'title',
        'description',
        'sort_order',
        'due_date',
        'is_required',
        'type',
    ];

    const TYPES = [
        'task' => 'Task',
        'document' => 'Document',
        'enablement' => 'Enablement',
        'feedback' => 'Feedback',
    ];

    const TYPE_COLORS = [
        'task' => '#007bff',
        'document' => '#fd7e14',
        'enablement' => '#7B1FA2',
        'feedback' => '#28a745',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'is_required' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(LaunchProgram::class, 'launch_program_id');
    }

    public function completions(): HasMany
    {
        return $this->hasMany(LaunchProgramMilestoneCompletion::class);
    }
}
