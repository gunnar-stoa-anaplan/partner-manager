<?php

namespace Database\Seeders;

use App\Models\ContentAsset;
use Illuminate\Database\Seeder;

class ContentAssetSeeder extends Seeder
{
    private const SOURCE_FILE = 'database/seeders/data/asset-index.json';

    /**
     * URL-bearing field name → asset type mapping.
     */
    private const SALES_FIELDS = [
        'pitch_deck' => 'pitch_deck',
        'icp' => 'icp',
        'datasheet' => 'datasheet',
        'faq' => 'faq',
        'positioning' => 'positioning',
        'seismic_courses' => 'seismic_course',
    ];

    private const PRE_SALES_FIELDS = [
        'consensus_demo_link' => 'consensus_demo',
        'feature_demo_link' => 'feature_demo',
    ];

    private const DELIVERY_FIELDS = [
        'technical_docs' => 'technical_doc',
        'academy_courses' => 'academy_course',
    ];

    private const NEEDS_TOKENS = ['Need', 'Need?', 'needed', 'TBD'];

    private const SKIP_TOKENS = ['n/a', 'N/A', 'na'];

    public function run(): void
    {
        $path = base_path(self::SOURCE_FILE);
        if (! is_file($path)) {
            $this->command?->error("Asset index not found at {$path}");

            return;
        }

        /** @var array{apps?: array<int, array<string, mixed>>, consensus_demos?: array<int, array<string, mixed>>} $data */
        $data = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);

        $appsCount = $this->seedApps($data['apps'] ?? []);
        $demosCount = $this->seedConsensusDemos($data['consensus_demos'] ?? []);

