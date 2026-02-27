<?php

namespace App\Models;

use App\Enums\DocumentSource;
use App\Enums\DocumentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KnowledgeDocument extends Model
{
    /** @use HasFactory<\Database\Factories\KnowledgeDocumentFactory> */
    use HasFactory;

    protected $connection = 'knowledge_base';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'file_name',
        'file_path',
        'mime_type',
        'file_size_bytes',
        'source',
        'storage_disk',
        'status',
        'chunk_count',
        'error_message',
        'metadata',
        'checksum',
        'uploaded_by',
        'ingested_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'file_size_bytes' => 'integer',
            'chunk_count' => 'integer',
            'metadata' => 'array',
            'uploaded_by' => 'integer',
            'ingested_at' => 'datetime',
            'status' => DocumentStatus::class,
            'source' => DocumentSource::class,
        ];
    }

    /**
     * Whether this document's file is stored on a remote Storage disk.
     */
    public function isRemote(): bool
    {
        return $this->storage_disk !== null;
    }

    public function chunks(): HasMany
    {
        return $this->hasMany(KnowledgeChunk::class, 'document_id');
    }

    /**
     * Cross-database lookup for the user who uploaded this document.
     */
    public function uploader(): ?User
    {
        if (! $this->uploaded_by) {
            return null;
        }

        return User::find($this->uploaded_by);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', DocumentStatus::Completed);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', DocumentStatus::Pending);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', DocumentStatus::Failed);
    }
}
