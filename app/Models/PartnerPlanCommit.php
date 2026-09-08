<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerPlanCommit extends Model
{
    protected $fillable = ['partner_id', 'role', 'committed'];

    protected $casts = ['committed' => 'integer'];

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }
}
