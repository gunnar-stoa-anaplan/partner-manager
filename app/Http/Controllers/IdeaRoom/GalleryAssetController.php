<?php

namespace App\Http\Controllers\IdeaRoom;

use App\Http\Controllers\Controller;
use App\Models\IdeaRoom\GalleryTheme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class GalleryAssetController extends Controller
{
    public function wall(Request $request): BinaryFileResponse
    {
        $path = config('idea_room.gallery.output_directory')
            .'/gallery-wall-user-'.$request->user()->id.'.png';

        abort_unless(file_exists($path), 404, 'Gallery wall not yet generated.');

        return response()->file($path, ['Content-Type' => 'image/png']);
    }

    public function template(Request $request): BinaryFileResponse
    {
        $slug = $request->query('slug', config('idea_room.gallery.default_template'));

        $theme = GalleryTheme::where('slug', $slug)
            ->where('is_published', true)
            ->first();

        if ($theme && $theme->template_overlay_path) {
            $path = Storage::disk('public')->path($theme->template_overlay_path);
            abort_unless(file_exists($path), 404, 'Template overlay not found.');

            return response()->file($path, [
                'Content-Type' => 'image/png',
                'Cache-Control' => 'public, max-age=86400',
            ]);
        }

        // Fallback to filesystem
        $path = config('idea_room.gallery.templates_path').'/'.$slug.'/template.png';
        abort_unless(file_exists($path), 404, 'Template not found.');

        return response()->file($path, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
