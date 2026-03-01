<?php

namespace App\Models;

use App\Enums\TrainingMode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Module extends Model
{
    /** @use HasFactory<\Database\Factories\ModuleFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'slug',
        'name',
        'description',
        'icon',
        'group',
        'is_active',
        'sort_order',
        'training_mode',
        'definition',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'training_mode' => TrainingMode::class,
            'definition' => 'array',
        ];
    }

    public function activationPath(): HasOne
    {
        return $this->hasOne(ActivationPath::class);
    }

    /**
     * Convert to the array shape that ModuleRegistry::register() expects.
     *
     * @return array{name: string, description: string, icon: string, group: string, enabled: bool, training_mode: string, sections: array, extends?: array}
     */
    public function toRegistryArray(): array
    {
        $definition = $this->definition ?? [];

        return [
            'name' => $this->name,
            'description' => $this->description ?? '',
            'icon' => $this->icon,
            'group' => $this->group,
            'enabled' => $this->is_active,
            'training_mode' => $this->training_mode->value,
            'sections' => $definition['sections'] ?? [],
            'extends' => $definition['extends'] ?? [],
        ];
    }
}
