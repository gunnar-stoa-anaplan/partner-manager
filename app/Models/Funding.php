<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Funding extends Model
{
    use HasFactory;

    protected $fillable = [
        'partner_id',
        'name',
        'amount',
        'status',
        'type',
        'description',
        'date',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * Get the partner that owns the funding.
     */
    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }
}
