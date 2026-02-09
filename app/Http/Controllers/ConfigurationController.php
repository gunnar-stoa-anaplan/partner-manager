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
}
