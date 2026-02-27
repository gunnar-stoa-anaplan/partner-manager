<?php

namespace Database\Seeders;

use App\Models\IdeaRoom\GalleryTheme;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class GalleryThemeSeeder extends Seeder
{
    public function run(): void
    {
        if (GalleryTheme::query()->exists()) {
            return;
        }

        $admin = User::whereRelation('role', 'slug', 'admin')->first()
            ?? User::first();

        if (! $admin) {
            $this->command->warn('No users found — skipping GalleryThemeSeeder.');

            return;
        }

        // Theme 1: Modern Office Gallery 10 (10 frames + whiteboard)
        $galleryWallSource = public_path('images/idea-room/gallery-wall.png');
        $galleryWallPath = 'gallery-themes/gallery-wall-bg.png';

        if (file_exists($galleryWallSource) && ! Storage::disk('public')->exists($galleryWallPath)) {
            Storage::disk('public')->put($galleryWallPath, file_get_contents($galleryWallSource));
        }

        $whiteboardSource = public_path('images/idea-room/gallery-wall-whiteboard.png');
        $whiteboardPath = 'gallery-themes/gallery-wall-whiteboard.png';

        if (file_exists($whiteboardSource) && ! Storage::disk('public')->exists($whiteboardPath)) {
            Storage::disk('public')->put($whiteboardPath, file_get_contents($whiteboardSource));
        }

        GalleryTheme::create([
            'name' => 'Modern Office Gallery 10',
            'slug' => 'modern-office-gallery-10',
            'description' => 'The original gallery wall with ten balanced picture frames.',
            'background_image_path' => $galleryWallPath,
            'whiteboard_image_path' => $whiteboardPath,
            'frame_slots' => [
                ['left' => 25.5, 'top' => 10.7, 'width' => 12.8, 'height' => 15.5],
                ['left' => 44.1, 'top' => 11.0, 'width' => 12.3, 'height' => 15.2],
                ['left' => 62.3, 'top' => 11.1, 'width' => 12.2, 'height' => 15.2],
                ['left' => 26.2, 'top' => 37.1, 'width' => 6.2, 'height' => 11.8],
                ['left' => 39.5, 'top' => 37.2, 'width' => 6.5, 'height' => 11.6],
                ['left' => 52.8, 'top' => 37.1, 'width' => 7.5, 'height' => 12.4],
                ['left' => 66.9, 'top' => 37.3, 'width' => 7.4, 'height' => 11.3],
                ['left' => 25.9, 'top' => 60.5, 'width' => 13.9, 'height' => 16.1],
                ['left' => 46.2, 'top' => 60.3, 'width' => 16.8, 'height' => 16.3],
                ['left' => 69.5, 'top' => 60.7, 'width' => 5.1, 'height' => 10.0],
            ],
            'writing_area' => ['left' => 20.1, 'top' => 23.7, 'width' => 59.5, 'height' => 36.8],
            'aspect_ratio' => '16 / 9',
            'greyscale' => false,
            'is_published' => true,
            'is_default' => false,
            'sort_order' => 0,
            'created_by' => $admin->id,
        ]);

        // Theme 2: Modern Office (12 frames, default)
        $modernSource = public_path('images/idea-room/modern-office-bg.png');
        $modernPath = 'gallery-themes/modern-office-bg.png';

        if (file_exists($modernSource) && ! Storage::disk('public')->exists($modernPath)) {
            Storage::disk('public')->put($modernPath, file_get_contents($modernSource));
        }

        GalleryTheme::create([
            'name' => 'Modern Office',
            'slug' => 'modern-office',
            'description' => 'A sleek, modern office wall with 12 frames.',
            'background_image_path' => $modernPath,
            'frame_slots' => [
                ['left' => 27.3, 'top' => 13.6, 'width' => 8.9, 'height' => 13.2],
                ['left' => 41.3, 'top' => 14.9, 'width' => 6.1, 'height' => 10.5],
                ['left' => 52.3, 'top' => 14.9, 'width' => 6.8, 'height' => 11.2],
                ['left' => 64.4, 'top' => 13.5, 'width' => 15.7, 'height' => 13.0],
                ['left' => 27.5, 'top' => 36.3, 'width' => 10.8, 'height' => 19.4],
                ['left' => 43.7, 'top' => 34.6, 'width' => 10.5, 'height' => 13.4],
                ['left' => 59.2, 'top' => 35.7, 'width' => 7.0, 'height' => 12.7],
                ['left' => 71.4, 'top' => 35.4, 'width' => 9.8, 'height' => 13.0],
                ['left' => 32.8, 'top' => 65.1, 'width' => 5.6, 'height' => 7.9],
                ['left' => 43.6, 'top' => 56.9, 'width' => 10.7, 'height' => 16.2],
                ['left' => 59.7, 'top' => 57.2, 'width' => 11.0, 'height' => 11.4],
                ['left' => 75.9, 'top' => 57.6, 'width' => 5.2, 'height' => 11.0],
            ],
            'aspect_ratio' => '16 / 9',
            'greyscale' => false,
            'is_published' => true,
            'is_default' => true,
            'sort_order' => 1,
            'created_by' => $admin->id,
        ]);
    }
}
