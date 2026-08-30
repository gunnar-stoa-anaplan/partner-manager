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
        $workshop = Workshop::factory()->create();

        $this->actingAs($this->coe)->post(route('workshops.credentials.store', $workshop), [
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

    public function test_admin_can_extend_a_credential_assignment_hold(): void
    {
        $session = WorkshopSession::factory()->create();
        $credential = WorkshopCredential::factory()->create(['workshop_id' => $session->workshop_id]);
        $contact = Contact::factory()->create();

        $assignment = $credential->assignments()->create([
            'workshop_session_id' => $session->id,
            'contact_id' => $contact->id,
            'assigned_at' => now(),
            'hold_until' => now()->addDay(),
        ]);

        $newDate = now()->addWeek()->format('Y-m-d');
        $this->actingAs($this->coe)->post(route('workshops.credentials.assignments.extend', $assignment), [
            'hold_until' => $newDate,
        ]);

        $this->assertEquals($newDate, $assignment->fresh()->hold_until->format('Y-m-d'));
    }

    public function test_scheduled_command_releases_expired_credential_assignments(): void
    {
        $session = WorkshopSession::factory()->create();
        $credential = WorkshopCredential::factory()->create(['workshop_id' => $session->workshop_id]);
        $expired = $credential->assignments()->create([
            'workshop_session_id' => $session->id,
            'contact_id' => Contact::factory()->create()->id,
            'assigned_at' => now()->subWeek(),
            'hold_until' => now()->subDay(),
        ]);
        $stillHeld = $credential->assignments()->create([
            'workshop_session_id' => $session->id,
            'contact_id' => Contact::factory()->create()->id,
            'assigned_at' => now(),
            'hold_until' => now()->addWeek(),
        ]);

        $this->artisan('workshops:release-expired-credentials')->assertSuccessful();

        $this->assertNotNull($expired->fresh()->released_at);
        $this->assertEquals('session_ended', $expired->fresh()->released_reason);
        $this->assertNull($stillHeld->fresh()->released_at);
    }

    public function test_admin_can_bulk_import_credentials_from_csv(): void
    {
        $workshop = Workshop::factory()->create();
        $csv = "username,email,password,notes\nsandbox01,sandbox01@demo.com,SecretPass1,First batch\n";
        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('credentials.csv', $csv);

        $this->actingAs($this->coe)->post(route('workshops.credentials.bulk-upload', $workshop), [
            'file' => $file,
        ]);

        $this->assertDatabaseHas('workshop_credential_pool', [
            'workshop_id' => $workshop->id,
            'username' => 'sandbox01',
            'email' => 'sandbox01@demo.com',
        ]);
    }

    public function test_refresh_queue_lists_never_and_stale_refreshed_credentials(): void
    {
        $fresh = WorkshopCredential::factory()->create(['last_refreshed_at' => now()]);
        $stale = WorkshopCredential::factory()->create(['last_refreshed_at' => now()->subDays(45)]);
        $never = WorkshopCredential::factory()->create(['last_refreshed_at' => null]);

        $response = $this->actingAs($this->coe)->get(route('workshops.credentials.refresh-queue'));

        $response->assertOk();
        $response->assertSee($stale->username);
        $response->assertSee($never->username);
        $response->assertDontSee($fresh->username);
    }

    public function test_marking_a_credential_refreshed_updates_the_timestamp(): void
    {
        $credential = WorkshopCredential::factory()->create(['last_refreshed_at' => null]);

        $this->actingAs($this->coe)->post(route('workshops.credentials.mark-refreshed', $credential));

        $this->assertNotNull($credential->fresh()->last_refreshed_at);
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
