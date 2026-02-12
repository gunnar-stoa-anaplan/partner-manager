<?php
namespace App\Http\Controllers;

use App\Models\NotificationTemplate;
use App\Services\NotificationDispatchService;
use Illuminate\Http\Request;

class NotificationTemplateController extends Controller
{
    public function index()
    {
        $templates = NotificationTemplate::orderBy('event_type')->get();

        $eventTypes = [
            'blueprint_assigned', 'resource_enrolled_blueprint', 'blueprint_completed', 'blueprint_overdue',
            'event_registration', 'event_reminder', 'event_completed',
            'certification_earned', 'certification_expiring',
            'task_assigned', 'task_overdue',
            'path_stage_advanced', 'qbr_scheduled', 'alert_generated',
        ];

        return view('admin.notification-templates.index', compact('templates', 'eventTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'event_type' => 'required|string',
            'channel' => 'required|in:email,in_app,both',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        NotificationTemplate::create([
            'event_type' => $request->event_type,
            'channel' => $request->channel,
            'subject' => $request->subject,
            'body' => $request->body,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.notification-templates.index')->with('success', 'Template created.');
    }

    public function update(Request $request, NotificationTemplate $template)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $template->update([
            'subject' => $request->subject,
            'body' => $request->body,
            'channel' => $request->channel ?? $template->channel,
            'is_active' => $request->boolean('is_active', true),
            'updated_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Template updated.');
    }

    public function destroy(NotificationTemplate $template)
    {
        $template->delete();
        return redirect()->route('admin.notification-templates.index')->with('success', 'Template deleted.');
    }

    public function preview(Request $request, NotificationTemplate $template)
    {
        $variables = NotificationDispatchService::getAvailableVariables($template->event_type);
        $sampleData = [];
        foreach ($variables as $var) {
            $sampleData[$var] = "[{$var}]";
        }

        $rendered = [
            'subject' => str_replace(array_map(fn($v) => '{{'.$v.'}}', $variables), array_values($sampleData), $template->subject),
            'body' => str_replace(array_map(fn($v) => '{{'.$v.'}}', $variables), array_values($sampleData), $template->body),
        ];

        return response()->json(['rendered' => $rendered, 'variables' => $variables]);
    }
}
