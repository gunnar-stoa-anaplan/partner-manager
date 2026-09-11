<?php

namespace App\Models;

use App\Models\Concerns\ScopedToOwner;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JourneyAssignment extends Model
{
    use HasFactory, ScopedToOwner;

    protected $fillable = [
        'journey_id',
        'user_id',
    ];

    /**
     * Belongs-to relationship with Journey.
     */
    public function journey(): BelongsTo
    {
        return $this->belongsTo(Journey::class);
    }

    /**
     * Belongs-to relationship with User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
