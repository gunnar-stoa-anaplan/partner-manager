<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkshopContentReviewComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'workshop_page_id',
        'workshop_survey_template_id',
        'author_user_id',
        'body',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(WorkshopPage::class, 'workshop_page_id');
    }

    public function surveyTemplate(): BelongsTo
    {
        return $this->belongsTo(WorkshopSurveyTemplate::class, 'workshop_survey_template_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }
}
