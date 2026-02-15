<?php

namespace App\Services;

use App\Models\AnaplanList;
use App\Models\AnaplanModel;
use App\Models\AnaplanModule;
use App\Models\AnaplanVersionConfig;
use App\Models\AnaplanWorkspace;
use App\Models\NotificationLog;
use App\Models\NotificationTemplate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AnaplanApiService
{
    private string $authUrl;

    private string $apiUrl;

    private string $username;

    private string $password;

    private string $configKey;

    public function __construct(string $configKey = 'anaplan')
    {
        $this->configKey = $configKey;
        $this->authUrl = config("services.{$configKey}.auth_url");
        $this->apiUrl = config("services.{$configKey}.api_url");
        $this->username = config("services.{$configKey}.username") ?? '';
        $this->password = config("services.{$configKey}.password") ?? '';
    }

    public function isConfigured(): bool
    {
        return $this->username !== '' && $this->password !== '';
    }

    /**
     * Authenticate and return AnaplanAuthToken.
     * Cached for 30 minutes (token expires after 35 min).
     */
    public function authenticate(): ?string
    {
        $cacheKey = "anaplan_auth_token_{$this->configKey}";

        return Cache::remember($cacheKey, 1800, function () {
            $credentials = base64_encode($this->username.':'.$this->password);

            $response = Http::withHeaders([
                'Authorization' => 'Basic '.$credentials,
                'Content-Type' => 'application/json',
            ])->post($this->authUrl);

            if ($response->successful()) {
                return $response->json('tokenInfo.tokenValue');
            }

            Log::error('Anaplan authentication failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        });
    }

    /**
     * @return array<int, array>|null
     */
    public function fetchWorkspaces(): ?array
    {
        $token = $this->authenticate();
        if (! $token) {
            return null;
        }

        $response = Http::withHeaders([
            'Authorization' => 'AnaplanAuthToken '.$token,
            'Content-Type' => 'application/json',
        ])->get($this->apiUrl.'/workspaces', ['tenantDetails' => 'true']);

        if ($response->successful()) {
            return $response->json('workspaces') ?? [];
        }

        Log::error('Anaplan fetch workspaces failed', ['status' => $response->status()]);

        return null;
    }

    /**
     * Fetch all models across all workspaces in a single API call.
     * Returns memoryUsage, currentWorkspaceId, and all model details.
     *
     * @return array<int, array>|null
     */
    public function fetchAllModels(): ?array
    {
        $token = $this->authenticate();
        if (! $token) {
            return null;
        }

        $response = Http::withHeaders([
            'Authorization' => 'AnaplanAuthToken '.$token,
            'Content-Type' => 'application/json',
        ])->timeout(120)->get($this->apiUrl.'/models', ['modelDetails' => 'true']);

        if ($response->successful()) {
            return $response->json('models') ?? [];
        }

        Log::error('Anaplan fetch all models failed', ['status' => $response->status()]);

        return null;
    }

    /**
     * @return array<int, array>|null
     */
    public function fetchModels(string $workspaceId): ?array
    {
        $token = $this->authenticate();
        if (! $token) {
            return null;
        }

        $response = Http::withHeaders([
            'Authorization' => 'AnaplanAuthToken '.$token,
            'Content-Type' => 'application/json',
        ])->get($this->apiUrl.'/workspaces/'.$workspaceId.'/models', ['modelDetails' => 'true']);

        if ($response->successful()) {
            return $response->json('models') ?? [];
        }

        Log::error('Anaplan fetch models failed', ['workspace_id' => $workspaceId, 'status' => $response->status()]);

        return null;
    }

    /**
     * @return array<int, array>|null
     */
    public function fetchModules(string $workspaceId, string $modelId): ?array
    {
        $token = $this->authenticate();
        if (! $token) {
            return null;
        }

        $response = Http::withHeaders([
            'Authorization' => 'AnaplanAuthToken '.$token,
            'Content-Type' => 'application/json',
        ])->get($this->apiUrl.'/workspaces/'.$workspaceId.'/models/'.$modelId.'/modules');

        if ($response->successful()) {
            return $response->json('modules') ?? [];
        }

        Log::error('Anaplan fetch modules failed', ['model_id' => $modelId, 'status' => $response->status()]);

        return null;
    }

    /**
     * @return array<int, array>|null
     */
    public function fetchLists(string $workspaceId, string $modelId): ?array
    {
        $token = $this->authenticate();
        if (! $token) {
            return null;
        }

        $response = Http::withHeaders([
            'Authorization' => 'AnaplanAuthToken '.$token,
            'Content-Type' => 'application/json',
        ])->get($this->apiUrl.'/workspaces/'.$workspaceId.'/models/'.$modelId.'/lists');

        if ($response->successful()) {
            return $response->json('lists') ?? [];
        }

        Log::error('Anaplan fetch lists failed', ['model_id' => $modelId, 'status' => $response->status()]);

        return null;
    }

    /**
     * Full sync: workspaces + all models (with memoryUsage via single API call).
     * Optionally syncs modules/lists per model.
     *
     * @return array{workspaces_synced: int, models_synced: int, models_archived: int, workspaces_linked: int, errors: array<string>}
     */
    public function syncAll(bool $includeModules = true): array
    {
        $result = ['workspaces_synced' => 0, 'models_synced' => 0, 'models_archived' => 0, 'workspaces_linked' => 0, 'errors' => []];

        // Step 1: Sync workspaces
        $apiWorkspaces = $this->fetchWorkspaces();
        if ($apiWorkspaces === null) {
            $result['errors'][] = 'Failed to fetch workspaces from Anaplan API.';

            return $result;
        }

        $workspaceMap = []; // anaplan_id => local workspace model
        $syncedWorkspaceAnaplanIds = [];

        foreach ($apiWorkspaces as $ws) {
            $classification = AnaplanWorkspace::classifyWorkspace($ws['name']);

            $workspace = AnaplanWorkspace::updateOrCreate(
                ['anaplan_id' => $ws['id']],
                [
                    'name' => $ws['name'],
                    'active' => $ws['active'] ?? true,
                    'workspace_type' => $classification['workspace_type'],
                    'product_area' => $classification['product_area'],
                    'size_allowance' => $ws['sizeAllowance'] ?? null,
                    'current_size' => $ws['currentSize'] ?? null,
                    'last_synced_at' => now(),
                ]
            );
            $workspaceMap[$ws['id']] = $workspace;
            $syncedWorkspaceAnaplanIds[] = $ws['id'];
            $result['workspaces_synced']++;
        }

        // Step 1b: Auto-link unassigned workspaces to partners by name
        $result['workspaces_linked'] = AnaplanWorkspace::linkToPartners();

        // Step 2: Sync all models via single API call
        $apiModels = $this->fetchAllModels();
        if ($apiModels === null) {
            $result['errors'][] = 'Failed to fetch models from Anaplan API.';

            return $result;
        }

        $syncedModelAnaplanIds = [];

        foreach ($apiModels as $m) {
            $activeState = $m['activeState'] ?? 'UNLOCKED';
            $wsAnaplanId = $m['currentWorkspaceId'] ?? null;

            // Find the local workspace for this model
            $workspace = $wsAnaplanId ? ($workspaceMap[$wsAnaplanId] ?? null) : null;
            if (! $workspace) {
                $workspace = AnaplanWorkspace::where('anaplan_id', $wsAnaplanId)->first();
            }

            if (! $workspace) {
                continue;
            }

            $detectedVersion = AnaplanModel::detectVersion($m['name']);
            $productArea = $workspace->product_area;

            $model = AnaplanModel::updateOrCreate(
                ['anaplan_id' => $m['id']],
                [
                    'name' => $m['name'],
                    'anaplan_workspace_id' => $workspace->id,
                    'active_state' => $activeState,
                    'model_url' => $m['modelUrl'] ?? null,
                    'category' => ! empty($m['categoryValues']) ? ($m['categoryValues'][0]['name'] ?? null) : null,
                    'detected_version' => $detectedVersion,
                    'product_area' => $productArea,
                    'memory_usage' => $m['memoryUsage'] ?? null,
                    'created_in_anaplan_at' => isset($m['isoCreationDate']) ? $m['isoCreationDate'] : null,
                    'last_modified_in_anaplan_at' => isset($m['lastModified']) ? $m['lastModified'] : null,
                    'last_modified_by_guid' => $m['lastModifiedByUserGuid'] ?? null,
                    'last_saved_serial_number' => $m['lastSavedSerialNumber'] ?? null,
                    'last_synced_at' => now(),
                ]
            );
            $syncedModelAnaplanIds[] = $m['id'];
            $result['models_synced']++;

            // Optionally sync modules and lists for active models
            if ($includeModules && $activeState !== 'ARCHIVED') {
                $this->syncModelModules($workspace->anaplan_id, $m['id'], $model);
                $this->syncModelLists($workspace->anaplan_id, $m['id'], $model);
            }
        }

        // Mark models not returned as ARCHIVED
        $result['models_archived'] = AnaplanModel::whereNotIn('anaplan_id', $syncedModelAnaplanIds)
            ->where('active_state', '!=', 'ARCHIVED')
            ->update(['active_state' => 'ARCHIVED']);

        // Mark workspaces no longer in API as inactive
        AnaplanWorkspace::whereNotIn('anaplan_id', $syncedWorkspaceAnaplanIds)
            ->update(['active' => false]);

        return $result;
    }

    /**
     * Check which partners have outdated versions and return alert data.
     *
     * @return array<int, array{partner_id: int, partner_name: string, product_area: string, latest_version: string, current_version: string}>
     */
    public function checkVersionAlerts(): array
    {
        $alerts = [];
        $configs = AnaplanVersionConfig::all()->keyBy('product_area');

        if ($configs->isEmpty()) {
            return $alerts;
        }

        $partners = AnaplanWorkspace::whereNotNull('partner_id')
            ->with(['partner', 'models'])
            ->get()
            ->groupBy('partner_id');

        foreach ($partners as $partnerId => $workspaces) {
            $partner = $workspaces->first()->partner;
            if (! $partner) {
                continue;
            }

            $allModels = $workspaces->flatMap(fn ($ws) => $ws->models);

            foreach ($configs as $productArea => $config) {
                $targetRank = AnaplanModel::VERSION_RANKS[$config->current_version] ?? -1;

                $partnerModels = $allModels->where('product_area', $productArea)
                    ->filter(fn ($m) => isset(AnaplanModel::VERSION_RANKS[$m->detected_version])
                        && AnaplanModel::VERSION_RANKS[$m->detected_version] > 0);

                if ($partnerModels->isEmpty()) {
                    continue;
                }

                $latestPartnerRank = $partnerModels->max(fn ($m) => AnaplanModel::VERSION_RANKS[$m->detected_version] ?? 0);
                $latestPartnerVersion = collect(AnaplanModel::VERSION_RANKS)
                    ->filter(fn ($rank) => $rank === $latestPartnerRank)
                    ->keys()
                    ->first();

                if ($latestPartnerRank < $targetRank) {
                    $alerts[] = [
                        'partner_id' => $partnerId,
                        'partner_name' => $partner->name,
                        'product_area' => $productArea,
                        'latest_version' => $latestPartnerVersion,
                        'current_version' => $config->current_version,
                        'psm_user_id' => $partner->user_id,
                    ];
                }
            }
        }

        return $alerts;
    }

    /**
     * Send version outdated notifications for the given alerts.
     *
     * @param  array<int, array>  $alerts
     */
    public function sendVersionAlerts(array $alerts): int
    {
        $template = NotificationTemplate::where('event_type', 'anaplan_version_outdated')
            ->active()
            ->first();

        if (! $template) {
            Log::warning('No active notification template for anaplan_version_outdated');

            return 0;
        }

        $sentCount = 0;

        foreach ($alerts as $alert) {
            // Check for recent duplicate (within 30 days) for same partner
            $recentlyNotified = NotificationLog::where('event_type', 'anaplan_version_outdated')
                ->where('recipient_id', $alert['psm_user_id'])
                ->where('created_at', '>=', now()->subDays(30))
                ->where('related_type', 'App\\Models\\Partner')
                ->where('related_id', $alert['partner_id'])
                ->exists();

            if ($recentlyNotified) {
                continue;
            }

            NotificationLog::create([
                'notification_template_id' => $template->id,
                'event_type' => 'anaplan_version_outdated',
                'recipient_type' => 'user',
                'recipient_id' => $alert['psm_user_id'],
                'channel' => $template->channel,
                'status' => 'queued',
                'related_type' => 'App\\Models\\Partner',
                'related_id' => $alert['partner_id'],
                'subject' => "Version outdated: {$alert['partner_name']} ({$alert['product_area']})",
                'body' => "Partner {$alert['partner_name']} is on {$alert['latest_version']} for {$alert['product_area']}, current version is {$alert['current_version']}.",
            ]);
            $sentCount++;
        }

        return $sentCount;
    }

    /**
     * Fetch line items for a module. Uses direct model path for broader access.
     *
     * @return array<int, array>|null
     */
    public function fetchLineItems(string $modelId, string $moduleId, bool $includeAll = false): ?array
    {
        $token = $this->authenticate();
        if (! $token) {
            return null;
        }

        $url = $this->apiUrl.'/models/'.$modelId.'/modules/'.$moduleId.'/lineItems';
        $params = $includeAll ? ['includeAll' => 'true'] : [];

        $response = Http::withHeaders([
            'Authorization' => 'AnaplanAuthToken '.$token,
            'Content-Type' => 'application/json',
        ])->get($url, $params);

        if ($response->successful()) {
            return $response->json('items') ?? [];
        }

        Log::error('Anaplan fetch line items failed', ['model_id' => $modelId, 'module_id' => $moduleId, 'status' => $response->status()]);

        return null;
    }

    /**
     * Fetch items from a list.
     *
     * @return array<int, array>|null
     */
    public function fetchListItems(string $modelId, string $listId): ?array
    {
        $token = $this->authenticate();
        if (! $token) {
            return null;
        }

        $response = Http::withHeaders([
            'Authorization' => 'AnaplanAuthToken '.$token,
            'Content-Type' => 'application/json',
        ])->get($this->apiUrl.'/models/'.$modelId.'/lists/'.$listId.'/items');

        if ($response->successful()) {
            return $response->json('listItems') ?? [];
        }

        Log::error('Anaplan fetch list items failed', ['model_id' => $modelId, 'list_id' => $listId, 'status' => $response->status()]);

        return null;
    }

    /**
     * Fetch available exports for a model.
     *
     * @return array<int, array>|null
     */
    public function fetchExports(string $modelId): ?array
    {
        $token = $this->authenticate();
        if (! $token) {
            return null;
        }

        $response = Http::withHeaders([
            'Authorization' => 'AnaplanAuthToken '.$token,
            'Content-Type' => 'application/json',
        ])->get($this->apiUrl.'/models/'.$modelId.'/exports');

        if ($response->successful()) {
            return $response->json('exports') ?? [];
        }

        Log::error('Anaplan fetch exports failed', ['model_id' => $modelId, 'status' => $response->status()]);

        return null;
    }

    /**
     * Run an export and return the task ID.
     */
    public function runExport(string $modelId, string $exportId): ?string
    {
        $token = $this->authenticate();
        if (! $token) {
            return null;
        }

        $response = Http::withHeaders([
            'Authorization' => 'AnaplanAuthToken '.$token,
            'Content-Type' => 'application/json',
        ])->post($this->apiUrl.'/models/'.$modelId.'/exports/'.$exportId.'/tasks', [
            'localeName' => 'en_US',
        ]);

        if ($response->successful()) {
            return $response->json('task.taskId') ?? $response->json('task.id');
        }

        Log::error('Anaplan run export failed', ['model_id' => $modelId, 'export_id' => $exportId, 'status' => $response->status()]);

        return null;
    }

    /**
     * Check the status of an export task.
     *
     * @return array{taskState: string, progress?: float}|null
     */
    public function getExportTaskStatus(string $modelId, string $exportId, string $taskId): ?array
    {
        $token = $this->authenticate();
        if (! $token) {
            return null;
        }

        $response = Http::withHeaders([
            'Authorization' => 'AnaplanAuthToken '.$token,
            'Content-Type' => 'application/json',
        ])->get($this->apiUrl.'/models/'.$modelId.'/exports/'.$exportId.'/tasks/'.$taskId);

        if ($response->successful()) {
            return $response->json('task');
        }

        Log::error('Anaplan export task status failed', ['task_id' => $taskId, 'status' => $response->status()]);

        return null;
    }

    /**
     * Read export file chunk data (CSV content).
     */
    public function readExportFile(string $modelId, string $fileId, int $chunkIndex = 0): ?string
    {
        $token = $this->authenticate();
        if (! $token) {
            return null;
        }

        $response = Http::withHeaders([
            'Authorization' => 'AnaplanAuthToken '.$token,
            'Accept' => 'application/octet-stream',
        ])->get($this->apiUrl.'/models/'.$modelId.'/files/'.$fileId.'/chunks/'.$chunkIndex);

        if ($response->successful()) {
            return $response->body();
        }

        Log::error('Anaplan read export file failed', ['file_id' => $fileId, 'chunk' => $chunkIndex, 'status' => $response->status()]);

        return null;
    }

    public function clearTokenCache(): void
    {
        Cache::forget("anaplan_auth_token_{$this->configKey}");
    }

    private function syncModelModules(string $workspaceAnaplanId, string $modelAnaplanId, AnaplanModel $model): void
    {
        $apiModules = $this->fetchModules($workspaceAnaplanId, $modelAnaplanId);
        if ($apiModules === null) {
            return;
        }

        $syncedIds = [];
        foreach ($apiModules as $mod) {
            AnaplanModule::updateOrCreate(
                ['anaplan_id' => $mod['id'], 'anaplan_model_id' => $model->id],
                ['name' => $mod['name']]
            );
            $syncedIds[] = $mod['id'];
        }

        // Remove modules no longer in API
        $model->modules()->whereNotIn('anaplan_id', $syncedIds)->delete();
        $model->update(['module_count' => count($apiModules)]);
    }

    private function syncModelLists(string $workspaceAnaplanId, string $modelAnaplanId, AnaplanModel $model): void
    {
        $apiLists = $this->fetchLists($workspaceAnaplanId, $modelAnaplanId);
        if ($apiLists === null) {
            return;
        }

        $syncedIds = [];
        foreach ($apiLists as $list) {
            AnaplanList::updateOrCreate(
                ['anaplan_id' => $list['id'], 'anaplan_model_id' => $model->id],
                [
                    'name' => $list['name'],
                    'item_count' => $list['permittedItems'] ?? null,
                    'has_selective_access' => $list['hasSelectiveAccess'] ?? false,
                ]
            );
            $syncedIds[] = $list['id'];
        }

        // Remove lists no longer in API
        $model->lists()->whereNotIn('anaplan_id', $syncedIds)->delete();
        $model->update(['list_count' => count($apiLists)]);
    }
}
