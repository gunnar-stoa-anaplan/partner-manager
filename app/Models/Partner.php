<?php

namespace App\Models;

use App\Models\Concerns\ScopedToActor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Partner extends Model
{
    use HasFactory, ScopedToActor;

    /** Partner's own id IS the partner id for visibility scoping. */
    public function visibilityPartnerColumn(): string
    {
        return 'id';
    }


    protected $fillable = [
        'salesforce_id',
        'name',
        'website',
        'industry',
        'user_id',
        'psm',
        'geography',
        'region',
        'logo_path',
        'phone',
        'sales_territory',
        'mailing_country',
        'mailing_state',
        'mailing_street',
        'mailing_suite',
        'mailing_city',
        'mailing_postal_code',
        'partner_locator_emails',
        'headline',
        'company_description',
        'marketplace_status',
        'marketplace_reviewed_by',
        'marketplace_reviewed_at',
        'marketplace_rejection_reason',
    ];

    public const MARKETPLACE_STATUSES = [
        'draft' => 'Draft',
        'submitted' => 'Submitted',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
    ];

    public const MARKETPLACE_STATUS_COLORS = [
        'draft' => 'secondary',
        'submitted' => 'warning',
        'approved' => 'success',
        'rejected' => 'danger',
    ];

    /**
     * Eager load relationships commonly used
     */
    protected $with = ['projects', 'contacts'];

    protected function casts(): array
    {
        return [
            'marketplace_reviewed_at' => 'datetime',
        ];
    }

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

    public function portalContacts()
    {
        return $this->hasMany(Contact::class)->where('portal_access', true);
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

    public function scorecards()
    {
        return $this->hasMany(PartnerScorecard::class);
    }

    public function healthSnapshots()
    {
        return $this->hasMany(PartnerHealthSnapshot::class);
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

    public function certifications()
    {
        return $this->hasMany(Certification::class);
    }

    public function dealRegistrations()
    {
        return $this->hasMany(DealRegistration::class);
    }

    public function launchProgramPartners()
    {
        return $this->hasMany(LaunchProgramPartner::class);
    }

    public function launchPrograms()
    {
        return $this->belongsToMany(LaunchProgram::class, 'launch_program_partners')
            ->withPivot('status', 'nominated_by', 'accepted_at', 'notes')
            ->withTimestamps();
    }

    public function anaplanWorkspaces()
    {
        return $this->hasMany(AnaplanWorkspace::class);
    }

    public function anaplanModels()
    {
        return $this->hasManyThrough(AnaplanModel::class, AnaplanWorkspace::class);
    }

    public function marketplaceEntries()
    {
        return $this->hasMany(PartnerMarketplaceEntry::class);
    }

    public function psmObjectives(): HasMany
    {
        return $this->hasMany(PsmObjective::class);
    }

    public function weeklyFocusItems(): HasMany
    {
        return $this->hasMany(WeeklyFocusItem::class);
    }

    public function marketplaceReviewer()
    {
        return $this->belongsTo(User::class, 'marketplace_reviewed_by');
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path
            ? Storage::disk('public')->url($this->logo_path)
            : null;
    }

    /**
     * Get historical resource certification counts over time
     *
     * @param  string|null  $program  Filter by specific program (IFP, I&D, SPM, OWP)
     * @param  string|null  $startDate  Start date for time range
     * @param  string|null  $endDate  End date for time range
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
     * @param  string|null  $startDate  Start date for time range
     * @param  string|null  $endDate  End date for time range
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
            $partner->anaplanWorkspaces()->delete();
            $partner->marketplaceEntries()->delete();
        });
    }
}
