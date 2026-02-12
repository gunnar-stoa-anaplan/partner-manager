<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    use HasFactory;

    protected $fillable = [
        'salesforce_id',
        'name',
        'website',
        'industry',
        'user_id',
        'psm',
        'geography',
        'region',
    ];

    /**
     * Eager load relationships commonly used
     */
    protected $with = ['projects', 'contacts'];

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function accounts()
    {
        return $this->belongsToMany(Account::class, 'account_partner');
    }
    
    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    public function fundings()
    {
        return $this->hasMany(Funding::class);
    }

    public function opportunities()
    {
        return $this->hasMany(Opportunity::class);
    }

    public function resources()
    {
        return $this->hasMany(Resource::class);
    }

    public function events()
    {
        return $this->hasMany(PartnerEvent::class);
    }

    public function enablementRegistrations()
    {
        return $this->hasMany(EnablementRegistration::class);
    }

    public function blueprintAssignments()
    {
        return $this->hasMany(PartnerBlueprintAssignment::class);
    }

    public function enablementSessions()
    {
        return $this->belongsToMany(EnablementSession::class, 'partner_enablement_session')
            ->withPivot('added_by', 'notes')
            ->withTimestamps();
    }

    /**
     * Many-to-many relationship with User through user_partner pivot (assigned users).
     */
    public function assignedUsers()
    {
        return $this->belongsToMany(User::class, 'user_partner')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get PSMs assigned to this partner.
     */
    public function psms()
    {
        return $this->belongsToMany(User::class, 'user_partner')
            ->wherePivot('role', 'psm')
            ->withTimestamps();
    }

    /**
     * Get resource snapshots for this partner
     */
    public function resourceSnapshots()
    {
        return $this->hasMany(ResourceSnapshot::class);
    }

    /**
     * Get opportunity snapshots for this partner
     */
    public function opportunitySnapshots()
    {
        return $this->hasMany(OpportunitySnapshot::class);
    }

    public function goals()
    {
        return $this->hasMany(PartnerGoal::class);
    }

    public function influencers()
    {
        return $this->hasMany(Contact::class)->where('contact_type', 'influencer');
    }

    public function resourceContacts()
    {
        return $this->hasMany(Contact::class)->where('contact_type', 'resource');
    }

    public function contactInteractions()
    {
        return $this->hasMany(ContactInteraction::class);
    }

    public function stageHistory()
    {
        return $this->hasMany(PartnerStageHistory::class);
    }

    /**
     * Get historical resource certification counts over time
     *
     * @param string|null $program Filter by specific program (IFP, I&D, SPM, OWP)
     * @param string|null $startDate Start date for time range
     * @param string|null $endDate End date for time range
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getResourceHistory($program = null, $startDate = null, $endDate = null)
    {
        $query = $this->resourceSnapshots()
            ->orderBy('snapshot_date', 'asc');

        if ($program) {
            $query->where('program', $program);
        }

        if ($startDate) {
            $query->where('snapshot_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('snapshot_date', '<=', $endDate);
        }

        return $query->get();
    }

    /**
     * Get historical opportunity counts and amounts over time
     *
     * @param string|null $startDate Start date for time range
     * @param string|null $endDate End date for time range
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getOpportunityHistory($startDate = null, $endDate = null)
    {
        $query = $this->opportunitySnapshots()
            ->orderBy('snapshot_date', 'asc');

        if ($startDate) {
            $query->where('snapshot_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('snapshot_date', '<=', $endDate);
        }

        return $query->get();
    }

    /**
     * Cascade deletes on related models
     */
    protected static function booted()
    {
        static::deleting(function ($partner) {
            $partner->events()->delete();
            $partner->contacts()->delete();
            $partner->leads()->delete();
            $partner->fundings()->delete();
            $partner->opportunities()->delete();
            $partner->resources()->delete();
            $partner->goals()->delete();
        });
    }
}