<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BrandingController extends Controller
{
    public function index(): View
    {
        $settings = AppSetting::pluck('value', 'key');

        $themes = collect(glob(public_path('css/themes/*.css')))
            ->map(fn (string $path) => pathinfo($path, PATHINFO_FILENAME))
            ->sort()
            ->values();

        return view('admin.branding', compact('settings', 'themes'));
    }

    public function update(Request $request): RedirectResponse
    {
        $themes = collect(glob(public_path('css/themes/*.css')))
            ->map(fn (string $path) => pathinfo($path, PATHINFO_FILENAME))
            ->implode(',');

        $request->validate([
            'branding_app_name' => 'nullable|string|max:255',
            'branding_theme' => 'nullable|string|in:'.$themes,
            'branding_logo' => 'nullable|image|mimes:jpeg,jpg,png,gif,svg|max:2048',
            'branding_favicon' => 'nullable|image|mimes:png,ico|max:512',
        ]);

        // App name
        AppSetting::set('branding_app_name', $request->input('branding_app_name', ''));

        // Theme
        AppSetting::set('branding_theme', $request->input('branding_theme', ''));

        // Logo upload
        if ($request->hasFile('branding_logo')) {
            $this->deleteOldFile('branding_logo_path');
            $path = $request->file('branding_logo')->store('branding', 'public');
            AppSetting::set('branding_logo_path', $path);
        } elseif ($request->boolean('remove_logo')) {
            $this->deleteOldFile('branding_logo_path');
            AppSetting::set('branding_logo_path', '');
        }

        // Favicon upload
        if ($request->hasFile('branding_favicon')) {
            $this->deleteOldFile('branding_favicon_path');
            $path = $request->file('branding_favicon')->store('branding', 'public');
            AppSetting::set('branding_favicon_path', $path);
        } elseif ($request->boolean('remove_favicon')) {
            $this->deleteOldFile('branding_favicon_path');
            AppSetting::set('branding_favicon_path', '');
        }

        return redirect()->route('admin.branding')->with('success', 'Branding updated successfully.');
    }

    private function deleteOldFile(string $settingKey): void
    {
        $oldPath = AppSetting::get($settingKey);

        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }
    }
}
