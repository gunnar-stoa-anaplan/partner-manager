<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Http;

class IntegrationConnection extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'direction',
        'config',
        'auth_config',
        'status',
        'last_tested_at',
        'last_sync_at',
        'applied_templates',
    ];

    protected function casts(): array
    {
        return [
            'config' => 'array',
            'auth_config' => 'encrypted:array',
            'last_tested_at' => 'datetime',
            'last_sync_at' => 'datetime',
            'applied_templates' => 'array',
        ];
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(IntegrationJob::class, 'connection_id');
    }

    public function mappings(): HasMany
    {
        return $this->hasMany(FieldMapping::class, 'connection_id');
    }

    /**
     * @return array{success: bool, message: string, latency_ms: int|null}
     */
    public function testConnection(): array
    {
        $start = microtime(true);

        try {
            $result = match ($this->type) {
                'rest_api' => $this->testRestApi(),
                'salesforce' => $this->testSalesforce(),
                'csv_upload' => ['success' => true, 'message' => 'CSV upload connections are always available.'],
                'webhook' => ['success' => true, 'message' => 'Webhook endpoint is configured.'],
                'database' => $this->testDatabase(),
                default => ['success' => false, 'message' => "Unknown connection type: {$this->type}"],
            };
        } catch (\Throwable $e) {
            $result = ['success' => false, 'message' => $e->getMessage()];
        }

        $latency = (int) ((microtime(true) - $start) * 1000);

        $this->update(['last_tested_at' => now()]);

        return array_merge($result, ['latency_ms' => $latency]);
    }

    /**
     * @return array{success: bool, message: string}
     */
    private function testRestApi(): array
    {
        $url = $this->config['base_url'] ?? null;

        if (! $url) {
            return ['success' => false, 'message' => 'No base_url configured.'];
        }

        $response = Http::timeout(10)->get($url);

        return [
            'success' => $response->successful(),
            'message' => $response->successful() ? 'Connected successfully.' : "HTTP {$response->status()}",
        ];
    }

    /**
     * @return array{success: bool, message: string}
     */
    private function testSalesforce(): array
    {
        $instanceUrl = $this->config['instance_url'] ?? null;

        if (! $instanceUrl) {
            return ['success' => false, 'message' => 'No Salesforce instance URL configured.'];
        }

        return ['success' => true, 'message' => 'Salesforce credentials configured.'];
    }

    /**
     * @return array{success: bool, message: string}
     */
    private function testDatabase(): array
    {
        return ['success' => false, 'message' => 'Database connection testing not yet implemented.'];
    }

    public static function availableTypes(): array
    {
        return [
            'csv_upload' => 'CSV File Upload',
            'rest_api' => 'REST API',
            'salesforce' => 'Salesforce',
            'webhook' => 'Webhook',
            'database' => 'External Database',
        ];
    }
}
