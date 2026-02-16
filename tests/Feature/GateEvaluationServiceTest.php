<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Opportunity;
use App\Models\Partner;
use App\Models\PartnerPath;
use App\Models\PartnerPathGate;
use App\Models\PartnerPathStage;
use App\Models\Project;
use App\Models\User;
use App\Services\GateEvaluationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GateEvaluationServiceTest extends TestCase
{
    use RefreshDatabase;

    private GateEvaluationService $service;

    private Partner $partner;

    private PartnerPath $path;

    private PartnerPathStage $stage1;

    private PartnerPathStage $stage2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(GateEvaluationService::class);

        $psm = User::factory()->create(['role' => 'psm']);
        $this->partner = Partner::factory()->create(['user_id' => $psm->id]);

        $this->path = PartnerPath::factory()->create();
        $this->stage1 = PartnerPathStage::factory()->create([
            'partner_path_id' => $this->path->id,
            'stage_number' => 1,
            'sort_order' => 0,
        ]);
        $this->stage2 = PartnerPathStage::factory()->create([
            'partner_path_id' => $this->path->id,
            'stage_number' => 2,
            'sort_order' => 1,
        ]);
    }

    private function createGate(string $key, float $threshold, string $comparison = 'gte', string $unit = 'count'): PartnerPathGate
    {
        return PartnerPathGate::create([
            'partner_path_id' => $this->path->id,
            'from_stage_id' => $this->stage1->id,
            'to_stage_id' => $this->stage2->id,
            'requirement_key' => $key,
            'requirement_label' => ucwords(str_replace('_', ' ', $key)),
            'comparison' => $comparison,
            'threshold_value' => $threshold,
            'unit' => $unit,
        ]);
    }

    // ─── evaluateGates ────────────────────────────────────────────

    public function test_evaluates_contact_count_gate(): void
    {
        $this->createGate('contact_count', 3);

        Contact::factory()->count(5)->create(['partner_id' => $this->partner->id]);

        $results = $this->service->evaluateGates($this->partner, $this->stage1->id, $this->stage2->id);

        $this->assertCount(1, $results);
        $this->assertTrue($results->first()['met']);
        $this->assertEquals(5, $results->first()['actual_value']);
    }

    public function test_evaluates_contact_count_gate_not_met(): void
    {
        $this->createGate('contact_count', 10);

        Contact::factory()->count(2)->create(['partner_id' => $this->partner->id]);

        $results = $this->service->evaluateGates($this->partner, $this->stage1->id, $this->stage2->id);

        $this->assertFalse($results->first()['met']);
        $this->assertEquals(2, $results->first()['actual_value']);
    }

    public function test_evaluates_salesforce_connected_gate(): void
    {
        $this->createGate('salesforce_connected', 1, 'gte', 'boolean');

        $this->partner->update(['salesforce_id' => 'SF123']);

        $results = $this->service->evaluateGates($this->partner, $this->stage1->id, $this->stage2->id);

        $this->assertTrue($results->first()['met']);
    }

    public function test_salesforce_not_connected_fails_gate(): void
    {
        $this->createGate('salesforce_connected', 1, 'gte', 'boolean');

        $this->partner->update(['salesforce_id' => null]);

        $results = $this->service->evaluateGates($this->partner, $this->stage1->id, $this->stage2->id);

        $this->assertFalse($results->first()['met']);
    }

    public function test_evaluates_certified_mb_count_gate(): void
    {
        $this->createGate('certified_mb_count', 2);

        DB::table('certifications')->insert([
            ['partner_id' => $this->partner->id, 'type' => 'model_builder', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
            ['partner_id' => $this->partner->id, 'type' => 'model_builder', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
            ['partner_id' => $this->partner->id, 'type' => 'model_builder', 'status' => 'expired', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $results = $this->service->evaluateGates($this->partner, $this->stage1->id, $this->stage2->id);

        $this->assertTrue($results->first()['met']);
        $this->assertEquals(2, $results->first()['actual_value']);
    }

    public function test_evaluates_completed_project_count(): void
    {
        $this->createGate('completed_project_count', 1);

        Project::factory()->create([
            'partner_id' => $this->partner->id,
            'status' => 'completed',
        ]);

        $results = $this->service->evaluateGates($this->partner, $this->stage1->id, $this->stage2->id);

        $this->assertTrue($results->first()['met']);
    }

    public function test_evaluates_open_opportunity_count(): void
    {
        $this->createGate('open_opportunity_count', 2);

        Opportunity::factory()->forPartner($this->partner->id)->stage('Qualification')->create(['amount' => 100000]);
        Opportunity::factory()->forPartner($this->partner->id)->stage('Proposal/Price Quote')->create(['amount' => 200000]);
        Opportunity::factory()->forPartner($this->partner->id)->stage('Closed Won')->create(['amount' => 50000]);

        $results = $this->service->evaluateGates($this->partner, $this->stage1->id, $this->stage2->id);

        $this->assertTrue($results->first()['met']);
        $this->assertEquals(2, $results->first()['actual_value']);
    }

    public function test_evaluates_pipeline_value_gate(): void
    {
        $this->createGate('pipeline_value', 500000, 'gte', 'currency');

        Opportunity::factory()->forPartner($this->partner->id)->stage('Qualification')->create(['amount' => 300000]);
        Opportunity::factory()->forPartner($this->partner->id)->stage('Proposal/Price Quote')->create(['amount' => 250000]);

        $results = $this->service->evaluateGates($this->partner, $this->stage1->id, $this->stage2->id);

        $this->assertTrue($results->first()['met']);
        $this->assertEquals(550000, $results->first()['actual_value']);
    }

    public function test_evaluates_health_score_gate(): void
    {
        $this->createGate('health_score', 50, 'gte', 'count');

        DB::table('partner_scorecards')->insert([
            'partner_id' => $this->partner->id,
            'overall_score' => 65,
            'certification_score' => 50,
            'pipeline_score' => 60,
            'delivery_score' => 70,
            'engagement_score' => 65,
            'growth_score' => 55,
            'self_sufficiency_score' => 45,
            'scored_at' => now()->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $results = $this->service->evaluateGates($this->partner, $this->stage1->id, $this->stage2->id);

        $this->assertTrue($results->first()['met']);
        $this->assertEquals(65, $results->first()['actual_value']);
    }

    public function test_unknown_requirement_key_returns_zero(): void
    {
        $this->createGate('nonexistent_metric', 1);

        $results = $this->service->evaluateGates($this->partner, $this->stage1->id, $this->stage2->id);

        $this->assertFalse($results->first()['met']);
        $this->assertEquals(0, $results->first()['actual_value']);
    }

    // ─── allGatesMet ──────────────────────────────────────────────

    public function test_all_gates_met_returns_true_when_all_pass(): void
    {
        $this->createGate('contact_count', 1);

        Contact::factory()->count(2)->create(['partner_id' => $this->partner->id]);

        $this->assertTrue($this->service->allGatesMet($this->partner, $this->stage1->id, $this->stage2->id));
    }

    public function test_all_gates_met_returns_false_when_any_fails(): void
    {
        $this->createGate('contact_count', 1);
        $this->createGate('contact_count', 100); // This will fail

        Contact::factory()->count(2)->create(['partner_id' => $this->partner->id]);

        $this->assertFalse($this->service->allGatesMet($this->partner, $this->stage1->id, $this->stage2->id));
    }

    public function test_all_gates_met_returns_true_with_no_gates(): void
    {
        $this->assertTrue($this->service->allGatesMet($this->partner, $this->stage1->id, $this->stage2->id));
    }

    // ─── Comparison Operators ─────────────────────────────────────

    public function test_lte_comparison(): void
    {
        $this->createGate('contact_count', 5, 'lte');

        Contact::factory()->count(3)->create(['partner_id' => $this->partner->id]);

        $results = $this->service->evaluateGates($this->partner, $this->stage1->id, $this->stage2->id);
        $this->assertTrue($results->first()['met']);
    }

    public function test_eq_comparison(): void
    {
        $this->createGate('contact_count', 3, 'eq');

        Contact::factory()->count(3)->create(['partner_id' => $this->partner->id]);

        $results = $this->service->evaluateGates($this->partner, $this->stage1->id, $this->stage2->id);
        $this->assertTrue($results->first()['met']);
    }

    public function test_gt_comparison(): void
    {
        $this->createGate('contact_count', 2, 'gt');

        Contact::factory()->count(3)->create(['partner_id' => $this->partner->id]);

        $results = $this->service->evaluateGates($this->partner, $this->stage1->id, $this->stage2->id);
        $this->assertTrue($results->first()['met']);
    }

    public function test_lt_comparison(): void
    {
        $this->createGate('contact_count', 5, 'lt');

        Contact::factory()->count(3)->create(['partner_id' => $this->partner->id]);

        $results = $this->service->evaluateGates($this->partner, $this->stage1->id, $this->stage2->id);
        $this->assertTrue($results->first()['met']);
    }

    // ─── Multiple Gates ───────────────────────────────────────────

    public function test_multiple_gates_evaluated_independently(): void
    {
        $this->createGate('contact_count', 2);
        $this->createGate('salesforce_connected', 1, 'gte', 'boolean');

        Contact::factory()->count(3)->create(['partner_id' => $this->partner->id]);
        $this->partner->update(['salesforce_id' => null]); // Salesforce not connected

        $results = $this->service->evaluateGates($this->partner, $this->stage1->id, $this->stage2->id);

        $this->assertCount(2, $results);
        $this->assertTrue($results[0]['met']);   // contact_count: 3 >= 2
        $this->assertFalse($results[1]['met']);   // salesforce: 0 >= 1
    }

    // ─── Result Structure ─────────────────────────────────────────

    public function test_gate_result_contains_expected_keys(): void
    {
        $gate = $this->createGate('contact_count', 3);

        $results = $this->service->evaluateGates($this->partner, $this->stage1->id, $this->stage2->id);
        $result = $results->first();

        $this->assertArrayHasKey('gate_id', $result);
        $this->assertArrayHasKey('requirement_key', $result);
        $this->assertArrayHasKey('requirement_label', $result);
        $this->assertArrayHasKey('threshold', $result);
        $this->assertArrayHasKey('unit', $result);
        $this->assertArrayHasKey('comparison', $result);
        $this->assertArrayHasKey('actual_value', $result);
        $this->assertArrayHasKey('met', $result);
        $this->assertEquals($gate->id, $result['gate_id']);
    }
}
