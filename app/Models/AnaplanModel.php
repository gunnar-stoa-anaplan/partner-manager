<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnaplanModel extends Model
{
    use HasFactory;

    protected $table = 'anaplan_models';

    protected $fillable = [
        'anaplan_id',
        'name',
        'anaplan_workspace_id',
        'active_state',
        'model_url',
        'category',
        'detected_version',
        'product_area',
        'memory_usage',
        'created_in_anaplan_at',
        'last_modified_in_anaplan_at',
        'last_modified_by_guid',
        'last_saved_serial_number',
        'module_count',
        'list_count',
        'last_synced_at',
    ];

    const ACTIVE_STATES = [
        'PRODUCTION' => 'Production',
        'UNLOCKED' => 'Unlocked',
        'LOCKED' => 'Locked',
        'ARCHIVED' => 'Archived',
        'PRODUCTION_MAINTENANCE' => 'Production Maintenance',
    ];

    const ACTIVE_STATE_COLORS = [
        'PRODUCTION' => '#28a745',
        'UNLOCKED' => '#17a2b8',
        'LOCKED' => '#ffc107',
        'ARCHIVED' => '#868e96',
        'PRODUCTION_MAINTENANCE' => '#6f42c1',
    ];

    const VERSIONS = ['V2.0', 'V1.3', 'V1.2.1', 'V1.2', 'V1.1', 'V0', 'Base', 'Demo', 'Other'];

    const VERSION_RANKS = [
        'V2.0' => 7,
        'V1.3' => 6,
        'V1.2.1' => 5,
        'V1.2' => 4,
        'V1.1' => 3,
        'V0' => 2,
        'Base' => 1,
        'Demo' => 0,
        'Other' => -1,
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'memory_usage' => 'integer',
            'last_saved_serial_number' => 'integer',
            'module_count' => 'integer',
            'list_count' => 'integer',
            'created_in_anaplan_at' => 'datetime',
            'last_modified_in_anaplan_at' => 'datetime',
            'last_synced_at' => 'datetime',
        ];
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(AnaplanWorkspace::class, 'anaplan_workspace_id');
    }

    public function modules(): HasMany
    {
        return $this->hasMany(AnaplanModule::class);
    }

    public function lists(): HasMany
    {
        return $this->hasMany(AnaplanList::class);
    }

    public function isActive(): bool
    {
        return $this->active_state !== 'ARCHIVED';
    }

    /**
     * Detect the IFP version from the model name.
     */
    public static function detectVersion(string $name): string
    {
        $name = trim($name);

        if (preg_match('/^first$/i', $name) || preg_match('/^first[\s\-\(]/i', $name)) {
            return 'Other';
        }

        if (preg_match('/[Vv]2\.0/i', $name)) {
            return 'V2.0';
        }

        if (preg_match('/[Vv]?1\.3/', $name)) {
            return 'V1.3';
        }

        if (preg_match('/[Vv]?1\.2\.1/', $name)) {
            return 'V1.2.1';
        }

        if (preg_match('/[Vv]?1\.2(?!\.\d)/', $name)) {
            return 'V1.2';
        }

        if (preg_match('/[Vv]1\.1/', $name)) {
            return 'V1.1';
        }

        if (preg_match('/[Vv]0\b/', $name) || str_contains($name, 'Spoke V0') || str_contains($name, 'Hub V0')) {
            return 'V0';
        }

        if (str_contains($name, 'FPT') || str_contains($name, 'Conceptual Demo')) {
            return 'Demo';
        }

        if (str_starts_with($name, 'Base -') || str_starts_with($name, 'Base -')) {
            return 'Base';
        }

        return 'Other';
    }

    /**
     * Check if this model's version is outdated compared to the configured current version.
     */
    public function isOutdated(): bool
    {
        if (! $this->detected_version || ! $this->product_area) {
            return false;
        }

        $currentRank = self::VERSION_RANKS[$this->detected_version] ?? -1;
        if ($currentRank <= 0) {
            return false;
        }

        $config = AnaplanVersionConfig::where('product_area', $this->product_area)->first();
        if (! $config) {
            return false;
        }

        $targetRank = self::VERSION_RANKS[$config->current_version] ?? -1;

        return $currentRank < $targetRank;
    }

    /**
     * Get the version badge color based on how far behind the current version.
     */
    public function versionBadgeColor(): string
    {
        if (! $this->detected_version || ! $this->product_area) {
            return '#868e96';
        }

        $currentRank = self::VERSION_RANKS[$this->detected_version] ?? -1;
        if ($currentRank <= 0) {
            return '#868e96';
        }

        $config = AnaplanVersionConfig::where('product_area', $this->product_area)->first();
        if (! $config) {
            return '#17a2b8';
        }

        $targetRank = self::VERSION_RANKS[$config->current_version] ?? -1;
        $diff = $targetRank - $currentRank;

        if ($diff <= 0) {
            return '#28a745'; // Current
        }
        if ($diff === 1) {
            return '#ffc107'; // One behind
        }

        return '#dc3545'; // Two or more behind
    }

    public function formattedMemoryUsage(): string
    {
        if ($this->memory_usage === null || $this->memory_usage === 0) {
            return 'N/A';
        }

        $gb = $this->memory_usage / (1024 * 1024 * 1024);

        if ($gb >= 1) {
            return round($gb, 2).' GB';
        }

        $mb = $this->memory_usage / (1024 * 1024);

        return round($mb, 1).' MB';
    }
}
