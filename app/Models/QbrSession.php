<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QbrSession extends Model
{
    use \App\Models\Concerns\ScopedToActor;

    protected $fillable = [
        'partner_id', 'fiscal_quarter', 'status', 'scheduled_date',
        'completed_date', 'partner_event_id', 'prepared_by',
        'attendees', 'overall_rating', 'executive_summary',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'completed_date' => 'date',
        'attendees' => 'array',
    ];

    const STATUSES = [
        'planning' => 'Planning',
        'scheduled' => 'Scheduled',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    const RATINGS = [
        'strong' => 'Strong',
        'satisfactory' => 'Satisfactory',
        'needs_improvement' => 'Needs Improvement',
        'at_risk' => 'At Risk',
    ];

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function partnerEvent()
    {
        return $this->belongsTo(PartnerEvent::class);
    }

    public function preparedByUser()
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function sections()
    {
        return $this->hasMany(QbrSection::class)->orderBy('sort_order');
    }
}
