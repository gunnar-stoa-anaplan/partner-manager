<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Seeder;
use Laravel\Pennant\Feature;

class ModuleSeeder extends Seeder
{
    /**
     * Seed modules from config/modules.php into the modules table.
     *
     * In development (default), new modules are activated immediately.
     * In production, pass --dark to ship features inactive so the admin
     * can enable them when ready:
     *
     *   php artisan db:seed --class=ModuleSeeder          # activate new modules
     *   php artisan db:seed --class=ModuleSeeder -- --dark # ship dark
     */
    public function run(): void
    {
        $dark = in_array('--dark', $_SERVER['argv'] ?? [], true);
        $modules = config('modules', []);
        $existingSlugs = Module::pluck('slug')->flip()->all();
        $sortOrder = 0;
        $created = 0;
        $updated = 0;

        foreach ($modules as $slug => $definition) {
            $isNew = ! isset($existingSlugs[$slug]);

            Module::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $definition['name'],
                    'description' => $definition['description'] ?? null,
                    'icon' => $definition['icon'],
                    'group' => $definition['group'],
                    'is_active' => $isNew ? ! $dark : Module::where('slug', $slug)->value('is_active'),
                    'sort_order' => $sortOrder++,
                    'training_mode' => $definition['training_mode'] ?? 'none',
                    'definition' => [
                        'sections' => $definition['sections'] ?? [],
                        'extends' => $definition['extends'] ?? [],
                    ],
                ],
            );

            if ($isNew) {
                $created++;

                if (! $dark) {
                    Feature::for(null)->activate($slug);
                    $this->command->info("  + {$definition['name']} — created & activated");
                } else {
                    $this->command->info("  + {$definition['name']} — created (dark, admin must activate)");
                }
            } else {
                $updated++;
            }
        }

        $this->command->info("Modules: {$created} created, {$updated} updated (total {$sortOrder}).");
    }
}
