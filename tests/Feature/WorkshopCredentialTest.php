<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\User;
use App\Models\Workshop;
use App\Models\WorkshopCredential;
use App\Models\WorkshopSession;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Tests\TestCase;

class WorkshopCredentialTest extends TestCase
{
    use RefreshDatabase;

    private User $coe;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        Feature::for(null)->activate('workshops');

        $this->coe = User::factory()->coe()->create();
    }

    public function test_credential_password_is_encrypted_at_rest(): void
    {
        $this->actingAs($this->coe)->post(route('workshops.credentials.store'), [
            'workshop_id' => Workshop::factory()->create()->id,
            'email' => 'sandbox@example.com',
            'username' => 'sandbox_user',
            'password' => 'plaintext-secret',
        ]);

        $credential = WorkshopCredential::firstWhere('username', 'sandbox_user');

        $this->assertNotNull($credential);
        $this->assertEquals('plaintext-secret', $credential->password_enc);
        $this->assertDatabaseMissing('workshop_credential_pool', ['password_enc' => 'plaintext-secret']);
    }

    public function test_credential_can_be_assigned_and_released(): void
    {
        $session = WorkshopSession::factory()->create();
        $credential = WorkshopCredential::factory()->create(['workshop_id' => $session->workshop_id]);
        $contact = Contact::factory()->create();

        $this->actingAs($this->coe)->post(route('workshops.credentials.assign', $credential), [
            'workshop_session_id' => $session->id,
            'contact_id' => $contact->id,
        ]);

        $this->assertFalse($credential->fresh()->isAvailable());
        $assignment = $credential->fresh()->activeAssignment();
        $this->assertNotNull($assignment);
        $this->assertEquals($contact->id, $assignment->contact_id);

        $this->actingAs($this->coe)->post(route('workshops.credentials.assignments.release', $assignment));

        $this->assertTrue($credential->fresh()->isAvailable());
    }

    public function test_an_already_assigned_credential_cannot_be_double_assigned(): void
    {
        $session = WorkshopSession::factory()->create();
        $credential = WorkshopCredential::factory()->create(['workshop_id' => $session->workshop_id]);
        $contactA = Contact::factory()->create();
        $contactB = Contact::factory()->create();

        $credential->assignments()->create([
            'workshop_session_id' => $session->id,
            'contact_id' => $contactA->id,
            'assigned_at' => now(),
        ]);

        $this->actingAs($this->coe)->post(route('workshops.credentials.assign', $credential), [
            'workshop_session_id' => $session->id,
            'contact_id' => $contactB->id,
        ]);

        $this->assertDatabaseMissing('workshop_credential_assignments', ['contact_id' => $contactB->id]);
    }
}
