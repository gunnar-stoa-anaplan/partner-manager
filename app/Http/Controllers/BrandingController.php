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
            'branding_color_primary' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'branding_color_success' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'branding_color_info' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'branding_color_warning' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'branding_color_danger' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'branding_custom_css' => ['nullable', 'file', 'max:1024', function (string $attribute, mixed $value, \Closure $fail) {
                if (strtolower($value->getClientOriginalExtension()) !== 'css') {
                    $fail('The custom CSS file must have a .css extension.');
                }
            }],
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

        // Custom brand colors
        $colorNames = ['primary', 'success', 'info', 'warning', 'danger'];

        if ($request->boolean('clear_custom_colors')) {
            foreach ($colorNames as $name) {
                AppSetting::set("branding_color_{$name}", '');
            }
            AppSetting::set('branding_custom_colors_css', '');
        } else {
            $customColors = [];
            foreach ($colorNames as $name) {
                $value = $request->input("branding_color_{$name}", '');
                AppSetting::set("branding_color_{$name}", $value);
                if ($value) {
                    $customColors[$name] = $value;
                }
            }
            AppSetting::set(
                'branding_custom_colors_css',
                $customColors ? $this->generateCustomColorsCss($customColors) : ''
            );
        }

        // Custom CSS file upload
        if ($request->hasFile('branding_custom_css')) {
            $this->deleteOldFile('branding_custom_css_path');
            $path = $request->file('branding_custom_css')->store('branding', 'public');
            AppSetting::set('branding_custom_css_path', $path);
        } elseif ($request->boolean('remove_custom_css')) {
            $this->deleteOldFile('branding_custom_css_path');
            AppSetting::set('branding_custom_css_path', '');
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

    /**
     * Generate inline CSS with custom color variable overrides.
     *
     * @param  array<string, string>  $colors  Map of color name to hex value
     */
    private function generateCustomColorsCss(array $colors): string
    {
        $lines = [];

        foreach ($colors as $name => $hex) {
            $rgb = $this->hexToRgb($hex);
            $lines[] = "--bs-{$name}: {$hex}";
            $lines[] = "--bs-{$name}-rgb: {$rgb['r']},{$rgb['g']},{$rgb['b']}";

            $shades = [
                50 => 0.80, 100 => 0.65, 200 => 0.50, 300 => 0.35, 400 => 0.15,
                600 => -0.10, 700 => -0.25, 800 => -0.40, 900 => -0.55,
            ];

            foreach ($shades as $level => $factor) {
                $target = $factor > 0
                    ? ['r' => 255, 'g' => 255, 'b' => 255]
                    : ['r' => 0, 'g' => 0, 'b' => 0];
                $shade = $this->mixColor($rgb, $target, abs($factor));
                $lines[] = "--{$name}-{$level}: {$shade}";
            }

            $lines[] = "--{$name}-500: {$hex}";
        }

        return ":root, [data-bs-theme=light] {\n    ".implode(";\n    ", $lines).";\n}";
    }

    /**
     * @return array{r: int, g: int, b: int}
     */
    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2)),
        ];
    }

    /**
     * Mix a color with a target color by a given amount.
     *
     * @param  array{r: int, g: int, b: int}  $color
     * @param  array{r: int, g: int, b: int}  $target
     */
    private function mixColor(array $color, array $target, float $amount): string
    {
        $r = (int) round($color['r'] + ($target['r'] - $color['r']) * $amount);
        $g = (int) round($color['g'] + ($target['g'] - $color['g']) * $amount);
        $b = (int) round($color['b'] + ($target['b'] - $color['b']) * $amount);

        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }
}
