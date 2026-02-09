<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'email', 'phone', 'company', 'title', 'source',
        'status', 'partner_id', 'notes'
    ];

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }
}