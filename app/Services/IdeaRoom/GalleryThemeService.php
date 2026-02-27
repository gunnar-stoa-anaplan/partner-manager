<?php

namespace App\Services\IdeaRoom;

use App\Models\IdeaRoom\GalleryTheme;
use Illuminate\Database\Eloquent\Collection;

class GalleryThemeService
{
    /**
     * @return Collection<int, GalleryTheme>
     */
    public function published(): Collection
    {
        return GalleryTheme::query()->published()->get();
    }

    /**
     * @return list<array{slug: string, name: string, max_frames: int, greyscale: bool, thumbnail_url: ?string}>
     */
    public function all(): array
    {
        return $this->published()->map(fn (GalleryTheme $t) => [
            'slug' => $t->slug,
            'name' => $t->name,
            'max_frames' => $t->max_frames,
            'greyscale' => $t->greyscale,
            'thumbnail_url' => $t->thumbnail_url ?? $t->background_image_url,
        ])->values()->toArray();
    }

    public function find(string $slug): ?GalleryTheme
    {
        return GalleryTheme::where('slug', $slug)
            ->where('is_published', true)
            ->first();
    }

    public function default(): GalleryTheme
    {
        return GalleryTheme::where('is_default', true)->first()
            ?? GalleryTheme::query()->published()->first()
            ?? $this->fallbackTheme();
    }

    /**
     * @return list<array{left: float, top: float, width: float, height: float}>
     */
    public function frameSlots(string $slug): array
    {
        return $this->find($slug)?->frame_slots ?? $this->default()->frame_slots;
    }

    private function fallbackTheme(): GalleryTheme
    {
        $theme = new GalleryTheme;
        $theme->slug = 'default';
        $theme->name = 'Classic Gallery';
        $theme->greyscale = false;
        $theme->aspect_ratio = '16 / 9';
        $theme->frame_slots = [
            ['left' => 3, 'top' => 10, 'width' => 27, 'height' => 38],
            ['left' => 36, 'top' => 10, 'width' => 27, 'height' => 38],
            ['left' => 69, 'top' => 10, 'width' => 27, 'height' => 38],
            ['left' => 3, 'top' => 54, 'width' => 27, 'height' => 38],
            ['left' => 36, 'top' => 54, 'width' => 27, 'height' => 38],
            ['left' => 69, 'top' => 54, 'width' => 27, 'height' => 38],
        ];
        $theme->max_frames = 6;

        return $theme;
    }
}
