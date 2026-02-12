<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QbrSection extends Model
{
    protected $fillable = [
        'qbr_session_id', 'section_type', 'sort_order',
        'auto_generated_data', 'psm_notes', 'partner_feedback', 'rating',
    ];

    protected $casts = [
        'auto_generated_data' => 'array',
    ];

    const SECTION_TYPES = [
        'scorecard_review' => 'Scorecard Review',
        'pipeline_review' => 'Pipeline Review',
        'delivery_review' => 'Delivery Review',
        'enablement_review' => 'Enablement Review',
        'goals_review' => 'Goals Review',
        'path_review' => 'Path Review',
        'task_review' => 'Task Review',
        'action_review' => 'Action Review',
        'open_discussion' => 'Open Discussion',
    ];

    public function session()
    {
        return $this->belongsTo(QbrSession::class, 'qbr_session_id');
    }
}
