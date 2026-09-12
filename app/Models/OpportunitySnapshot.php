<?php

namespace App\Models;

use App\Models\Concerns\ScopedToActor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpportunitySnapshot extends Model
{
    use HasFactory, ScopedToActor;

    protected $fillable = [
        'opportunity_id',
        'import_id',
        'snapshot_date',
        'opportunity_name',
        'account_name',
        'partner_id',
        'stage',
        'amount',
        'close_date',
        'fiscal_quarter',
        'fiscal_year',
        'type',
        'lead_source',
        'revenue_type',
        'product_area',
        'probability',
        'owner',
    ];

    protected $casts = [
        'snapshot_date' => 'date',
        'close_date' => 'date',
        'amount' => 'decimal:2',
        'probability' => 'decimal:2',
    ];

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }

    public function import(): BelongsTo
    {
        return $this->belongsTo(Import::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }
}
