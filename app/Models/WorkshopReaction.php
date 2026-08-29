<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkshopReaction extends Model
{
    protected $fillable = [
        'workshop_thread_id',
        'author_user_id',
        'author_contact_id',
        'kind',
    ];

    public function thread(): BelongsTo
    {
        return $this->belongsTo(WorkshopThread::class, 'workshop_thread_id');
    }
}
