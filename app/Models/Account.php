<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        // Add any other relevant fields here
    ];

    public function partners()
    {
        return $this->belongsToMany(Partner::class, 'account_partner')
                    ->withTimestamps(); // ensures pivot table timestamps are maintained
    }
}