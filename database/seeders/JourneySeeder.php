<?php

namespace Database\Seeders;

use App\Models\Journey;
use App\Models\User;
use Illuminate\Database\Seeder;

class JourneySeeder extends Seeder
{
    /**
     * Seed the journeys table with the IFP Delivery Journey and assign it
     * to the first admin user (falling back to the first user if none exist).
     */
    public function run(): void
    {
        $journey = Journey::firstOrCreate(
            ['journey_key' => 'ifp-delivery-finance'],
            [
                'name' => 'IFP Delivery Journey',
                'product_area' => 'Integrated Financial Planning',
                'persona' => 'Delivery',
                'status' => 'pilot',
                'db_path' => '/home/gstoa/.openclaw/workspace/agents/journey/ifp-delivery-finance/journey_state.db',
                'config_path' => null,
            ]
        );

        // Assign to the first admin user, or the first user if no admin exists
        $user = User::whereRelation('role', 'slug', 'admin')->first()
            ?? User::whereRelation('role', 'slug', 'super_admin')->first()
            ?? User::first();

        if ($user) {
            $journey->assignments()->firstOrCreate(['user_id' => $user->id]);
        }
    }
}
