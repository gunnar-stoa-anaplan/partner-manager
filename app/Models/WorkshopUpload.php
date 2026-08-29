<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkshopUpload extends Model
{
    use HasFactory;

    protected $fillable = [
        'workshop_thread_id',
        'workshop_reply_id',
        'filename',
        'stored_path',
        'mime_type',
        'size_bytes',
        'uploaded_by_user_id',
        'uploaded_by_contact_id',
    ];

    public function thread(): BelongsTo
    {
        return $this->belongsTo(WorkshopThread::class, 'workshop_thread_id');
    }

    public function reply(): BelongsTo
    {
        return $this->belongsTo(WorkshopReply::class, 'workshop_reply_id');
    }

    public function uploaderUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }

    public function uploaderContact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'uploaded_by_contact_id');
    }
}
