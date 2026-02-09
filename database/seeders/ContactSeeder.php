<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Contact;
use App\Models\Partner;
use App\Models\Project;

class ContactSeeder extends Seeder
{
    public function run(): void
    {
        $partners = Partner::all();

        foreach ($partners as $partner) {
            // Create 10 contacts per partner
            Contact::factory(10)->create([
                'partner_id' => $partner->id,
            ])->each(function ($contact) {
                // Attach the contact to 1–3 random projects
                $projectIds = Project::inRandomOrder()
                    ->limit(rand(1, 3))
                    ->pluck('id')
                    ->toArray();

                $contact->projects()->attach($projectIds);
            });
        }
    }
}