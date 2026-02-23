<?php

namespace App\Models\IdeaRoom;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ExternalParticipant extends Model
{
    use HasFactory;

    protected $table = 'idea_external_participants';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'idea_room_id',
        'invited_by',
        'participant_type',
        'name',
        'email',
        'agent_endpoint',
        'agent_auth_token',
        'permissions',
        'snapshot_id',
        'status',
        'accepted_at',
        'revoked_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'accepted_at' => 'datetime',
            'revoked_at' => 'datetime',
            'agent_auth_token' => 'encrypted',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (ExternalParticipant $model): void {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'idea_room_id');
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function snapshot(): BelongsTo
    {
        return $this->belongsTo(Snapshot::class);
    }
}
