<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaunchProgramDateChange extends Model
{
    use HasFactory;

    protected $fillable = [
        'launch_program_id',
        'field',
        'old_date',
        'new_date',
        'reason',
        'changed_by',
    ];

    const FIELD_LABELS = [
        'starts_at' => 'Start Date',
        'target_ga_date' => 'Target GA Date',
        'ends_at' => 'End Date',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'old_date' => 'date',
            'new_date' => 'date',
        ];
    }

    public function launchProgram(): BelongsTo
    {
        return $this->belongsTo(LaunchProgram::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
