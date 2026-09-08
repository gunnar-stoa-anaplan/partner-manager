<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResourcePathEnrollment extends Model
{
    use HasFactory;
    use \App\Models\Concerns\ScopedToActor;

    protected $fillable = [
        'contact_id', 'partner_id', 'resource_path_id', 'current_stage_id',
        'enrolled_at', 'stage_entered_at', 'last_reviewed_at',
        'reviewed_by', 'status', 'notes',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'stage_entered_at' => 'datetime',
        'last_reviewed_at' => 'datetime',
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function path()
    {
        return $this->belongsTo(ResourcePath::class, 'resource_path_id');
    }

    public function currentStage()
    {
        return $this->belongsTo(ResourcePathStage::class, 'current_stage_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function tasks()
    {
        return $this->hasMany(ResourceTask::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
