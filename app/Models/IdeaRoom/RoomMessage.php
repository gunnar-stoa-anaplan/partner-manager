<?php

namespace App\Models\IdeaRoom;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class RoomMessage extends Model
{
    use HasFactory;

    protected $table = 'room_messages';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'idea_room_id',
        'sender_type',
        'sender_id',
        'content',
        'metadata',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (RoomMessage $model): void {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'idea_room_id');
    }
}
