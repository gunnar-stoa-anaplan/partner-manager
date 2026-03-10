<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\Partner;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ContactFactory extends Factory
{
    /**
     * Known partner name → email domain mapping for contact email generation.
     * Partners not listed here fall back to a sanitized version of their name.
     */
    public const KNOWN_PARTNERS = [
        'Accenture - US' => 'accenture.com',
        'Accenture France' => 'accenture.com',
        'Accenture India' => 'accenture.com',
        'Accenture UK' => 'accenture.com',
        'ACCENTURE JAPAN LTD.' => 'accenture.com',
        'ACCENTURE - CONSULTORES DE GESTÃO, S.A.' => 'accenture.com',
        'Accenture S.p.A A Socio Unico' => 'accenture.com',
        'Deloitte Consulting LLP - US (HQ)' => 'deloitte.com',
        'Deloitte - Germany' => 'deloitte.com',
        'Deloitte AS - Norway' => 'deloitte.com',
        'Deloitte Consulting S.L.U. (Spain)' => 'deloitte.com',
        'Deloitte Consulting S.r.l.' => 'deloitte.com',
        'Deloitte EMEA' => 'deloitte.com',
        'Deloitte France' => 'deloitte.com',
        'Deloitte Nederland' => 'deloitte.com',
        'Deloitte South Africa' => 'deloitte.com',
        'Deloitte Switzerland' => 'deloitte.com',
        'Deloitte T&T Pty Ltd' => 'deloitte.com',
        'Deloitte Technology Consulting Middle East FZ LLC' => 'deloitte.com',
        'Deloitte Tohmatsu Consulting LLC - Japan' => 'deloitte.com',
        'Deloitte Touche Tohmatsu India LLP' => 'deloitte.com',
        'Deloitte UK' => 'deloitte.com',
        'PwC' => 'pwc.com',
        'PwC Advisory Sp. z o.o.' => 'pwc.com',
        'PwC Consulting LLC' => 'pwc.com',
        'PwC France' => 'pwc.com',
        'PwC LLP - US' => 'pwc.com',
        'PWC Australia' => 'pwc.com',
        'PWC Singapore' => 'pwc.com',
        'PT. PwC Consulting Indonesia' => 'pwc.com',
        'PricewaterhouseCoopers Private Limited' => 'pwc.com',
        'KPMG US (HQ)' => 'kpmg.com',
        'KPMG UK' => 'kpmg.com',
        'KPMG Australia' => 'kpmg.com',
        'KPMG CONSULTING CO., LTD. Japan' => 'kpmg.com',
        'Tata Consultancy Services' => 'tcs.com',
        'Tata Consultancy Services Limited' => 'tcs.com',
        'TruQua, an IBM Company' => 'ibm.com',
        'The Boston Consulting Group' => 'bcg.com',
        'BearingPoint' => 'bearingpoint.com',
        'Bearingpoint GmbH' => 'bearingpoint.com',
        'Fujitsu Limited' => 'fujitsu.com',
        'Grant Thornton LLP' => 'grantthornton.com',
        'Slalom Consulting' => 'slalom.com',
        'Genpact Intl' => 'genpact.com',
        'NTT DATA JAPAN CORPORATION' => 'nttdata.com',
        'Bluecrux BV' => 'bluecrux.com',
        'Bluecrux, Inc.' => 'bluecrux.com',
        'Spaulding Ridge' => 'spauldingridge.com',
        'Spaulding Ridge Advisory Sweden AB' => 'spauldingridge.com',
        'Spaulding Ridge UK' => 'spauldingridge.com',
        'Peloton Group' => 'pelotongroup.com',
        'ABeam Consulting' => 'abeam.com',
        'Argano, LLC (Partner)' => 'argano.com',
        'Keyrus EMEA' => 'keyrus.com',
        'Keyrus EPM Americas' => 'keyrus.com',
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
            'partner_id' => Partner::factory(),
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

    /**
     * Assign a portal role by slug (creates the role if it doesn't exist).
     */
    public function portalRole(string $slug): static
    {
        return $this->state(fn () => [
            'portal_access' => true,
            'portal_role_id' => Role::firstOrCreate(
                ['slug' => $slug, 'type' => 'portal'],
                ['display_name' => ucwords(str_replace('_', ' ', $slug))]
            )->id,
            'password' => 'password',
        ]);
    }

    public function portalAdmin(): static
    {
        return $this->portalRole('partner_admin');
    }

    public function portalSales(): static
    {
        return $this->portalRole('sales');
    }

    public function portalDelivery(): static
    {
        return $this->portalRole('delivery_lead');
    }

    public static function partnerDomain(string $partnerName): string
    {
        return self::KNOWN_PARTNERS[$partnerName] ?? strtolower(preg_replace('/[^a-z0-9]+/i', '', $partnerName)).'.com';
    }
}
