<?php

namespace Database\Seeders;

use App\Models\EnablementGoal;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Database\Seeder;

class EnablementGoalSeeder extends Seeder
{
    /**
     * Targets by PSM name → partner name fragment → category targets.
     *
     * Aggregate groups use the group key directly (sales, pre_sales).
     * Line-level groups use the line key (delinno_polaris, delapp_fcr, etc.).
     *
     * @var array<string, array<string, array<string, array{baseline: int, target: int}>>>
     */
    private const GOALS = [
        'Gunnar Stoa' => [
            'Deloitte' => [
                'sales' => ['baseline' => 80, 'target' => 150],
                'pre_sales' => ['baseline' => 15, 'target' => 40],
                'delinno_polaris' => ['baseline' => 10, 'target' => 25],
                'delinno_ado' => ['baseline' => 5, 'target' => 15],
                'delinno_forecaster' => ['baseline' => 8, 'target' => 20],
                'delinno_comodeler' => ['baseline' => 3, 'target' => 10],
                'delinno_agentstudio' => ['baseline' => 2, 'target' => 8],
                'delapp_fcr' => ['baseline' => 35, 'target' => 58],
                'delapp_ifp' => ['baseline' => 35, 'target' => 60],
                'delapp_id' => ['baseline' => 45, 'target' => 75],
                'delapp_owp' => ['baseline' => 10, 'target' => 20],
                'delapp_spm' => ['baseline' => 10, 'target' => 20],
                'delapp_ibp' => ['baseline' => 0, 'target' => 5],
                'delapp_mfp' => ['baseline' => 1, 'target' => 4],
            ],
            'Slalom' => [
                'sales' => ['baseline' => 10, 'target' => 25],
                'pre_sales' => ['baseline' => 3, 'target' => 8],
                'delinno_polaris' => ['baseline' => 1, 'target' => 4],
                'delinno_ado' => ['baseline' => 1, 'target' => 3],
                'delapp_fcr' => ['baseline' => 2, 'target' => 5],
                'delapp_ifp' => ['baseline' => 2, 'target' => 6],
                'delapp_id' => ['baseline' => 3, 'target' => 8],
            ],
            'Spaulding' => [
                'sales' => ['baseline' => 12, 'target' => 25],
                'pre_sales' => ['baseline' => 5, 'target' => 12],
                'delinno_polaris' => ['baseline' => 2, 'target' => 5],
                'delinno_forecaster' => ['baseline' => 1, 'target' => 4],
                'delapp_fcr' => ['baseline' => 5, 'target' => 10],
                'delapp_ifp' => ['baseline' => 5, 'target' => 10],
                'delapp_id' => ['baseline' => 6, 'target' => 12],
            ],
        ],
        'Rick Palmer' => [
            'Accenture' => [
                'sales' => ['baseline' => 70, 'target' => 130],
                'pre_sales' => ['baseline' => 10, 'target' => 25],
                'delinno_polaris' => ['baseline' => 12, 'target' => 30],
                'delinno_ado' => ['baseline' => 8, 'target' => 20],
                'delinno_forecaster' => ['baseline' => 6, 'target' => 15],
                'delinno_comodeler' => ['baseline' => 4, 'target' => 10],
                'delinno_agentstudio' => ['baseline' => 3, 'target' => 10],
                'delapp_fcr' => ['baseline' => 25, 'target' => 42],
                'delapp_ifp' => ['baseline' => 20, 'target' => 35],
                'delapp_id' => ['baseline' => 50, 'target' => 85],
                'delapp_owp' => ['baseline' => 8, 'target' => 15],
                'delapp_spm' => ['baseline' => 8, 'target' => 15],
                'delapp_ibp' => ['baseline' => 2, 'target' => 8],
            ],
            'TruQua' => [
                'sales' => ['baseline' => 15, 'target' => 30],
                'pre_sales' => ['baseline' => 3, 'target' => 8],
                'delinno_polaris' => ['baseline' => 2, 'target' => 6],
                'delapp_fcr' => ['baseline' => 6, 'target' => 14],
                'delapp_ifp' => ['baseline' => 5, 'target' => 12],
                'delapp_id' => ['baseline' => 6, 'target' => 14],
            ],
        ],
        'Nicole Demarey' => [
            'KPMG' => [
                'sales' => ['baseline' => 18, 'target' => 35],
                'pre_sales' => ['baseline' => 5, 'target' => 12],
                'delinno_polaris' => ['baseline' => 1, 'target' => 4],
                'delinno_forecaster' => ['baseline' => 1, 'target' => 3],
                'delapp_fcr' => ['baseline' => 5, 'target' => 10],
                'delapp_ifp' => ['baseline' => 6, 'target' => 12],
                'delapp_id' => ['baseline' => 3, 'target' => 8],
                'delapp_owp' => ['baseline' => 2, 'target' => 6],
            ],
            'PwC' => [
                'sales' => ['baseline' => 12, 'target' => 25],
                'pre_sales' => ['baseline' => 4, 'target' => 8],
                'delinno_ado' => ['baseline' => 1, 'target' => 4],
                'delapp_fcr' => ['baseline' => 3, 'target' => 8],
                'delapp_ifp' => ['baseline' => 4, 'target' => 10],
                'delapp_id' => ['baseline' => 3, 'target' => 10],
            ],
        ],
        'Ben Wilson' => [
            'Grant Thornton' => [
                'sales' => ['baseline' => 4, 'target' => 10],
                'pre_sales' => ['baseline' => 4, 'target' => 12],
                'delinno_polaris' => ['baseline' => 1, 'target' => 3],
                'delapp_fcr' => ['baseline' => 2, 'target' => 5],
                'delapp_ifp' => ['baseline' => 2, 'target' => 6],
                'delapp_id' => ['baseline' => 2, 'target' => 6],
            ],
            'Peloton' => [
                'sales' => ['baseline' => 8, 'target' => 20],
                'pre_sales' => ['baseline' => 3, 'target' => 8],
                'delinno_polaris' => ['baseline' => 1, 'target' => 4],
                'delinno_agentstudio' => ['baseline' => 1, 'target' => 3],
                'delapp_fcr' => ['baseline' => 4, 'target' => 10],
                'delapp_ifp' => ['baseline' => 5, 'target' => 12],
                'delapp_id' => ['baseline' => 4, 'target' => 10],
                'delapp_spm' => ['baseline' => 2, 'target' => 5],
            ],
        ],
    ];

    public function run(): void
    {
        // Clear old category keys that no longer exist in config
        $validKeys = EnablementGoal::categoryKeys();
        EnablementGoal::whereNotIn('category', $validKeys)->delete();

        foreach (self::GOALS as $psmName => $partners) {
            $user = User::where('name', 'like', "%{$psmName}%")->first();
            if (! $user) {
                $this->command?->warn("PSM '{$psmName}' not found — skipping.");

                continue;
            }

            foreach ($partners as $partnerFragment => $categories) {
                $partner = Partner::where('name', 'like', "%{$partnerFragment}%")
                    ->where('user_id', $user->id)
                    ->first();

                if (! $partner) {
                    $this->command?->warn("Partner matching '{$partnerFragment}' for {$psmName} not found — skipping.");

                    continue;
                }

                foreach ($categories as $categoryKey => $values) {
                    EnablementGoal::updateOrCreate(
                        [
                            'partner_id' => $partner->id,
                            'user_id' => $user->id,
                            'category' => $categoryKey,
                        ],
                        [
                            'baseline' => $values['baseline'],
                            'target' => $values['target'],
                        ],
                    );
                }
            }
        }
    }
}
