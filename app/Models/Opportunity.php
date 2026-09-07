<?php

namespace App\Models;

use App\Models\Concerns\ScopedToActor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Opportunity extends Model
{
    use HasFactory, ScopedToActor;

    protected $fillable = [
        'salesforce_id',
        'opportunity_name',
        'account_name',
        'partner_id',
        'stage',
        'previous_stage',
        'amount',
        'close_date',
        'previous_close_date',
        'fiscal_quarter',
        'fiscal_year',
        'type',
        'description',
        'lead_source',
        'revenue_type',
        'product_area',
        'product_id',
        'probability',
        'owner',
        'excel_data',
        'import_id',
        'first_seen_at',
    ];

    protected $casts = [
        'excel_data' => 'array',
        'close_date' => 'date',
        'previous_close_date' => 'date',
        'first_seen_at' => 'datetime',
        'amount' => 'decimal:2',
        'probability' => 'decimal:2',
    ];

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function import(): BelongsTo
    {
        return $this->belongsTo(Import::class);
    }

    /**
     * The project created from this opportunity.
     */
    public function project(): HasOne
    {
        return $this->hasOne(Project::class);
    }

    /**
     * Get historical snapshots for this opportunity.
     */
    public function snapshots()
    {
        return $this->hasMany(OpportunitySnapshot::class);
    }

    /**
     * Get historical timeline showing how this opportunity changed over time
     *
     * @param  string|null  $startDate  Start date for time range
     * @param  string|null  $endDate  End date for time range
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getHistory($startDate = null, $endDate = null)
    {
        $query = $this->snapshots()
            ->orderBy('snapshot_date', 'asc');

        if ($startDate) {
            $query->where('snapshot_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('snapshot_date', '<=', $endDate);
        }

        return $query->get();
    }
}
