<?php

namespace App\Models\IdeaRoom;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentMemory extends Model
{
    use HasFactory;

    protected $table = 'agent_memories';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'idea_room_id',
        'memory_type',
        'content',
        'importance',
        'source_message_id',
        'embedding_id',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'idea_room_id');
    }

    public function sourceMessage(): BelongsTo
    {
        return $this->belongsTo(RoomMessage::class, 'source_message_id');
    }
}
