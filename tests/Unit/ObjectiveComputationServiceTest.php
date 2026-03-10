<?php

namespace Tests\Unit;

use App\Models\Partner;
use App\Models\PsmObjective;
use App\Models\User;
use App\Services\ObjectiveComputationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ObjectiveComputationServiceTest extends TestCase
{
    use RefreshDatabase;

    private ObjectiveComputationService $service;

    private User $psm;

    private Partner $partner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);

        $this->service = new ObjectiveComputationService;
        $this->psm = User::factory()->psm()->create();
        $this->partner = Partner::factory()->create(['user_id' => $this->psm->id]);
    }

    public function test_manual_metric_returns_stored_value(): void
    {
        $objective = PsmObjective::factory()->manual()->create([
            'user_id' => $this->psm->id,
            'partner_id' => $this->partner->id,
            'current_value' => 7,
        ]);

        $result = $this->service->compute($objective);

        $this->assertEquals(7.0, $result);
    }

    public function test_computes_certifications_correctly(): void
    {
        $certBase = ['partner_id' => $this->partner->id, 'type' => 'model_builder', 'level' => 'associate', 'created_at' => now(), 'updated_at' => now()];
        DB::table('certifications')->insert([
            array_merge($certBase, ['status' => 'active']),
            array_merge($certBase, ['status' => 'active']),
            array_merge($certBase, ['status' => 'active']),
            array_merge($certBase, ['status' => 'expired']),
        ]);

        $objective = PsmObjective::factory()->create([
            'user_id' => $this->psm->id,
            'partner_id' => $this->partner->id,
            'metric_type' => 'certifications',
            'metric_config' => ['cert_type' => 'model_builder'],
            'target_value' => 5,
        ]);

        $result = $this->service->compute($objective);

        $this->assertEquals(3.0, $result);
    }

    public function test_computes_pipeline_value_correctly(): void
    {
        $oppBase = ['partner_id' => $this->partner->id, 'opportunity_name' => 'Test Opp', 'created_at' => now(), 'updated_at' => now()];
        DB::table('opportunities')->insert([
            array_merge($oppBase, ['stage' => 'Prospecting', 'amount' => 50000, 'salesforce_id' => 'SF001']),
            array_merge($oppBase, ['stage' => 'Qualification', 'amount' => 30000, 'salesforce_id' => 'SF002']),
            array_merge($oppBase, ['stage' => 'Closed Won', 'amount' => 100000, 'salesforce_id' => 'SF003']),
        ]);

        $objective = PsmObjective::factory()->create([
            'user_id' => $this->psm->id,
            'partner_id' => $this->partner->id,
            'metric_type' => 'pipeline_value',
            'target_value' => 100000,
        ]);

        $result = $this->service->compute($objective);

        $this->assertEquals(80000.0, $result);
    }

    public function test_auto_completes_when_actual_meets_target(): void
    {
        $objective = PsmObjective::factory()->manual()->create([
            'user_id' => $this->psm->id,
            'partner_id' => $this->partner->id,
            'current_value' => 10,
            'target_value' => 10,
            'status' => 'active',
        ]);

        $this->service->computeAndUpdate($objective);

        $objective->refresh();
        $this->assertEquals('completed', $objective->status);
        $this->assertNotNull($objective->completed_at);
    }

    public function test_sets_missed_when_past_deadline_and_not_met(): void
    {
        $objective = PsmObjective::factory()->create([
            'user_id' => $this->psm->id,
            'partner_id' => $this->partner->id,
            'metric_type' => 'manual',
            'current_value' => 3,
            'target_value' => 10,
            'target_date' => now()->subDay(),
            'status' => 'active',
        ]);

        $this->service->computeAndUpdate($objective);

        $objective->refresh();
        $this->assertEquals('missed', $objective->status);
    }

    public function test_skips_non_active_objectives(): void
    {
        $objective = PsmObjective::factory()->completed()->create([
            'user_id' => $this->psm->id,
            'partner_id' => $this->partner->id,
        ]);

        $original = $objective->toArray();
        $this->service->computeAndUpdate($objective);
        $objective->refresh();

        $this->assertEquals('completed', $objective->status);
    }

    public function test_computes_resources_registered(): void
    {
        DB::table('contacts')->insert([
            ['partner_id' => $this->partner->id, 'contact_type' => 'resource', 'first_name' => 'A', 'last_name' => 'B', 'email' => 'a@b.com', 'created_at' => now(), 'updated_at' => now()],
            ['partner_id' => $this->partner->id, 'contact_type' => 'resource', 'first_name' => 'C', 'last_name' => 'D', 'email' => 'c@d.com', 'created_at' => now(), 'updated_at' => now()],
            ['partner_id' => $this->partner->id, 'contact_type' => 'influencer', 'first_name' => 'E', 'last_name' => 'F', 'email' => 'e@f.com', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $objective = PsmObjective::factory()->create([
            'user_id' => $this->psm->id,
            'partner_id' => $this->partner->id,
            'metric_type' => 'resources_registered',
            'target_value' => 5,
        ]);

        $result = $this->service->compute($objective);

        $this->assertEquals(2.0, $result);
    }
}
