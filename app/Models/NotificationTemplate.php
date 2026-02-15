<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    protected $fillable = [
        'event_type', 'channel', 'subject', 'body',
        'is_active', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    const EVENT_TYPES = [
        'blueprint_assigned' => 'Blueprint Assigned',
        'resource_enrolled_blueprint' => 'Resource Enrolled in Blueprint',
        'blueprint_completed' => 'Blueprint Completed',
        'blueprint_overdue' => 'Blueprint Overdue',
        'event_registration' => 'Event Registration',
        'event_reminder' => 'Event Reminder',
        'event_completed' => 'Event Completed',
        'certification_earned' => 'Certification Earned',
        'certification_expiring' => 'Certification Expiring',
        'task_assigned' => 'Task Assigned',
        'task_overdue' => 'Task Overdue',
        'path_stage_advanced' => 'Path Stage Advanced',
        'qbr_scheduled' => 'QBR Scheduled',
        'alert_generated' => 'Alert Generated',
        'anaplan_version_outdated' => 'Anaplan Version Outdated',
    ];

    const CHANNELS = [
        'email' => 'Email',
        'in_app' => 'In-App',
        'both' => 'Both',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function logs()
    {
        return $this->hasMany(NotificationLog::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function render(array $variables): array
    {
        $subject = $this->subject;
        $body = $this->body;

        foreach ($variables as $key => $value) {
            $subject = str_replace("{{{$key}}}", $value, $subject);
            $body = str_replace("{{{$key}}}", $value, $body);
        }

        return ['subject' => $subject, 'body' => $body];
    }
}
