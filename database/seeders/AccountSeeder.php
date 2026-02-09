<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Partner;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        $partners = Partner::all();

        Account::factory()
            ->count(10)
            ->create()
            ->each(function ($account) use ($partners) {
                if ($partners->count() > 0) {
                    $assignedPartners = $partners->random(rand(1, min(3, $partners->count())));
                    $account->partners()->attach($assignedPartners->pluck('id')->toArray());
                }
            });
    }
}