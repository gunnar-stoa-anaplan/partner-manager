<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Contact extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'partner_id',
        'first_name',
        'last_name',
        'email',
        'password',
        'phone',
        'title',
        'avatar',
        'contact_type',
        'influencer_role',
        'organization',
        'notes',
        'linkedin_url',
        'linkedin_profile_id',
        'linkedin_headline',
        'linkedin_image_url',
        'linkedin_synced_at',
        'resource_role',
        'portal_access',
        'portal_role_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'linkedin_synced_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'portal_access' => 'boolean',
        'password' => 'hashed',
    ];

    const CONTACT_TYPES = [
        'general' => 'General',
        'influencer' => 'Influencer',
        'enablement' => 'Enablement',
        'resource' => 'Resource',
    ];

    const INFLUENCER_ROLES = [
        'client_account_lead' => 'Client Account Lead',
        'technical_account_lead' => 'Technical Account Lead',
        'anaplan_specialist' => 'Anaplan Specialist',
        'strategy_consultant' => 'Strategy Consultant',
        'architect' => 'Architect',
        'other' => 'Other',
    ];

    const RESOURCE_ROLES = [
        'model_builder' => 'Model Builder',
        'solution_architect' => 'Solution Architect',
        'project_manager' => 'Project Manager',
        'consultant' => 'Consultant',
        'developer' => 'Developer',
        'data_integration' => 'Data Integration Specialist',
        'other' => 'Other',
    ];

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    /**
     * The contact's assigned portal role.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'portal_role_id');
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

    public function certifications()
    {
        return $this->hasMany(Certification::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'contact_product')
            ->withPivot('status', 'trained_at')
            ->withTimestamps();
    }

    public function interactions()
    {
        return $this->hasMany(ContactInteraction::class)->orderByDesc('interaction_date');
    }

    public function dealRegistrations()
    {
        return $this->hasMany(DealRegistration::class);
    }

    public function launchProgramParticipants()
    {
        return $this->hasMany(LaunchProgramParticipant::class);
    }

    public function launchProgramFeedback()
    {
        return $this->hasMany(LaunchProgramFeedback::class);
    }

    public function labParticipants()
    {
        return $this->hasMany(LabParticipant::class);
    }

    public function resourcePathEnrollments()
    {
        return $this->hasMany(ResourcePathEnrollment::class);
    }

    public function resourceStageHistory()
    {
        return $this->hasMany(ResourceStageHistory::class);
    }

    public function resourceTasks()
    {
        return $this->hasMany(ResourceTask::class);
    }

    public function scopeInfluencers($query)
    {
        return $query->where('contact_type', 'influencer');
    }

    public function scopeResources($query)
    {
        return $query->where('contact_type', 'resource');
    }

    public function scopePortalUsers($query)
    {
        return $query->where('portal_access', true)->whereNotNull('portal_role_id');
    }

    public function isPortalAdmin(): bool
    {
        return $this->role?->slug === 'partner_admin';
    }

    public function canAccess(string $capability): bool
    {
        return $this->role?->hasPermission($capability) ?? false;
    }

    public function dashboardGroup(): string
    {
        return $this->role?->dashboard_group ?? 'delivery';
    }

    public function hasPortalRole(string|array $roles): bool
    {
        return in_array($this->role?->slug, (array) $roles, true);
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function getLinkedInSearchUrl(): string
    {
        $query = urlencode("{$this->first_name} {$this->last_name}".($this->organization ? " {$this->organization}" : ''));

        return "https://www.linkedin.com/search/results/people/?keywords={$query}";
    }
}
