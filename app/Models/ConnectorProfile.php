<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConnectorProfile extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'description',
        'icon',
        'connection_type',
        'direction',
        'default_config',
        'config_schema',
        'template_slugs',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'default_config' => 'array',
            'config_schema' => 'array',
            'template_slugs' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Filter template slugs to only those the current license supports.
     *
     * @param  array<int, string>  $licensedFeatures
     * @return array<int, string>
     */
    public function licensedTemplateSlugs(array $licensedFeatures): array
    {
        if (empty($this->template_slugs)) {
            return [];
        }

        return MappingTemplate::query()
            ->current()
            ->forFeatures($licensedFeatures)
            ->whereIn('slug', $this->template_slugs)
            ->pluck('slug')
            ->all();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
