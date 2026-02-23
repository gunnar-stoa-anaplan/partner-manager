<?php

namespace App\Http\Controllers\IntegrationHub;

use App\Http\Controllers\Controller;
use App\Http\Requests\IntegrationHub\ApplyTemplateRequest;
use App\Models\FieldMapping;
use App\Models\IntegrationConnection;
use App\Models\MappingTemplate;
use App\Services\ModuleRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MappingTemplateController extends Controller
{
    public function __construct(private ModuleRegistry $registry) {}

    public function index(): View
    {
        $templates = MappingTemplate::query()
            ->current()
            ->forFeatures($this->licensedFeatures())
            ->orderBy('target_model')
            ->get();

        $connections = IntegrationConnection::query()
            ->where('status', 'active')
            ->get();

        // Detect which connections have template updates available
        $updatesAvailable = [];
        foreach ($connections as $connection) {
            $applied = $connection->applied_templates ?? [];
            foreach ($templates as $template) {
                $appliedVersion = $applied[$template->target_model] ?? null;
                if ($appliedVersion !== null && version_compare($appliedVersion, $template->version, '<')) {
                    $updatesAvailable[$connection->id][$template->target_model] = $template;
                }
            }
        }

        return view('integration-hub.templates.index', compact('templates', 'connections', 'updatesAvailable'));
    }

    public function review(MappingTemplate $template, Request $request): View
    {
        $connection = IntegrationConnection::findOrFail($request->query('connection_id'));

        $existingMappings = FieldMapping::query()
            ->where('connection_id', $connection->id)
            ->where('target_model', $template->target_model)
            ->get();

        $diff = $template->diffAgainst($existingMappings);

        $appliedVersion = ($connection->applied_templates ?? [])[$template->target_model] ?? null;
        $isUpdate = $appliedVersion !== null;

        return view('integration-hub.templates.review', compact('template', 'connection', 'diff', 'isUpdate', 'appliedVersion'));
    }

    public function apply(ApplyTemplateRequest $request, MappingTemplate $template): RedirectResponse
    {
        $connection = IntegrationConnection::findOrFail($request->validated('connection_id'));
        $selectedKeys = $request->validated('fields');

        $existingMappings = FieldMapping::query()
            ->where('connection_id', $connection->id)
            ->where('target_model', $template->target_model)
            ->get();

        $diff = $template->diffAgainst($existingMappings);
        $applied = 0;

        foreach ($diff as $item) {
            if (! in_array($item['key'], $selectedKeys)) {
                continue;
            }

            $field = $item['template'];

            if ($item['status'] === 'new' && $field) {
                FieldMapping::create([
                    'connection_id' => $connection->id,
                    'name' => $field['description'] ?? ucwords(str_replace('_', ' ', $field['key'])),
                    'source_field' => $field['source_field'],
                    'target_model' => $template->target_model,
                    'target_field' => $field['key'],
                    'transform' => $field['transform'] ?? null,
                    'default_value' => $field['default_value'] ?? null,
                    'is_required' => $field['is_required'] ?? false,
                    'sort_order' => $applied,
                ]);
                $applied++;
            } elseif ($item['status'] === 'changed' && $field && $item['mapping']) {
                $item['mapping']->update([
                    'transform' => $field['transform'] ?? null,
                    'is_required' => $field['is_required'] ?? false,
                ]);
                $applied++;
            }
        }

        // Record the applied template version
        $appliedTemplates = $connection->applied_templates ?? [];
        $appliedTemplates[$template->target_model] = $template->version;
        $connection->update(['applied_templates' => $appliedTemplates]);

        return redirect()
            ->route('integration-hub.connections.show', $connection)
            ->with('success', "Template applied — {$applied} field(s) created or updated.");
    }

    /**
     * Get licensed features for the current instance.
     *
     * @return array<int, string>
     */
    private function licensedFeatures(): array
    {
        if (app()->environment('local', 'testing')) {
            return ['core', 'analytics', 'qbr', 'enablement', 'competency', 'integration_hub', 'mcp_server'];
        }

        $statusPath = config('version.agent_status_path');
        $file = rtrim($statusPath, '/').'/license-status.json';

        if (! file_exists($file)) {
            return ['core'];
        }

        $data = json_decode(file_get_contents($file), true);

        return $data['features'] ?? ['core'];
    }
}
