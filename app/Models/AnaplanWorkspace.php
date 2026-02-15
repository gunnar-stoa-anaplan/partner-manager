<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnaplanWorkspace extends Model
{
    use HasFactory;

    protected $fillable = [
        'anaplan_id',
        'name',
        'partner_id',
        'active',
        'workspace_type',
        'product_area',
        'size_allowance',
        'current_size',
        'last_synced_at',
    ];

    const PRODUCT_AREAS = [
        'Finance',
        'Supply Chain',
        'HR & Workforce',
        'Sales & Marketing',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'size_allowance' => 'integer',
            'current_size' => 'integer',
            'last_synced_at' => 'datetime',
        ];
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function models(): HasMany
    {
        return $this->hasMany(AnaplanModel::class);
    }

    public function isAssigned(): bool
    {
        return $this->partner_id !== null;
    }

    /**
     * Parse product area and workspace type from the workspace name.
     *
     * @return array{product_area: string|null, workspace_type: string|null}
     */
    public static function classifyWorkspace(string $name): array
    {
        $name = trim($name);

        // Finance (IFP = Integrated Financial Planning)
        if (preg_match('/^(Anaplan\s+)?(Finance|IFP)\s+Applications?\s*V2\s*-/i', $name)) {
            return ['product_area' => 'Finance', 'workspace_type' => 'Application V2'];
        }

        if (preg_match('/^(Anaplan\s+)?(Finance|IFP)\s+Applications?\s*-/i', $name)) {
            return ['product_area' => 'Finance', 'workspace_type' => 'Applications'];
        }

        if (preg_match('/^(Anaplan\s+)?(Finance|IFP)\s+Configurator\s*-/i', $name)) {
            return ['product_area' => 'Finance', 'workspace_type' => 'Configurator'];
        }

        if (preg_match('/^IFP\s*App(lications?)?\s*Conceptual\s*Demo/i', $name)) {
            return ['product_area' => 'Finance', 'workspace_type' => 'Conceptual Demo'];
        }

        // Supply Chain (SCM = Supply Chain Management)
        if (preg_match('/^(Anaplan\s+)?(Supply\s*Chain|SCM)\s+Applications?\s*V2\s*-/i', $name)) {
            return ['product_area' => 'Supply Chain', 'workspace_type' => 'Application V2'];
        }

        if (preg_match('/^(Anaplan\s+)?(Supply\s*Chain|SCM)\s+Applications?\s*-/i', $name)) {
            return ['product_area' => 'Supply Chain', 'workspace_type' => 'Applications'];
        }

        if (preg_match('/^(Anaplan\s+)?(Supply\s*Chain|SCM)\s+Configurator\s*-/i', $name)) {
            return ['product_area' => 'Supply Chain', 'workspace_type' => 'Configurator'];
        }

        // HR & Workforce (OWP = Operational Workforce Planning)
        if (preg_match('/^(Anaplan\s+)?(HR|Workforce|HR\s*&\s*Workforce|OWP)\s+Applications?\s*V2\s*-/i', $name)) {
            return ['product_area' => 'HR & Workforce', 'workspace_type' => 'Application V2'];
        }

        if (preg_match('/^(Anaplan\s+)?(HR|Workforce|HR\s*&\s*Workforce|OWP)\s+Applications?\s*-/i', $name)) {
            return ['product_area' => 'HR & Workforce', 'workspace_type' => 'Applications'];
        }

        if (preg_match('/^(Anaplan\s+)?(HR|Workforce|HR\s*&\s*Workforce|OWP)\s+Configurator\s*-/i', $name)) {
            return ['product_area' => 'HR & Workforce', 'workspace_type' => 'Configurator'];
        }

        // Sales & Marketing (SPM = Sales Performance Management, RPM = Revenue Performance Management)
        if (preg_match('/^(Anaplan\s+)?(Sales|Sales\s*&\s*Marketing|Marketing|SPM|RPM)\s+Applications?\s*V2\s*-/i', $name)) {
            return ['product_area' => 'Sales & Marketing', 'workspace_type' => 'Application V2'];
        }

        if (preg_match('/^(Anaplan\s+)?(Sales|Sales\s*&\s*Marketing|Marketing|SPM|RPM)\s+Applications?\s*-/i', $name)) {
            return ['product_area' => 'Sales & Marketing', 'workspace_type' => 'Applications'];
        }

        if (preg_match('/^(Anaplan\s+)?(Sales|Sales\s*&\s*Marketing|Marketing|SPM|RPM)\s+Configurator\s*-/i', $name)) {
            return ['product_area' => 'Sales & Marketing', 'workspace_type' => 'Configurator'];
        }

        return ['product_area' => null, 'workspace_type' => null];
    }

    /**
     * Auto-link unassigned workspaces to partners by matching partner names in workspace names.
     *
     * @return int Number of workspaces linked
     */
    public static function linkToPartners(): int
    {
        $partners = Partner::pluck('name', 'id');
        $unlinked = static::whereNull('partner_id')->get();
        $linked = 0;

        foreach ($unlinked as $workspace) {
            $name = $workspace->name;

            // Extract the portion after " - " (e.g. "IFP Applications - Accenture APAC" → "Accenture APAC")
            $partnerPart = str_contains($name, ' - ')
                ? trim(substr($name, strrpos($name, ' - ') + 3))
                : null;

            // Also handle "Conceptual Demo Classic PartnerName" (no dash)
            if (! $partnerPart && preg_match('/Conceptual\s+Demo\s+Classic\s+(.+)$/i', $name, $matches)) {
                $partnerPart = trim($matches[1]);
            }

            if (! $partnerPart) {
                continue;
            }

            // Match against known partners (case-insensitive, partner name must appear in the extracted portion)
            foreach ($partners as $partnerId => $partnerName) {
                if (stripos($partnerPart, $partnerName) !== false) {
                    $workspace->update(['partner_id' => $partnerId]);
                    $linked++;
                    break;
                }
            }
        }

        return $linked;
    }

    public function formattedSizeAllowance(): string
    {
        return $this->formatBytes($this->size_allowance);
    }

    public function formattedCurrentSize(): string
    {
        return $this->formatBytes($this->current_size);
    }

    public function usagePercentage(): ?float
    {
        if (! $this->size_allowance || $this->size_allowance <= 0) {
            return null;
        }

        return round(($this->current_size / $this->size_allowance) * 100, 1);
    }

    private function formatBytes(?int $bytes): string
    {
        if ($bytes === null || $bytes === 0) {
            return 'N/A';
        }

        $gb = $bytes / (1024 * 1024 * 1024);

        if ($gb >= 1) {
            return round($gb, 2).' GB';
        }

        $mb = $bytes / (1024 * 1024);

        return round($mb, 1).' MB';
    }
}
