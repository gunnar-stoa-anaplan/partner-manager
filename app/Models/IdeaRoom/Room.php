<?php

namespace App\Models\IdeaRoom;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Room extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'idea_rooms';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'idea_id',
        'user_id',
        'name',
        'status',
        'agent_persona',
        'agent_config',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'agent_config' => 'array',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Room $model): void {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function idea(): BelongsTo
    {
        return $this->belongsTo(Idea::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(RoomMessage::class, 'idea_room_id');
    }

    public function memories(): HasMany
    {
        return $this->hasMany(AgentMemory::class, 'idea_room_id');
    }

    public function artifacts(): HasMany
    {
        return $this->hasMany(Artifact::class, 'idea_room_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(ExternalParticipant::class, 'idea_room_id');
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(Snapshot::class, 'idea_room_id');
    }
}
