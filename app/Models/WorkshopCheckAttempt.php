<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkshopCheckAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_id',
        'workshop_session_id',
        'workshop_page_id',
        'check_id',
        'passed',
        'attempted_at',
    ];

    protected $casts = [
        'passed' => 'boolean',
        'attempted_at' => 'datetime',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(WorkshopSession::class, 'workshop_session_id');
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(WorkshopPage::class, 'workshop_page_id');
    }
}
