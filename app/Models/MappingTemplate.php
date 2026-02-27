<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class MappingTemplate extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'description',
        'target_model',
        'version',
        'required_feature',
        'is_current',
        'fields',
        'changelog',
    ];

    protected function casts(): array
    {
        return [
            'fields' => 'array',
            'is_current' => 'boolean',
        ];
    }

    /**
     * Scope to only current (latest) template versions.
     */
    public function scopeCurrent(Builder $query): Builder
    {
        return $query->where('is_current', true);
    }

    /**
     * Scope to templates available for the given license features.
     *
     * @param  array<int, string>  $features
     */
    public function scopeForFeatures(Builder $query, array $features): Builder
    {
        return $query->where(function (Builder $q) use ($features) {
            $q->whereNull('required_feature')
                ->orWhereIn('required_feature', $features);
        });
    }

    /**
     * Compare this template's fields against a connection's existing mappings.
     *
     * @return array<int, array{status: string, key: string, template: array|null, mapping: FieldMapping|null}>
     */
    public function diffAgainst(Collection $existingMappings): array
    {
        $results = [];
        $matchedMappingIds = [];

        foreach ($this->fields as $field) {
            $existing = $existingMappings->first(
                fn (FieldMapping $m) => $m->target_field === $field['key']
            );

            if ($existing) {
                $matchedMappingIds[] = $existing->id;

                $transformChanged = ($existing->transform ?: null) !== ($field['transform'] ?? null);
                $requiredChanged = (bool) $existing->is_required !== (bool) ($field['is_required'] ?? false);

                if ($transformChanged || $requiredChanged) {
                    $results[] = [
                        'status' => 'changed',
                        'key' => $field['key'],
                        'template' => $field,
                        'mapping' => $existing,
                    ];
                } else {
                    $results[] = [
                        'status' => 'matched',
                        'key' => $field['key'],
                        'template' => $field,
                        'mapping' => $existing,
                    ];
                }
            } else {
                $results[] = [
                    'status' => 'new',
                    'key' => $field['key'],
                    'template' => $field,
                    'mapping' => null,
                ];
            }
        }

        // User-added mappings not in the template
        foreach ($existingMappings as $mapping) {
            if (! in_array($mapping->id, $matchedMappingIds)) {
                $results[] = [
                    'status' => 'custom',
                    'key' => $mapping->target_field,
                    'template' => null,
                    'mapping' => $mapping,
                ];
            }
        }

        return $results;
    }
}
