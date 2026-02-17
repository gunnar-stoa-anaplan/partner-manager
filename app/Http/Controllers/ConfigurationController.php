<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use Illuminate\Http\Request;

class ConfigurationController extends Controller
{
    public function index()
    {
        $settings = AppSetting::pluck('value', 'key');

        return view('configuration.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'fiscal_year_start_month' => 'required|integer|between:1,12',
            'default_currency' => 'required|string|max:10',
            'historical_lookback_weeks' => 'required|integer|between:1,52',
            'company_name' => 'required|string|max:255',
        ]);

        foreach ($validated as $key => $value) {
            AppSetting::set($key, $value);
        }

        return redirect()->route('configuration.index')->with('success', 'Configuration updated successfully.');
    }

    public function updateNavigation(Request $request)
    {
        $navKeys = [
            'nav_visible_analysis',
            'nav_visible_plan',
            'nav_visible_engage',
            'nav_visible_key_metrics',
            'nav_visible_business_dev',
            'nav_visible_measure',
            'nav_visible_act',
            'nav_visible_dashboards',
            'nav_visible_administration',
            'nav_visible_program_admin',
            'nav_visible_system',
        ];

        foreach ($navKeys as $key) {
            AppSetting::set($key, $request->input($key, '0'));
        }

        return redirect()->route('configuration.index')->with('success', 'Navigation visibility updated successfully.');
    }

    public function updateAi(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'ai_provider' => 'nullable|string|in:,openai,anthropic,azure_openai',
            'ai_api_key' => 'nullable|string|max:500',
            'ai_model' => 'nullable|string|max:100',
        ]);

        AppSetting::set('ai_provider', $validated['ai_provider'] ?? '');
        AppSetting::set('ai_model', $validated['ai_model'] ?? '');

        // Only update the key if a new one was provided (blank means keep existing)
        if (! empty($validated['ai_api_key'])) {
            AppSetting::setEncrypted('ai_api_key', $validated['ai_api_key']);
        }

        $providerLabel = match ($validated['ai_provider'] ?? '') {
            'openai' => 'OpenAI',
            'anthropic' => 'Anthropic',
            'azure_openai' => 'Azure OpenAI',
            default => 'Simulated (built-in)',
        };

        return redirect()->route('configuration.index')->with('success', "AI Assistant set to: {$providerLabel}");
    }

    public function updateDevFocus(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'dev_focus_feature' => 'nullable|string|max:50',
        ]);

        AppSetting::set('dev_focus_feature', $validated['dev_focus_feature'] ?? '');

        $label = $validated['dev_focus_feature']
            ? str_replace('_', ' ', ucfirst($validated['dev_focus_feature']))
            : 'Show All';

        return redirect()->route('configuration.index')->with('success', "Dev Focus set to: {$label}");
    }
}
