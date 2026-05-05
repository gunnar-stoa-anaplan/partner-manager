<?php

namespace Database\Seeders;

use App\Models\Asset;
use Illuminate\Database\Seeder;

class AssetSeeder extends Seeder
{
    /**
     * Seed a small representative set of assets so the journey builder has
     * something to drag. Real catalog will be loaded via CSV import.
     */
    public function run(): void
    {
        $assets = [
            ['type' => 'academy_course', 'name' => 'IFP Foundations', 'duration_minutes' => 240, 'tags' => ['ifp', 'foundation'], 'external_id' => 'AC-IFP-101'],
            ['type' => 'academy_course', 'name' => 'IFP Modeling Essentials', 'duration_minutes' => 360, 'tags' => ['ifp', 'modeling'], 'external_id' => 'AC-IFP-201'],
            ['type' => 'academy_course', 'name' => 'IFP Advanced Workflows', 'duration_minutes' => 480, 'tags' => ['ifp', 'advanced'], 'external_id' => 'AC-IFP-301'],
            ['type' => 'academy_course', 'name' => 'Anaplan Platform Fundamentals', 'duration_minutes' => 180, 'tags' => ['platform', 'foundation'], 'external_id' => 'AC-PLT-101'],
            ['type' => 'academy_course', 'name' => 'Connected Planning Patterns', 'duration_minutes' => 240, 'tags' => ['platform', 'advanced'], 'external_id' => 'AC-PLT-301'],

            ['type' => 'ilt_workshop', 'name' => 'IFP Build-Along Lab', 'duration_minutes' => 480, 'tags' => ['ifp', 'hands-on'], 'external_id' => 'IL-IFP-LAB-1'],
            ['type' => 'ilt_workshop', 'name' => 'Finance Use-Case Workshop', 'duration_minutes' => 240, 'tags' => ['finance', 'hands-on'], 'external_id' => 'IL-FIN-WS-1'],
            ['type' => 'ilt_workshop', 'name' => 'Solution Architecture Bootcamp', 'duration_minutes' => 960, 'tags' => ['advanced'], 'external_id' => 'IL-ARCH-BC-1'],

            ['type' => 'specialist_engagement', 'name' => 'Supervised Project Review', 'duration_minutes' => 120, 'tags' => ['delivery', 'mentorship'], 'external_id' => 'SE-PROJ-REV'],
            ['type' => 'specialist_engagement', 'name' => 'Live Implementation Shadow', 'duration_minutes' => 480, 'tags' => ['delivery'], 'external_id' => 'SE-IMPL-SHAD'],
            ['type' => 'specialist_engagement', 'name' => 'Architect 1:1 Mentorship', 'duration_minutes' => 60, 'tags' => ['mentorship'], 'external_id' => 'SE-ARCH-MENT'],

            ['type' => 'blueprint', 'name' => 'IFP Reference Architecture Blueprint', 'tags' => ['ifp', 'reference'], 'external_id' => 'BP-IFP-REF'],
            ['type' => 'blueprint', 'name' => 'Financial Close Process Blueprint', 'tags' => ['finance', 'reference'], 'external_id' => 'BP-FIN-CLOSE'],

            ['type' => 'reading', 'name' => 'Anaplan Best Practices Guide', 'tags' => ['foundation'], 'external_id' => 'RD-BP-GUIDE'],
            ['type' => 'reading', 'name' => 'IFP Customer Case Study Pack', 'tags' => ['ifp'], 'external_id' => 'RD-IFP-CASES'],

            ['type' => 'video', 'name' => 'IFP Demo Walkthrough', 'duration_minutes' => 45, 'tags' => ['ifp'], 'external_id' => 'VD-IFP-DEMO'],
            ['type' => 'video', 'name' => 'Customer Discovery Conversation Series', 'duration_minutes' => 90, 'tags' => ['sales'], 'external_id' => 'VD-DISC-SER'],

            ['type' => 'mentor_session', 'name' => 'Career Path Conversation', 'duration_minutes' => 45, 'tags' => ['mentorship'], 'external_id' => 'MS-CAREER'],
            ['type' => 'mentor_session', 'name' => 'Pre-Cert Readiness Check-In', 'duration_minutes' => 30, 'tags' => ['mentorship'], 'external_id' => 'MS-CERT-CHK'],
        ];

        foreach ($assets as $row) {
            Asset::firstOrCreate(
                ['type' => $row['type'], 'external_id' => $row['external_id']],
                array_merge($row, [
                    'description' => $row['description'] ?? 'Sample asset for journey builder demo.',
                    'status' => 'active',
                    'scope' => 'global',
                ])
            );
        }
    }
}
