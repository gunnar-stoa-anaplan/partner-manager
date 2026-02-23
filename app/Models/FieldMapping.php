<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FieldMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'connection_id',
        'name',
        'source_field',
        'target_model',
        'target_field',
        'transform',
        'default_value',
        'is_required',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function connection(): BelongsTo
    {
        return $this->belongsTo(IntegrationConnection::class, 'connection_id');
    }

    /**
     * Apply the mapping transform to a sample value.
     *
     * @return array{input: string, output: string, transform: string|null}
     */
    public function previewTransform(string $sampleValue = 'Sample Value'): array
    {
        $output = match ($this->transform) {
            'lowercase' => strtolower($sampleValue),
            'uppercase' => strtoupper($sampleValue),
            'trim' => trim($sampleValue),
            'date_iso' => date('Y-m-d', strtotime($sampleValue)) ?: $sampleValue,
            'integer' => (string) (int) $sampleValue,
            'float' => (string) (float) $sampleValue,
            'boolean' => in_array(strtolower($sampleValue), ['true', '1', 'yes']) ? 'true' : 'false',
            default => $sampleValue,
        };

        return [
            'input' => $sampleValue,
            'output' => $output,
            'transform' => $this->transform,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function availableTargetModels(): array
    {
        return [
            'Partner' => 'Partner',
            'Contact' => 'Contact',
            'Opportunity' => 'Opportunity',
            'Resource' => 'Resource',
            'Project' => 'Project',
            'Lead' => 'Lead',
            'Account' => 'Account',
            'DealRegistration' => 'Deal Registration',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function availableTransforms(): array
    {
        return [
            '' => 'None (pass-through)',
            'lowercase' => 'Lowercase',
            'uppercase' => 'Uppercase',
            'trim' => 'Trim whitespace',
            'date_iso' => 'Date → ISO format',
            'integer' => 'Cast to integer',
            'float' => 'Cast to float',
            'boolean' => 'Cast to boolean',
        ];
    }
}
