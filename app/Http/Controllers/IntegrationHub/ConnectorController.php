<?php

namespace App\Http\Controllers\IntegrationHub;

use App\Http\Controllers\Controller;
use App\Http\Requests\IntegrationHub\SetupConnectorRequest;
use App\Models\ConnectorProfile;
use App\Models\FieldMapping;
use App\Models\IntegrationConnection;
use App\Models\MappingTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ConnectorController extends Controller
{
    public function catalog(): View
    {
        $profiles = ConnectorProfile::query()
            ->active()
            ->orderBy('sort_order')
            ->get();

        return view('integration-hub.connectors.catalog', compact('profiles'));
    }

    public function setup(ConnectorProfile $profile): View
    {
        $licensedFeatures = $this->licensedFeatures();

        $templates = MappingTemplate::query()
            ->current()
            ->forFeatures($licensedFeatures)
            ->whereIn('slug', $profile->template_slugs ?? [])
            ->get();

        return view('integration-hub.connectors.setup', compact('profile', 'templates'));
    }

    public function store(SetupConnectorRequest $request, ConnectorProfile $profile): RedirectResponse
    {
        $validated = $request->validated();

        $connection = IntegrationConnection::create([
            'name' => $validated['name'],
            'type' => $profile->connection_type,
            'direction' => $profile->direction,
            'config' => $validated['config'] ?? [],
            'status' => 'active',
        ]);

        // Auto-apply licensed templates
        $licensedFeatures = $this->licensedFeatures();
        $templates = MappingTemplate::query()
            ->current()
            ->forFeatures($licensedFeatures)
            ->whereIn('slug', $profile->template_slugs ?? [])
            ->get();

        $appliedTemplates = [];
        $totalMappings = 0;

        foreach ($templates as $template) {
            $sortOrder = $totalMappings;
            foreach ($template->fields as $field) {
                FieldMapping::create([
                    'connection_id' => $connection->id,
                    'name' => $field['description'] ?? ucwords(str_replace('_', ' ', $field['key'])),
                    'source_field' => $field['source_field'],
                    'target_model' => $template->target_model,
                    'target_field' => $field['key'],
                    'transform' => $field['transform'] ?? null,
                    'default_value' => $field['default_value'] ?? null,
                    'is_required' => $field['is_required'] ?? false,
                    'sort_order' => $sortOrder++,
                ]);
                $totalMappings++;
            }
            $appliedTemplates[$template->target_model] = $template->version;
        }

        $connection->update(['applied_templates' => $appliedTemplates]);

        $templateCount = $templates->count();

        return redirect()
            ->route('integration_hub.connections.show', $connection)
            ->with('success', "Connection created with {$totalMappings} field mapping(s) from {$templateCount} template(s).");
    }

    /**
     * @return array<int, string>
     */
    private function licensedFeatures(): array
    {
        if (app()->environment('local', 'testing')) {
            return ['core', 'partner_management', 'measurement', 'enablement', 'competency', 'integration_hub'];
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
