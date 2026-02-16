<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\Partner;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ContactFactory extends Factory
{
    public const KNOWN_PARTNERS = [
        'Accenture' => 'accenture.com',
        'Deloitte' => 'deloitte.com',
        'PwC' => 'pwc.com',
        'KPMG' => 'kpmg.com',
        'EY' => 'ey.com',
        'Capgemini' => 'capgemini.com',
        'Infosys' => 'infosys.com',
        'Wipro' => 'wipro.com',
        'TCS' => 'tcs.com',
        'Cognizant' => 'cognizant.com',
        'IBM' => 'ibm.com',
        'HCL' => 'hcltech.com',
        'Tech Mahindra' => 'techmahindra.com',
        'McKinsey' => 'mckinsey.com',
        'BCG' => 'bcg.com',
        'Bain' => 'bain.com',
    ];

    public function definition(): array
    {
        $techConsultingTitles = [
            'Technical Architect',
            'Enterprise Architect',
            'Integration Consultant',
            'Cloud Solutions Engineer',
            'IT Strategy Consultant',
            'Digital Delivery Manager',
            'Technical Program Manager',
            'Managing Director',
            'Technology Account Lead',
            'Client Account Lead',
            'Data Analytics Consultant',
            'CRM Functional Consultant',
            'Cybersecurity Advisor',
            'Innovation Partner',
            'AI/ML Engineer',
            'Platform Engineering Manager',
            'Technology Partner',
            'Client Delivery Lead',
            'ERP Implementation Manager',
        ];

        return [
            'partner_id' => Partner::inRandomOrder()->first()->id,
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => $this->faker->phoneNumber,
            'title' => $this->faker->randomElement($techConsultingTitles),
            'avatar' => 'img/demo/avatars/avatar-'.$this->faker->numberBetween(1, 18).'.png',
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Contact $contact) {
            if ($contact->partner_id) {
                $partner = Partner::find($contact->partner_id);
                if ($partner) {
                    $domain = self::partnerDomain($partner->name);
                    $first = preg_replace('/[^a-z]/', '', strtolower(Str::ascii($contact->first_name)));
                    $last = preg_replace('/[^a-z]/', '', strtolower(Str::ascii($contact->last_name)));
                    $email = $first.'.'.$last.'@'.$domain;

                    // Ensure uniqueness
                    if (Contact::where('email', $email)->exists()) {
                        $i = 2;
                        while (Contact::where('email', $first.'.'.$last.$i.'@'.$domain)->exists()) {
                            $i++;
                        }
                        $email = $first.'.'.$last.$i.'@'.$domain;
                    }

                    $contact->email = $email;
                }
            }
        });
    }

    public function portalAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'portal_access' => true,
            'portal_role' => 'partner_admin',
            'password' => 'password',
        ]);
    }

    public function portalSales(): static
    {
        return $this->state(fn (array $attributes) => [
            'portal_access' => true,
            'portal_role' => 'sales',
            'password' => 'password',
        ]);
    }

    public function portalDelivery(): static
    {
        return $this->state(fn (array $attributes) => [
            'portal_access' => true,
            'portal_role' => 'delivery_lead',
            'password' => 'password',
        ]);
    }

    public function portalRole(string $role): static
    {
        return $this->state(fn (array $attributes) => [
            'portal_access' => true,
            'portal_role' => $role,
            'password' => 'password',
        ]);
    }

    public static function partnerDomain(string $partnerName): string
    {
        return self::KNOWN_PARTNERS[$partnerName] ?? strtolower(preg_replace('/[^a-z0-9]+/i', '', $partnerName)).'.com';
    }
}
