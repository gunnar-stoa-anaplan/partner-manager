<?php

namespace App\Http\Controllers\IdeaRoom;

use App\Http\Controllers\Controller;
use App\Services\IdeaRoom\GalleryThemeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class WhiteboardController extends Controller
{
    public function __invoke(Request $request, GalleryThemeService $themeService): View
    {
        $user = $request->user();
        $slug = $user->preferences['gallery_template'] ?? null;

        $theme = $slug ? $themeService->find($slug) : null;
        $theme ??= $themeService->default();

        $arW = 3;
        $arH = 2;
        if ($theme?->whiteboard_image_path && Storage::disk('public')->exists($theme->whiteboard_image_path)) {
            $size = getimagesize(Storage::disk('public')->path($theme->whiteboard_image_path));
            if ($size) {
                $arW = $size[0];
                $arH = $size[1];
            }
        }

        $writingArea = $theme?->writing_area ?? ['top' => 13, 'left' => 14, 'width' => 72, 'height' => 41];

        return view('idea-room.whiteboard', [
            'whiteboardImageUrl' => $theme?->whiteboard_image_url,
            'arW' => $arW,
            'arH' => $arH,
            'writingArea' => $writingArea,
        ]);
    }
}
