<?php

namespace App\Http\Controllers\IdeaRoom\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        $settings = AppSetting::where('key', 'like', 'idea_room.%')
            ->pluck('value', 'key');

        return view('idea-room.admin.settings', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ai_provider' => 'nullable|string|in:,anthropic,openai',
            'ai_model' => 'nullable|string|max:100',
            'ai_api_key' => 'nullable|string|max:500',
            'default_gallery_template' => 'nullable|string|max:64',
            'max_rooms_per_user' => 'required|integer|between:1,100',
            'allow_external_participants' => 'nullable|boolean',
        ]);

        AppSetting::set('idea_room.ai_provider', $validated['ai_provider'] ?? '');
        AppSetting::set('idea_room.ai_model', $validated['ai_model'] ?? '');
        AppSetting::set('idea_room.default_gallery_template', $validated['default_gallery_template'] ?? '');
        AppSetting::set('idea_room.max_rooms_per_user', $validated['max_rooms_per_user']);
        AppSetting::set('idea_room.allow_external_participants', $validated['allow_external_participants'] ?? '0');

        if (! empty($validated['ai_api_key'])) {
            AppSetting::setEncrypted('idea_room.ai_api_key', $validated['ai_api_key']);
        }

        return redirect()->route('idea_room.admin.settings')->with('success', 'Idea Room settings updated.');
    }
}
