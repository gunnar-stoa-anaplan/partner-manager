<?php

namespace App\Models\IdeaRoom;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GalleryTheme extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'gallery_themes';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'background_image_path',
        'template_overlay_path',
        'thumbnail_path',
        'whiteboard_image_path',
        'frame_slots',
        'writing_area',
        'aspect_ratio',
        'max_frames',
        'greyscale',
        'is_published',
        'is_default',
        'sort_order',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'frame_slots' => 'array',
            'writing_area' => 'array',
            'greyscale' => 'boolean',
            'is_published' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (GalleryTheme $model): void {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });

        static::saving(function (GalleryTheme $model): void {
            $model->max_frames = count($model->frame_slots ?? []);

            if ($model->is_default) {
                DB::table('gallery_themes')
                    ->where('id', '!=', $model->id ?? 0)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }
        });
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @param  Builder<GalleryTheme>  $query
     * @return Builder<GalleryTheme>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)->orderBy('sort_order');
    }

    public function getBackgroundImageUrlAttribute(): ?string
    {
        if (! $this->background_image_path) {
            return null;
        }

        return asset('storage/'.$this->background_image_path);
    }

    public function getTemplateOverlayUrlAttribute(): ?string
    {
        if (! $this->template_overlay_path) {
            return null;
        }

        return asset('storage/'.$this->template_overlay_path);
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if (! $this->thumbnail_path) {
            return null;
        }

        return asset('storage/'.$this->thumbnail_path);
    }

    public function getWhiteboardImageUrlAttribute(): ?string
    {
        if (! $this->whiteboard_image_path) {
            return null;
        }

        return asset('storage/'.$this->whiteboard_image_path);
    }
}
