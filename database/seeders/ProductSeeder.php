<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding product catalog...');

        $products = [
            // Finance
            ['name' => 'Consolidation', 'product_line' => 'Finance', 'code' => 'CON', 'sort_order' => 1],
            ['name' => 'Disclosure Management', 'product_line' => 'Finance', 'code' => 'DM', 'sort_order' => 2],
            ['name' => 'Integrated Financial Planning', 'product_line' => 'Finance', 'code' => 'IFP', 'sort_order' => 3],
            ['name' => 'Financial Planning & Close', 'product_line' => 'Finance', 'code' => 'FPC', 'sort_order' => 4],
            ['name' => 'Financial Close & Reporting', 'product_line' => 'Finance', 'code' => 'FCR', 'sort_order' => 5],
            ['name' => 'Anaplan Extended Logic', 'product_line' => 'Finance', 'code' => 'AXL', 'sort_order' => 6],
            ['name' => 'Anaplan Data Management', 'product_line' => 'Finance', 'code' => 'ADM', 'sort_order' => 7],

            // Supply Chain
            ['name' => 'Demand Planning', 'product_line' => 'Supply Chain', 'code' => 'DP', 'sort_order' => 1],
            ['name' => 'Inventory Planning', 'product_line' => 'Supply Chain', 'code' => 'IP', 'sort_order' => 2],
            ['name' => 'Supply Planning Application', 'product_line' => 'Supply Chain', 'code' => 'SPA', 'sort_order' => 3],
            ['name' => 'Production Planning', 'product_line' => 'Supply Chain', 'code' => 'PP', 'sort_order' => 4],
            ['name' => 'Rough Cut Capacity Planning', 'product_line' => 'Supply Chain', 'code' => 'RCCP', 'sort_order' => 5],
            ['name' => 'Statistical Forecasting', 'product_line' => 'Supply Chain', 'code' => 'SF', 'sort_order' => 6],
            ['name' => 'Integrated Business Planning', 'product_line' => 'Supply Chain', 'code' => 'IBP', 'sort_order' => 7],
            ['name' => 'Master Financial Planning', 'product_line' => 'Supply Chain', 'code' => 'MFP', 'sort_order' => 8],

            // Workforce
            ['name' => 'Operational Workforce Planning', 'product_line' => 'Workforce', 'code' => 'OWP', 'sort_order' => 1],

            // Sales
            ['name' => 'Territory & Quota', 'product_line' => 'Sales', 'code' => 'TQ', 'sort_order' => 1],
            ['name' => 'GTM Capacity Planning', 'product_line' => 'Sales', 'code' => 'GTMCP', 'sort_order' => 2],
            ['name' => 'Segmentation & Scoring', 'product_line' => 'Sales', 'code' => 'SS', 'sort_order' => 3],
            ['name' => 'Revenue Performance Management', 'product_line' => 'Sales', 'code' => 'RPM', 'sort_order' => 4],

            // Platform
            ['name' => 'Polaris', 'product_line' => 'Platform', 'code' => 'POL', 'sort_order' => 1],
            ['name' => 'Forecaster', 'product_line' => 'Platform', 'code' => 'FOR', 'sort_order' => 2],
        ];

        foreach ($products as $product) {
            Product::create(array_merge($product, [
                'slug' => Str::slug($product['name']),
                'status' => 'published',
            ]));
        }

        $this->command->info('Created '.count($products).' products.');
    }
}
