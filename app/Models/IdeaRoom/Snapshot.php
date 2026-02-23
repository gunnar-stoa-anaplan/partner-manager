<?php

namespace App\Models\IdeaRoom;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Snapshot extends Model
{
    use HasFactory;

    protected $table = 'idea_snapshots';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'idea_id',
        'idea_room_id',
        'created_by',
        'title',
        'scope_config',
        'content_cache',
        'access_token',
        'expires_at',
        'max_uses',
        'use_count',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scope_config' => 'array',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Snapshot $model): void {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }

            if (empty($model->access_token)) {
                $model->access_token = Str::random(64);
            }
        });
    }

    public function idea(): BelongsTo
    {
        return $this->belongsTo(Idea::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'idea_room_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(ExternalParticipant::class);
    }
}
