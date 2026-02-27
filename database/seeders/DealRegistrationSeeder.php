<?php

namespace Database\Seeders;

use App\Models\DealRegistration;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Database\Seeder;

class DealRegistrationSeeder extends Seeder
{
    public function run(): void
    {
        $partners = Partner::take(5)->get();
        $admin = User::whereRelation('role', 'slug', 'admin')->first();

        if ($partners->isEmpty() || ! $admin) {
            $this->command->warn('Need partners and admin user. Skipping DealRegistrationSeeder.');

            return;
        }

        $deals = [
            ['deal_name' => 'Acme Corp — FP&A Modernization', 'customer_name' => 'Acme Corporation', 'estimated_value' => 450000, 'product_area' => 'Finance', 'status' => 'approved'],
            ['deal_name' => 'Global Logistics — Supply Chain Planning', 'customer_name' => 'Global Logistics Inc', 'estimated_value' => 780000, 'product_area' => 'Supply Chain', 'status' => 'approved'],
            ['deal_name' => 'TechStart — Revenue Planning', 'customer_name' => 'TechStart Solutions', 'estimated_value' => 250000, 'product_area' => 'Sales', 'status' => 'submitted'],
            ['deal_name' => 'MegaRetail — Workforce Optimization', 'customer_name' => 'MegaRetail Corp', 'estimated_value' => 520000, 'product_area' => 'Workforce', 'status' => 'submitted'],
            ['deal_name' => 'FinServ Group — Risk Analytics', 'customer_name' => 'FinServ Group', 'estimated_value' => 350000, 'product_area' => 'Finance', 'status' => 'under_review'],
            ['deal_name' => 'DataDriven — Platform Integration', 'customer_name' => 'DataDriven Analytics', 'estimated_value' => 180000, 'product_area' => 'Platform', 'status' => 'rejected', 'rejection_reason' => 'Duplicate registration — already registered by another partner.'],
            ['deal_name' => 'CloudFirst — S&OP Implementation', 'customer_name' => 'CloudFirst Technologies', 'estimated_value' => 620000, 'product_area' => 'Supply Chain', 'status' => 'approved'],
            ['deal_name' => 'BioPharm — Demand Planning', 'customer_name' => 'BioPharm International', 'estimated_value' => 290000, 'product_area' => 'Supply Chain', 'status' => 'submitted'],
        ];

        $count = 0;
        foreach ($deals as $i => $d) {
            $partner = $partners[$i % $partners->count()];

            $reg = DealRegistration::create([
                'partner_id' => $partner->id,
                'deal_name' => $d['deal_name'],
                'customer_name' => $d['customer_name'],
                'estimated_value' => $d['estimated_value'],
                'product_area' => $d['product_area'],
                'status' => $d['status'],
                'submitted_at' => now()->subDays(rand(5, 60)),
                'expires_at' => now()->addDays(rand(10, 80)),
                'rejection_reason' => $d['rejection_reason'] ?? null,
                'reviewed_by' => in_array($d['status'], ['approved', 'rejected']) ? $admin->id : null,
                'reviewed_at' => in_array($d['status'], ['approved', 'rejected']) ? now()->subDays(rand(1, 30)) : null,
            ]);

            $count++;
        }

        $this->command->info("Created {$count} demo deal registrations.");
    }
}
