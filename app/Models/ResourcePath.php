<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResourcePath extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'is_default', 'is_active', 'created_by',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function stages()
    {
        return $this->hasMany(ResourcePathStage::class)->orderBy('sort_order');
    }

    public function gates()
    {
        return $this->hasMany(ResourcePathGate::class);
    }

    public function enrollments()
    {
        return $this->hasMany(ResourcePathEnrollment::class);
    }

    public function taskTemplates()
    {
        return $this->hasMany(ResourceTaskTemplate::class);
    }

    public function stageHistory()
    {
        return $this->hasMany(ResourceStageHistory::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}
