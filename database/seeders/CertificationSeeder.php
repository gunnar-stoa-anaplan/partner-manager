<?php

namespace Database\Seeders;

use App\Models\Certification;
use App\Models\Contact;
use App\Models\Partner;
use Illuminate\Database\Seeder;

class CertificationSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating certifications...');

        $partners = Partner::with('contacts')->get();
        $count = 0;

        $certTypes = [
            'model_builder' => ['level' => 'associate', 'label' => 'Model Builder'],
            'model_builder_pro' => ['level' => 'professional', 'label' => 'Model Builder Professional'],
            'solution_architect' => ['level' => 'professional', 'label' => 'Solution Architect'],
            'master_anaplanner' => ['level' => 'master', 'label' => 'Master Anaplanner'],
            'platform_expert' => ['level' => 'expert', 'label' => 'Platform Expert'],
        ];

        $productAreas = ['IFP', 'SCM', 'SPM', 'OWP', 'Polaris', 'Platform'];
        $versions = ['2024.1', '2024.2', '2025.1'];

        foreach ($partners as $partner) {
            $contacts = $partner->contacts;
            if ($contacts->isEmpty()) continue;

            // Each partner gets a mix of certifications — larger partners get more
            $certCount = fake()->numberBetween(3, min(15, $contacts->count() * 2));

            for ($i = 0; $i < $certCount; $i++) {
                $contact = $contacts->random();
                $typeKey = fake()->randomElement(array_keys($certTypes));
                $certInfo = $certTypes[$typeKey];

                // Weighted toward active certs
                $status = fake()->randomElement(['active', 'active', 'active', 'active', 'expired', 'pending']);
                $issuedDate = fake()->dateTimeBetween('-24 months', '-1 month');

                $expiryDate = match ($status) {
                    'active' => fake()->dateTimeBetween('+1 month', '+18 months'),
                    'expired' => fake()->dateTimeBetween('-6 months', '-1 day'),
                    'pending' => null,
                };

                Certification::create([
                    'contact_id' => $contact->id,
                    'partner_id' => $partner->id,
                    'type' => $typeKey,
                    'product_area' => fake()->randomElement($productAreas),
                    'level' => $certInfo['level'],
                    'status' => $status,
                    'issued_date' => $status !== 'pending' ? $issuedDate : null,
                    'expiry_date' => $expiryDate,
                    'exam_score' => $status !== 'pending' ? fake()->numberBetween(65, 100) : null,
                    'version' => fake()->randomElement($versions),
                    'credential_id' => strtoupper(fake()->bothify('CERT-####-??##')),
                    'source' => fake()->randomElement(['academy', 'import', 'manual']),
                ]);

                $count++;
            }
        }

        $this->command->info("Created {$count} certifications.");
    }
}
