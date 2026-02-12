<?php
namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Services\TelemetryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TelemetryController extends Controller
{
    public function index(TelemetryService $service)
    {
        $settings = [
            'telemetry_endpoint' => AppSetting::get('telemetry_endpoint'),
            'telemetry_api_key' => AppSetting::get('telemetry_api_key'),
            'telemetry_enabled' => AppSetting::get('telemetry_enabled', '1'),
            'telemetry_interval' => AppSetting::get('telemetry_interval', '60'),
            'instance_id' => AppSetting::get('instance_id'),
            'license_key' => AppSetting::get('license_key'),
        ];

        $latestHeartbeat = DB::table('telemetry_heartbeats')->orderByDesc('created_at')->first();
        $recentEvents = DB::table('telemetry_events')->orderByDesc('created_at')->limit(20)->get();
        $pendingCount = DB::table('telemetry_events')->where('status', 'pending')->count();

        // Quick preview of current metrics
        $preview = $service->collectHeartbeat();

        return view('admin.telemetry.index', compact('settings', 'latestHeartbeat', 'recentEvents', 'pendingCount', 'preview'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'telemetry_endpoint' => 'nullable|url',
            'telemetry_api_key' => 'nullable|string',
            'telemetry_interval' => 'required|integer|min:5|max:1440',
            'license_key' => 'nullable|string|max:255',
        ]);

        AppSetting::set('telemetry_endpoint', $request->telemetry_endpoint);
        AppSetting::set('telemetry_api_key', $request->telemetry_api_key);
        AppSetting::set('telemetry_enabled', $request->boolean('telemetry_enabled') ? '1' : '0');
        AppSetting::set('telemetry_interval', $request->telemetry_interval);
        AppSetting::set('license_key', $request->license_key);

        return redirect()->route('admin.telemetry.index')->with('success', 'Telemetry settings updated.');
    }

    public function sendNow(TelemetryService $service)
    {
        $sent = $service->sendHeartbeat();
        $msg = $sent ? 'Heartbeat sent successfully.' : 'Failed to send heartbeat (check endpoint configuration).';
        return redirect()->back()->with($sent ? 'success' : 'error', $msg);
    }
}
