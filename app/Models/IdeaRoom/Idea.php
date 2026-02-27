<?php

namespace App\Models\IdeaRoom;

use App\Enums\IdeaRoom\EventType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Idea extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ideas';

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'user_id',
        'title',
        'original_text',
        'lifecycle_state',
        'dormant_at',
        'last_activity_at',
        'metadata',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'dormant_at' => 'datetime',
            'last_activity_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Idea $model): void {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }

            if (empty($model->last_activity_at)) {
                $model->last_activity_at = now();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(IdeaEvent::class);
    }

    public function buckets(): BelongsToMany
    {
        return $this->belongsToMany(Bucket::class, 'idea_bucket', 'idea_id', 'bucket_id')
            ->withPivot('confidence', 'confirmed', 'created_at');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'idea_tag')
            ->withPivot('source', 'created_at');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(IdeaEvent::class)
            ->where('event_type', EventType::NoteAdded->value);
    }

    public function outgoingEdges(): HasMany
    {
        return $this->hasMany(Edge::class, 'source_idea_id');
    }

    public function incomingEdges(): HasMany
    {
        return $this->hasMany(Edge::class, 'target_idea_id');
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class, 'idea_id');
    }

    public function artifacts(): HasMany
    {
        return $this->hasMany(Artifact::class);
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(Snapshot::class);
    }
}
