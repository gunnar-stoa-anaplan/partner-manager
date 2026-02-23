<?php

namespace App\Http\Controllers\IdeaRoom\Api;

use App\Http\Controllers\Controller;
use App\Services\IdeaRoom\GalleryThemeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class GalleryController extends Controller
{
    public function __construct(private GalleryThemeService $themeService) {}

    public function templates(): JsonResponse
    {
        return response()->json(['data' => $this->themeService->all()]);
    }

    public function config(Request $request): JsonResponse
    {
        $user = $request->user();
        $slug = $user->preferences['gallery_template'] ?? null;

        $theme = $slug ? $this->themeService->find($slug) : null;
        $theme ??= $this->themeService->default();

        $outputPath = $this->userGalleryPath($user->id);
        $version = File::exists($outputPath) ? File::lastModified($outputPath) : 0;

        $videoManifestPath = $this->userVideoManifestPath($user->id);
        $videos = null;
        $templateImageUrl = null;

        if (File::exists($videoManifestPath)) {
            $videoData = json_decode(File::get($videoManifestPath), true);

            if (($videoData['template'] ?? '') === $theme->slug) {
                $videos = $videoData['videos'] ?? [];
                $templateImageUrl = $theme->template_overlay_url
                    ?? route('idea_room.gallery.template', ['slug' => $theme->slug]);
            }
        }

        return response()->json([
            'template' => $theme->slug,
            'name' => $theme->name,
            'greyscale' => $theme->greyscale,
            'aspect_ratio' => $theme->aspect_ratio,
            'gallery_image_url' => $version > 0
                ? route('idea_room.gallery.wall', ['v' => $version])
                : null,
            'mode' => $videos ? 'video' : 'static',
            'videos' => $videos,
            'template_image_url' => $templateImageUrl,
            'frame_slots' => $theme->frame_slots,
            'background_image_url' => $theme->background_image_url,
            'whiteboard_image_url' => $theme->whiteboard_image_url,
        ]);
    }

    public function setTemplate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'slug' => 'required|string|max:64',
        ]);

        if (! $this->themeService->find($validated['slug'])) {
            return response()->json(['message' => 'Template not found.'], 422);
        }

        $user = $request->user();
        $preferences = $user->preferences ?? [];
        $preferences['gallery_template'] = $validated['slug'];
        $user->preferences = $preferences;
        $user->save();

        $stalePng = $this->userGalleryPath($user->id);

        if (File::exists($stalePng)) {
            File::delete($stalePng);
        }

        return response()->json(['message' => 'Template updated.', 'template' => $validated['slug']]);
    }

    private function userGalleryPath(int $userId): string
    {
        return config('idea_room.gallery.output.directory')."/gallery-wall-user-{$userId}.png";
    }

    private function userVideoManifestPath(int $userId): string
    {
        return config('idea_room.gallery.output.directory')."/gallery-videos-user-{$userId}.json";
    }
}
