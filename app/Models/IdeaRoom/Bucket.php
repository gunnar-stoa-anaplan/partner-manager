<?php

namespace App\Models\IdeaRoom;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Bucket extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'idea_buckets';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'user_id',
        'parent_bucket_id',
        'depth',
        'name',
        'color',
        'icon',
        'sort_order',
        'is_default',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Bucket $model): void {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Bucket::class, 'parent_bucket_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Bucket::class, 'parent_bucket_id');
    }

    public function ideas(): BelongsToMany
    {
        return $this->belongsToMany(Idea::class, 'idea_bucket', 'bucket_id', 'idea_id')
            ->withPivot('confidence', 'confirmed', 'created_at');
    }

    public function isRoot(): bool
    {
        return $this->parent_bucket_id === null;
    }

    public function fullPath(): string
    {
        if ($this->parent) {
            return $this->parent->name.' > '.$this->name;
        }

        return $this->name;
    }
}
