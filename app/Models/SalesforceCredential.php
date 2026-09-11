<?php

namespace App\Models;

use App\Models\Concerns\ScopedToOwner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SalesforceCredential extends Model
{
    use HasFactory, ScopedToOwner;

    protected $fillable = [
        'user_id',
        'client_id',
        'username',
        'client_secret_encrypted',
        'instance_url',
        'redirect_uri',
        'auth_method',
    ];

    protected $casts = [
        'client_secret_encrypted' => 'encrypted',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Accessor alias for convenience
    public function getClientSecretAttribute()
    {
        return $this->client_secret_encrypted;
    }

    public function setClientSecretAttribute($value)
    {
        $this->client_secret_encrypted = $value;
    }
}