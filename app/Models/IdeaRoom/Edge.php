<?php

namespace App\Models\IdeaRoom;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Edge extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'idea_edges';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'source_idea_id',
        'target_idea_id',
        'edge_type',
        'status',
        'proposed_by',
        'confidence',
        'reason',
        'confirmed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'confirmed_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Edge $model): void {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function sourceIdea(): BelongsTo
    {
        return $this->belongsTo(Idea::class, 'source_idea_id');
    }

    public function targetIdea(): BelongsTo
    {
        return $this->belongsTo(Idea::class, 'target_idea_id');
    }
}
