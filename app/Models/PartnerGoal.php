<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerGoal extends Model
{
    protected $fillable = [
        'partner_id',
        'fiscal_year',
        'sourced_nnacv_target',
        'influenced_nnacv_target',
        'notes',
        'updated_by',
    ];

    protected $casts = [
        'sourced_nnacv_target' => 'decimal:2',
        'influenced_nnacv_target' => 'decimal:2',
        'fiscal_year' => 'integer',
    ];

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function updatedByUser()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
