<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResourceSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'partner_id',
        'import_id',
        'snapshot_date',
        'program',
        'role',
        'started_count',
        'completed_count',
        'total_count',
        'unique_people_count',
    ];

    protected $casts = [
        'snapshot_date' => 'date',
    ];

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function import(): BelongsTo
    {
        return $this->belongsTo(Import::class);
    }
}
