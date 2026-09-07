<?php

namespace App\Models;

use App\Models\Concerns\ScopedToActor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory, ScopedToActor;

    protected $fillable = [
        'account_id',
        'partner_id',
        'opportunity_id',
        'name',
        'start_date',
        'end_date',
        'cal_id',
        'tal_id',
        'ae_id',
        'acv',
        'status',
        'percent_complete',
        'total_sprints',
        'current_sprint',
        'delivery_team',
        'budget',
        'phase',
        'customer_satisfaction',
        'is_referenceable',
        'led_to_expansion',
        'scope_changes',
        'go_live_date',
        'outcome_notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'go_live_date' => 'date',
            'acv' => 'decimal:2',
            'percent_complete' => 'integer',
            'total_sprints' => 'integer',
            'current_sprint' => 'integer',
            'customer_satisfaction' => 'integer',
            'is_referenceable' => 'boolean',
            'led_to_expansion' => 'boolean',
            'scope_changes' => 'integer',
        ];
    }

    /**
     * Get the product area from the linked opportunity.
     */
    public function getProductAreaAttribute(): ?string
    {
        return $this->opportunity?->product_area;
    }

    /**
     * The opportunity this project was created from.
     */
    public function opportunity()
    {
        return $this->belongsTo(Opportunity::class);
    }

    /**
     * The partner that owns this project.
     */
    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    /**
     * The account this project is associated with.
     */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * The contacts involved in this project.
     */
    public function contacts()
    {
        return $this->belongsToMany(Contact::class, 'contact_project');
    }

    /**
     * Get the Client Account Lead (CAL) contact.
     */
    public function cal()
    {
        return $this->belongsTo(Contact::class, 'cal_id');
    }

    /**
     * Get the Technical Account Lead (TAL) contact.
     */
    public function tal()
    {
        return $this->belongsTo(Contact::class, 'tal_id');
    }

    /**
     * Get the Account Executive (AE) contact.
     */
    public function ae()
    {
        return $this->belongsTo(Contact::class, 'ae_id');
    }
}
