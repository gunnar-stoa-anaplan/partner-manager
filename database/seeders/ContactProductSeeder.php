<?php

namespace Database\Seeders;

use App\Models\Partner;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ContactProductSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Assigning product training to resource contacts...');

        $products = Product::all();
        if ($products->isEmpty()) {
            $this->command->warn('No products found. Run ProductSeeder first.');

            return;
        }

        $partners = Partner::with(['contacts' => fn ($q) => $q->where('contact_type', 'resource')])->get();
        $totalAssignments = 0;

        foreach ($partners as $partner) {
            $resources = $partner->contacts;
            if ($resources->isEmpty()) {
                continue;
            }

            foreach ($resources as $contact) {
                $productCount = fake()->numberBetween(2, 5);
                $selectedProducts = $products->random(min($productCount, $products->count()));

                $syncData = [];
                foreach ($selectedProducts as $product) {
                    $status = fake()->randomElement(
                        array_merge(
                            array_fill(0, 12, 'trained'),
                            array_fill(0, 5, 'in_progress'),
                            array_fill(0, 3, 'workshop'),
                        )
                    );

                    $syncData[$product->id] = [
                        'status' => $status,
                        'trained_at' => $status === 'trained'
                            ? fake()->dateTimeBetween('-18 months', 'now')
                            : null,
                    ];
                    $totalAssignments++;
                }
                $contact->products()->syncWithoutDetaching($syncData);
            }
        }

        $this->command->info("Created {$totalAssignments} contact-product training records.");
    }
}
