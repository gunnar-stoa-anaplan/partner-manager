<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'partner_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'title',
        'avatar',
    ];

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'contact_project');
    }

    public function events()
    {
        return $this->belongsToMany(PartnerEvent::class, 'contact_partner_event')
            ->withTimestamps();
    }

    public function enablementRegistrations()
    {
        return $this->hasMany(EnablementRegistration::class);
    }

    public function blueprintEnrollments()
    {
        return $this->hasMany(BlueprintEnrollment::class);
    }
}