<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkshopToolkitAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'workshop_id',
        'title',
        'description',
        'file_path',
        'file_name',
        'file_size',
        'uploaded_by',
    ];

    public function workshop(): BelongsTo
    {
        return $this->belongsTo(Workshop::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
