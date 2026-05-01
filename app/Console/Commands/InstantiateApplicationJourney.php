<?php

namespace App\Console\Commands;

use App\Models\Journey;
use App\Services\JourneyAssetInitializer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class InstantiateApplicationJourney extends Command
{
    protected $signature = 'journey:instantiate
        {product : Product key (e.g., ifp, fcr, mfp)}
        {--name= : Full product name (default: uppercased key)}
        {--short= : Product short name (default: uppercased key)}
        {--adjacent-product= : Adjacent product display name for Stage 5}
        {--adjacent-journey-id= : Adjacent journey id token for Stage 5}
        {--terminal-outcome= : Stage 5 terminal outcome string}
        {--personas=delivery,sales,pre-sales : CSV of personas to create}
        {--status=planned : Initial Journey status (planned, pilot, active)}';

    protected $description = 'Instantiate per-persona Journey records for an application + seed JourneyAsset rows from templates.';

    public function handle(JourneyAssetInitializer $initializer): int
    {
        $productKey = strtolower((string) $this->argument('product'));
        $productName = (string) ($this->option('name') ?: strtoupper($productKey));
        $productShort = (string) ($this->option('short') ?: strtoupper($productKey));
        $adjacentProduct = (string) ($this->option('adjacent-product') ?: '');
        $adjacentJourneyId = (string) ($this->option('adjacent-journey-id') ?: '');
        $terminalOutcome = (string) ($this->option('terminal-outcome') ?: '');
        $personas = array_map('trim', explode(',', (string) $this->option('personas')));
        $status = (string) $this->option('status');

        $context = [
            'product_id' => $productKey,
            'product_name' => $productName,
            'product_short' => $productShort,
            'adjacent_product' => $adjacentProduct,
            'adjacent_journey_id' => $adjacentJourneyId,
            'terminal_outcome' => $terminalOutcome,
        ];

        $this->info("Instantiating {$productName} ({$productKey}) for personas: ".implode(', ', $personas));

        DB::transaction(function () use ($personas, $productKey, $productName, $status, $context, $initializer) {
            foreach ($personas as $persona) {
                $personaSlug = strtolower(str_replace('_', '-', $persona));
                $journeyKey = "{$productKey}-{$personaSlug}";
                $personaLabel = ucwords(str_replace('-', ' ', $personaSlug));
                $journeyName = "{$productName} — {$personaLabel}";

                $journey = Journey::query()->updateOrCreate(
                    ['journey_key' => $journeyKey],
                    [
                        'name' => $journeyName,
                        'product_area' => $productKey,
                        'persona' => $personaSlug,
                        'status' => $status,
                    ]
                );

                $created = $initializer->initialize($journey, $context);
                $this->line(" • {$journeyName}  →  {$created} new asset(s) seeded");
            }
        });

        $this->info('Done.');

        return self::SUCCESS;
    }
}
