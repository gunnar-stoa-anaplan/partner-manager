<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SalesforceSyncService;
use App\Models\Partner;

class SyncSalesforcePartners extends Command
{
    protected $signature = 'salesforce:sync-partners';
    protected $description = 'Sync partner accounts from Salesforce';

    public function handle(SalesforceSyncService $sf)
    {
        $this->info('Starting sync of Salesforce partner accounts...');

        try {
            // Example SOQL query to get Account records marked as partners
            $records = $sf->query("SELECT Id, Name, Website, Industry FROM Account WHERE Type = 'Partner' AND Owner.Email = 'gunnar.stoa@gmail.com'");

            foreach ($records as $record) {
                Partner::updateOrCreate(
                    ['salesforce_id' => $record['Id']],
                    [
                        'name' => $record['Name'],
                        'website' => $record['Website'] ?? null,
                        'industry' => $record['Industry'] ?? null,
                        'user_id' => 11,
                    ]
                );
            }

            $this->info("Successfully synced " . count($records) . " partner accounts.");
        } catch (\Exception $e) {
            $this->error("Sync failed: " . $e->getMessage());
        }
    }
}