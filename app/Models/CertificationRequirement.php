<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CertificationRequirement extends Model
{
    protected $fillable = [
        'partner_path_id', 'stage_id', 'certification_type',
        'minimum_count', 'product_area', 'description',
    ];

    public function path()
    {
        return $this->belongsTo(PartnerPath::class, 'partner_path_id');
    }

    public function stage()
    {
        return $this->belongsTo(PartnerPathStage::class, 'stage_id');
    }
}