        $this->command?->info("Seeded {$appsCount} app content assets and {$demosCount} consensus demo assets.");
    }

    /**
     * @param  array<int, array<string, mixed>>  $apps
     */
    private function seedApps(array $apps): int
    {
        $count = 0;

        foreach ($apps as $app) {
            $appName = (string) ($app['name'] ?? '');
            $lob = $this->normalizeLob($app['lob'] ?? null);
            if ($appName === '' || $lob === null) {
                continue;
            }

            $base = [
                'application' => $appName,
                'lob' => $lob,
                'core_app' => $this->normalizeCoreApp($app['core'] ?? null),
                'launch_year' => $app['launch_year'] !== null ? (string) $app['launch_year'] : null,
            ];

            $personas = $app['personas'] ?? [];
            $sales = $personas['sales'] ?? [];

            $count += $this->seedPersona(
                $sales,
                self::SALES_FIELDS,
                'sales',
                $base,
                ownerKey: 'coe_lead',
                secondaryOwnerKey: 'solutions_marketing_lead',
                isPriority: (bool) ($sales['is_priority'] ?? false),
                isLaunching: (bool) ($sales['is_launching'] ?? false),
                personaNotes: isset($sales['notes']) ? (string) $sales['notes'] : null,
            );

            $count += $this->seedPersona(
                $personas['pre_sales'] ?? [],
                self::PRE_SALES_FIELDS,
                'pre_sales',
                $base,
                ownerKey: 'pre_sales_lead',
            );

            $count += $this->seedPersona(
                $personas['delivery'] ?? [],
                self::DELIVERY_FIELDS,
                'delivery',
                $base,
                ownerKey: 'coe_lead',
            );
        }

        return $count;
    }

    /**
     * @param  array<string, mixed>  $personaData
     * @param  array<string, string>  $fieldMap
     * @param  array<string, mixed>  $base
     */
    private function seedPersona(
        array $personaData,
        array $fieldMap,
        string $persona,
        array $base,
        string $ownerKey,
        ?string $secondaryOwnerKey = null,
        bool $isPriority = false,
        bool $isLaunching = false,
        ?string $personaNotes = null,
    ): int {
        $count = 0;
        $ownerName = $personaData[$ownerKey] ?? null;
        $secondaryOwnerName = $secondaryOwnerKey !== null ? ($personaData[$secondaryOwnerKey] ?? null) : null;

        foreach ($fieldMap as $field => $type) {
            $resolved = $this->resolveUrlField($personaData[$field] ?? null);
            if ($resolved['skip']) {
                continue;
            }

            $assetName = sprintf('%s — %s', $base['application'], ContentAsset::TYPES[$type] ?? $type);

            ContentAsset::updateOrCreate(
                [
                    'application' => $base['application'],
                    'persona' => $persona,
                    'type' => $type,
                ],
                array_filter([
                    'name' => $assetName,
                    'lob' => $base['lob'],
                    'core_app' => $base['core_app'],
                    'launch_year' => $base['launch_year'],
                    'url' => $resolved['url'],
                    'status' => $resolved['status'],
                    'owner_name' => $ownerName,
                    'secondary_owner_name' => $secondaryOwnerName,
                    'is_priority_need' => $isPriority,
                    'is_launching_in_progress' => $isLaunching,
                    'notes' => $this->buildNotes($personaNotes, $resolved['note']),
                    'metadata' => $resolved['metadata'],
                ], static fn ($v) => $v !== null && $v !== ''),
            );

            $count++;
        }

        return $count;
    }

    /**
     * @param  array<int, array<string, mixed>>  $demos
     */
    private function seedConsensusDemos(array $demos): int
    {
        $count = 0;

        foreach ($demos as $demo) {
            $name = (string) ($demo['name'] ?? '');
            if ($name === '') {
                continue;
            }

            $products = $demo['products'] ?? [];
            $application = is_array($products) && $products !== [] ? implode(', ', $products) : null;
            $topics = $demo['topics'] ?? [];

            ContentAsset::updateOrCreate(
                [
                    'application' => $application ?? $name,
                    'persona' => 'pre_sales',
                    'type' => 'consensus_demo',
                    'name' => $name,
                ],
                [
                    'lob' => 'Platform',
                    'url' => $demo['play_link'] ?? null,
                    'status' => ! empty($demo['play_link']) ? 'available' : 'not_available',
                    'notes' => is_array($topics) && $topics !== [] ? 'Topics: '.implode('; ', $topics) : null,
                    'metadata' => [
                        'description' => $demo['description'] ?? null,
                        'total_minutes' => $demo['total_minutes'] ?? null,
                        'products' => is_array($products) ? $products : [],
                        'topics' => is_array($topics) ? $topics : [],
                    ],
                ],
            );

            $count++;
        }

        return $count;
    }

    /**
     * @return array{skip: bool, url: ?string, status: string, note: ?string, metadata: ?array<string, mixed>}
     */
    private function resolveUrlField(mixed $raw): array
    {
        if ($raw === null) {
            return ['skip' => true, 'url' => null, 'status' => 'not_available', 'note' => null, 'metadata' => null];
        }

        $value = trim((string) $raw);
        if ($value === '' || in_array($value, self::SKIP_TOKENS, true)) {
            return ['skip' => true, 'url' => null, 'status' => 'not_available', 'note' => null, 'metadata' => null];
        }

        if (in_array($value, self::NEEDS_TOKENS, true)) {
            return ['skip' => false, 'url' => null, 'status' => 'not_available', 'note' => 'Marked as needed in source', 'metadata' => null];
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return ['skip' => false, 'url' => $value, 'status' => 'available', 'note' => null, 'metadata' => null];
        }

        return [
            'skip' => false,
            'url' => null,
            'status' => 'partial',
            'note' => 'Source reference: '.$value,
            'metadata' => ['source_reference' => $value],
        ];
    }

    private function buildNotes(?string $base, ?string $extra): ?string
    {
        $combined = trim(implode("\n", array_filter([$base, $extra])));

        return $combined === '' ? null : $combined;
    }

    private function normalizeLob(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        return in_array($value, ContentAsset::LOBS, true) ? $value : null;
    }

    private function normalizeCoreApp(mixed $value): ?string
    {
        return match (strtoupper((string) $value)) {
            'Y', 'YES', 'TRUE', '1' => 'yes',
            'N', 'NO', 'FALSE', '0' => 'no',
            'N/A', 'NA' => 'na',
            default => null,
        };
    }
}
