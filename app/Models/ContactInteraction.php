<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactInteraction extends Model
{
    protected $fillable = [
        'contact_id',
        'partner_id',
        'logged_by',
        'interaction_type',
        'title',
        'description',
        'interaction_date',
        'location',
        'outcome',
        'next_steps',
        'partner_event_id',
    ];

    protected $casts = [
        'interaction_date' => 'date',
    ];

    const TYPES = [
        'lunch_and_learn' => 'Lunch & Learn',
        'webcast' => 'Webcast',
        'product_update' => 'Product Update',
        'dinner' => 'Dinner',
        'coffee_chat' => 'Coffee Chat',
        'conference' => 'Conference',
        'phone_call' => 'Phone Call',
        'email' => 'Email',
        'other' => 'Other',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function loggedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'logged_by');
    }

    public function partnerEvent(): BelongsTo
    {
        return $this->belongsTo(PartnerEvent::class);
    }
}
