<?php

namespace Tests\Feature\Api\V1;

use App\Models\Journey;
use App\Models\JourneyParticipant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JourneyRuntimeControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $agentUser;

    private Journey $journey;

    private string $writeToken;

    private string $readToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agentUser = User::factory()->create();
        $this->journey = Journey::factory()->create(['journey_key' => 'ifp-delivery-finance']);

        $this->writeToken = $this->agentUser
            ->createToken('journey-agent:ifp-delivery-finance', ['journey:read', 'journey:write'])
            ->plainTextToken;

        $this->readToken = $this->agentUser
            ->createToken('journey-agent:ifp-delivery-finance', ['journey:read'])
            ->plainTextToken;
    }

    private function withAgentToken(string $token): self
    {
        return $this->withHeader('Authorization', 'Bearer '.$token);
    }

    public function test_store_participant_creates_record_and_initial_stage_history(): void
    {
        $response = $this->withAgentToken($this->writeToken)->postJson(
            "/api/v1/journeys/{$this->journey->journey_key}/participants",
            [
                'name' => 'Jane Doe',
                'email' => 'jane@example.com',
                'external_id' => 'ext-1',
            ]
        );

        $response->assertCreated();
        $response->assertJsonPath('data.name', 'Jane Doe');
        $response->assertJsonPath('data.current_stage', 1);

        $this->assertDatabaseHas('journey_participants', [
            'journey_id' => $this->journey->id,
            'name' => 'Jane Doe',
            'external_id' => 'ext-1',
        ]);

        $this->assertDatabaseHas('journey_stage_history', [
            'from_stage' => null,
            'to_stage' => 1,
            'changed_by' => 'enrollment',
        ]);
    }

    public function test_storing_same_external_id_upserts_participant(): void
    {
        $first = $this->withAgentToken($this->writeToken)->postJson(
            "/api/v1/journeys/{$this->journey->journey_key}/participants",
            ['name' => 'Jane', 'external_id' => 'ext-1']
        );

        $second = $this->withAgentToken($this->writeToken)->postJson(
            "/api/v1/journeys/{$this->journey->journey_key}/participants",
            ['name' => 'Jane Renamed', 'external_id' => 'ext-1']
        );

        $second->assertCreated();
        $this->assertSame(
            $first->json('data.id'),
            $second->json('data.id'),
        );
        $this->assertSame(1, JourneyParticipant::query()->where('external_id', 'ext-1')->count());
    }

    public function test_transition_records_history_and_updates_stage(): void
    {
        $participant = JourneyParticipant::factory()->create([
            'journey_id' => $this->journey->id,
            'current_stage' => 2,
        ]);

        $response = $this->withAgentToken($this->writeToken)->postJson(
            "/api/v1/journeys/{$this->journey->journey_key}/participants/{$participant->id}/transition",
            [
                'to_stage' => 3,
                'changed_by' => 'agent',
                'note' => 'Completed credentialization',
            ]
        );

        $response->assertOk();
        $response->assertJsonPath('data.current_stage', 3);

        $this->assertDatabaseHas('journey_stage_history', [
            'journey_participant_id' => $participant->id,
            'from_stage' => 2,
            'to_stage' => 3,
            'changed_by' => 'agent',
        ]);
    }

    public function test_read_token_cannot_write(): void
    {
        $response = $this->withAgentToken($this->readToken)->postJson(
            "/api/v1/journeys/{$this->journey->journey_key}/participants",
            ['name' => 'Jane']
        );

        $response->assertForbidden();
    }

    public function test_read_token_can_list_participants(): void
    {
        JourneyParticipant::factory()->count(2)->create(['journey_id' => $this->journey->id]);

        $response = $this->withAgentToken($this->readToken)->getJson(
            "/api/v1/journeys/{$this->journey->journey_key}/participants"
        );

        $response->assertOk();
        $response->assertJsonCount(2, 'data');
    }

    public function test_token_for_other_journey_is_rejected(): void
    {
        $otherJourney = Journey::factory()->create(['journey_key' => 'other-journey']);

        $response = $this->withAgentToken($this->writeToken)->getJson(
            "/api/v1/journeys/{$otherJourney->journey_key}/participants"
        );

        $response->assertForbidden();
    }

    public function test_unauthenticated_request_is_rejected(): void
    {
        $response = $this->getJson(
            "/api/v1/journeys/{$this->journey->journey_key}/participants"
        );

        $response->assertUnauthorized();
    }

    public function test_complete_pending_action_marks_status_complete(): void
    {
        $action = \App\Models\JourneyPendingAction::query()->create([
            'journey_id' => $this->journey->id,
            'action' => 'review_submission',
            'description' => 'Review the participant submission',
            'assigned_to' => 'agent',
            'status' => 'pending',
        ]);

        $response = $this->withAgentToken($this->writeToken)->patchJson(
            "/api/v1/journeys/{$this->journey->journey_key}/pending-actions/{$action->id}/complete"
        );

        $response->assertOk();
        $response->assertJsonPath('data.status', 'complete');
        $this->assertNotNull($action->fresh()->completed_at);
    }
}
