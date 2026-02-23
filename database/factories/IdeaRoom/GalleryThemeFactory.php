<?php

namespace Database\Factories\IdeaRoom;

use App\Models\IdeaRoom\GalleryTheme;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<GalleryTheme>
 */
class GalleryThemeFactory extends Factory
{
    protected $model = GalleryTheme::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true).' Gallery';

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'background_image_path' => 'gallery-themes/default-bg.jpg',
            'frame_slots' => [
                ['left' => 3, 'top' => 10, 'width' => 27, 'height' => 38],
                ['left' => 36, 'top' => 10, 'width' => 27, 'height' => 38],
                ['left' => 69, 'top' => 10, 'width' => 27, 'height' => 38],
                ['left' => 3, 'top' => 54, 'width' => 27, 'height' => 38],
                ['left' => 36, 'top' => 54, 'width' => 27, 'height' => 38],
                ['left' => 69, 'top' => 54, 'width' => 27, 'height' => 38],
            ],
            'aspect_ratio' => '16 / 9',
            'greyscale' => false,
            'is_published' => true,
            'is_default' => false,
            'sort_order' => 0,
            'created_by' => User::factory(),
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => ['is_published' => true]);
    }

    public function unpublished(): static
    {
        return $this->state(fn () => ['is_published' => false]);
    }

    public function default(): static
    {
        return $this->state(fn () => ['is_default' => true, 'is_published' => true]);
    }
}
