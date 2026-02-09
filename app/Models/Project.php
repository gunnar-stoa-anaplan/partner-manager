<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'partner_id',
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
        // Legacy fields (if needed)
        'client',
        'initiative',
        'completion',
        'team',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'acv' => 'decimal:2',
        'percent_complete' => 'integer',
        'total_sprints' => 'integer',
        'current_sprint' => 'integer',
    ];

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