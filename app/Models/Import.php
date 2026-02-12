<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Import extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'source',
        'file_name',
        'file_path',
        'total_rows',
        'successful_rows',
        'failed_rows',
        'partners_created',
        'partners_updated',
        'opportunities_created',
        'opportunities_updated',
        'projects_created',
        'projects_updated',
        'contacts_created',
        'contacts_updated',
        'resources_created',
        'status',
        'error_log',
        'summary',
        'user_id',
        'api_token_id',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'summary' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function opportunities(): HasMany
    {
        return $this->hasMany(Opportunity::class);
    }

    public function resources(): HasMany
    {
        return $this->hasMany(Resource::class);
    }
}
