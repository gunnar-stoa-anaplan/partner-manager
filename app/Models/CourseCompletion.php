<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A partner resource's completion of one training course, imported from the Docebo export in Anaplan.
 */
class CourseCompletion extends Model
{
    use \App\Models\Concerns\ScopedToActor;

    /** @use HasFactory<\Database\Factories\CourseCompletionFactory> */
    use HasFactory;

    protected $fillable = [
        'partner_id',
        'resource_code',
        'person_name',
        'course_name',
        'completed_on',
        'partner_hq',
        'partner_region',
        'import_id',
    ];

    protected $casts = [
        'completed_on' => 'date',
    ];

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function import(): BelongsTo
    {
        return $this->belongsTo(Import::class);
    }
}
