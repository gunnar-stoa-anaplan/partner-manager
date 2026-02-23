<?php

namespace App\Http\Controllers\IdeaRoom\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\IdeaRoom\GalleryThemeRequest;
use App\Models\IdeaRoom\GalleryTheme;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ThemeController extends Controller
{
    public function index(): View
    {
        $themes = GalleryTheme::query()
            ->orderBy('sort_order')
            ->latest()
            ->get();

        return view('idea-room.admin.themes.index', compact('themes'));
    }

    public function create(): View
    {
        return view('idea-room.admin.themes.form', [
            'theme' => null,
        ]);
    }

    public function store(GalleryThemeRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['frame_slots'] = json_decode($data['frame_slots'], true) ?? [];
        $data['writing_area'] = json_decode($data['writing_area'] ?? 'null', true);
        $data['created_by'] = $request->user()->id;
        $data['greyscale'] = $request->boolean('greyscale');
        $data['is_published'] = $request->boolean('is_published');
        $data['is_default'] = $request->boolean('is_default');

        $data['background_image_path'] = $request->file('background_image')
            ->store('gallery-themes', 'public');

        if ($request->hasFile('template_overlay')) {
            $data['template_overlay_path'] = $request->file('template_overlay')
                ->store('gallery-themes', 'public');
        }

        if ($request->hasFile('whiteboard_image')) {
            $data['whiteboard_image_path'] = $request->file('whiteboard_image')
                ->store('gallery-themes', 'public');
        }

        $theme = GalleryTheme::create($data);

        return redirect()
            ->route('idea_room.admin.themes.edit', $theme)
            ->with('success', 'Theme created.');
    }

    public function edit(GalleryTheme $theme): View
    {
        return view('idea-room.admin.themes.form', compact('theme'));
    }

    public function update(GalleryThemeRequest $request, GalleryTheme $theme): RedirectResponse
    {
        $data = $request->validated();
        $data['frame_slots'] = json_decode($data['frame_slots'], true) ?? [];
        $data['writing_area'] = json_decode($data['writing_area'] ?? 'null', true);
        $data['greyscale'] = $request->boolean('greyscale');
        $data['is_published'] = $request->boolean('is_published');
        $data['is_default'] = $request->boolean('is_default');

        if ($request->hasFile('background_image')) {
            if ($theme->background_image_path) {
                Storage::disk('public')->delete($theme->background_image_path);
            }
            $data['background_image_path'] = $request->file('background_image')
                ->store('gallery-themes', 'public');
        } else {
            unset($data['background_image']);
        }

        if ($request->hasFile('template_overlay')) {
            if ($theme->template_overlay_path) {
                Storage::disk('public')->delete($theme->template_overlay_path);
            }
            $data['template_overlay_path'] = $request->file('template_overlay')
                ->store('gallery-themes', 'public');
        }

        if ($request->hasFile('whiteboard_image')) {
            if ($theme->whiteboard_image_path) {
                Storage::disk('public')->delete($theme->whiteboard_image_path);
            }
            $data['whiteboard_image_path'] = $request->file('whiteboard_image')
                ->store('gallery-themes', 'public');
        }

        $theme->update($data);

        return redirect()
            ->route('idea_room.admin.themes.edit', $theme)
            ->with('success', 'Theme updated.');
    }

    public function destroy(GalleryTheme $theme): RedirectResponse
    {
        if ($theme->is_default) {
            return back()->with('error', 'Cannot delete the default theme.');
        }

        $theme->delete();

        return redirect()
            ->route('idea_room.admin.themes.index')
            ->with('success', 'Theme deleted.');
    }

    public function togglePublish(GalleryTheme $theme): RedirectResponse
    {
        if ($theme->is_default && $theme->is_published) {
            return back()->with('error', 'Cannot unpublish the default theme.');
        }

        $theme->update(['is_published' => ! $theme->is_published]);

        return back()->with('success', 'Theme '.($theme->is_published ? 'published' : 'unpublished').'.');
    }

    public function setDefault(GalleryTheme $theme): RedirectResponse
    {
        $theme->update(['is_default' => true, 'is_published' => true]);

        return back()->with('success', "\"{$theme->name}\" is now the default theme.");
    }
}
