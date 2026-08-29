<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkshopSurveyResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'workshop_survey_template_id',
        'workshop_session_id',
        'contact_id',
        'responses_json',
        'score',
        'passed',
        'attempt_number',
        'submitted_at',
    ];

    protected $casts = [
        'responses_json' => 'array',
        'passed' => 'boolean',
        'submitted_at' => 'datetime',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(WorkshopSurveyTemplate::class, 'workshop_survey_template_id');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(WorkshopSession::class, 'workshop_session_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }
}
