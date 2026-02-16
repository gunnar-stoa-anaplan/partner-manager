<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class PortalImpersonationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $psm;

    private Partner $partner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->psm = User::factory()->create(['role' => 'psm']);
        $this->partner = Partner::factory()->create(['user_id' => $this->psm->id]);
    }

    private function createPortalContact(string $role = 'admin', ?Partner $partner = null): Contact
    {
        $partner = $partner ?? $this->partner;

        return Contact::factory()->{'portal'.ucfirst($role)}()->create([
            'partner_id' => $partner->id,
        ]);
    }

    // ─── Portal Users Tab ─────────────────────────────────────────

    public function test_psm_can_view_portal_users_tab(): void
    {
        $contact = Contact::factory()->create(['partner_id' => $this->partner->id]);

        $response = $this->actingAs($this->psm)->get(
            route('partners.tab', [$this->partner->id, 'portal-users'])
        );

        $response->assertOk();
        $response->assertSee($contact->full_name);
    }

    public function test_admin_can_view_any_partners_portal_users_tab(): void
    {
        $otherPsm = User::factory()->create(['role' => 'psm']);
        $otherPartner = Partner::factory()->create(['user_id' => $otherPsm->id]);

        $response = $this->actingAs($this->admin)->get(
            route('partners.tab', [$otherPartner->id, 'portal-users'])
        );

        $response->assertOk();
    }

    public function test_psm_cannot_view_other_partners_portal_users_tab(): void
    {
        $otherPsm = User::factory()->create(['role' => 'psm']);
        $otherPartner = Partner::factory()->create(['user_id' => $otherPsm->id]);

        $response = $this->actingAs($this->psm)->get(
            route('partners.tab', [$otherPartner->id, 'portal-users'])
        );

        $response->assertForbidden();
    }

    // ─── Toggle Access ────────────────────────────────────────────

    public function test_psm_can_toggle_portal_access_on(): void
    {
        $contact = Contact::factory()->create([
            'partner_id' => $this->partner->id,
            'portal_access' => false,
        ]);

        $response = $this->actingAs($this->psm)->patch(
            route('partners.portal-users.toggle-access', [$this->partner, $contact])
        );

        $response->assertRedirect();
        $this->assertTrue($contact->fresh()->portal_access);
    }

    public function test_psm_can_toggle_portal_access_off(): void
    {
        $contact = Contact::factory()->portalAdmin()->create([
            'partner_id' => $this->partner->id,
        ]);

        $response = $this->actingAs($this->psm)->patch(
            route('partners.portal-users.toggle-access', [$this->partner, $contact])
        );

        $response->assertRedirect();
        $this->assertFalse($contact->fresh()->portal_access);
    }

    public function test_toggle_access_rejects_contact_from_other_partner(): void
    {
        $otherPartner = Partner::factory()->create(['user_id' => $this->psm->id]);
        $contact = Contact::factory()->create(['partner_id' => $otherPartner->id]);

        $response = $this->actingAs($this->psm)->patch(
            route('partners.portal-users.toggle-access', [$this->partner, $contact])
        );

        $response->assertNotFound();
    }

    // ─── Update Role ──────────────────────────────────────────────

    public function test_psm_can_update_portal_role(): void
    {
        $contact = Contact::factory()->create([
            'partner_id' => $this->partner->id,
            'portal_role' => null,
        ]);

        $response = $this->actingAs($this->psm)->patch(
            route('partners.portal-users.update-role', [$this->partner, $contact]),
            ['portal_role' => 'sales']
        );

        $response->assertRedirect();
        $this->assertEquals('sales', $contact->fresh()->portal_role);
    }

    public function test_update_role_rejects_invalid_role(): void
    {
        $contact = Contact::factory()->create(['partner_id' => $this->partner->id]);

        $response = $this->actingAs($this->psm)->patch(
            route('partners.portal-users.update-role', [$this->partner, $contact]),
            ['portal_role' => 'superadmin']
        );

        $response->assertSessionHasErrors('portal_role');
    }

    public function test_update_role_can_clear_role(): void
    {
        $contact = Contact::factory()->portalAdmin()->create([
            'partner_id' => $this->partner->id,
        ]);

        $response = $this->actingAs($this->psm)->patch(
            route('partners.portal-users.update-role', [$this->partner, $contact]),
            ['portal_role' => null]
        );

        $response->assertRedirect();
        $this->assertNull($contact->fresh()->portal_role);
    }

    // ─── Set Password ─────────────────────────────────────────────

    public function test_psm_can_set_password(): void
    {
        $contact = Contact::factory()->create(['partner_id' => $this->partner->id]);

        $response = $this->actingAs($this->psm)->patch(
            route('partners.portal-users.set-password', [$this->partner, $contact]),
            ['password' => 'newpassword123', 'password_confirmation' => 'newpassword123']
        );

        $response->assertRedirect();
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('newpassword123', $contact->fresh()->password));
    }

    public function test_set_password_fails_without_confirmation(): void
    {
        $contact = Contact::factory()->create(['partner_id' => $this->partner->id]);

        $response = $this->actingAs($this->psm)->patch(
            route('partners.portal-users.set-password', [$this->partner, $contact]),
            ['password' => 'newpassword123', 'password_confirmation' => 'wrong']
        );

        $response->assertSessionHasErrors('password');
    }

    public function test_set_password_fails_when_too_short(): void
    {
        $contact = Contact::factory()->create(['partner_id' => $this->partner->id]);

        $response = $this->actingAs($this->psm)->patch(
            route('partners.portal-users.set-password', [$this->partner, $contact]),
            ['password' => 'short', 'password_confirmation' => 'short']
        );

        $response->assertSessionHasErrors('password');
    }

    // ─── Impersonation Start ──────────────────────────────────────

    public function test_admin_can_impersonate_any_contact(): void
    {
        $otherPsm = User::factory()->create(['role' => 'psm']);
        $otherPartner = Partner::factory()->create(['user_id' => $otherPsm->id]);
        $contact = $this->createPortalContact('admin', $otherPartner);

        $response = $this->actingAs($this->admin)->post(
            route('portal.impersonate.start', $contact)
        );

        $response->assertRedirect(route('portal.dashboard'));
        $this->assertTrue(session('impersonating_portal'));
        $this->assertEquals($this->admin->id, session('impersonator_user_id'));
        $this->assertEquals($otherPartner->id, session('impersonation_partner_id'));
        $this->assertTrue(Auth::guard('portal')->check());
        $this->assertEquals($contact->id, Auth::guard('portal')->id());
    }

    public function test_psm_can_impersonate_own_partner_contact(): void
    {
        $contact = $this->createPortalContact('admin');

        $response = $this->actingAs($this->psm)->post(
            route('portal.impersonate.start', $contact)
        );

        $response->assertRedirect(route('portal.dashboard'));
        $this->assertTrue(session('impersonating_portal'));
        $this->assertEquals($this->psm->id, session('impersonator_user_id'));
    }

    public function test_psm_cannot_impersonate_other_partners_contact(): void
    {
        $otherPsm = User::factory()->create(['role' => 'psm']);
        $otherPartner = Partner::factory()->create(['user_id' => $otherPsm->id]);
        $contact = $this->createPortalContact('admin', $otherPartner);

        $response = $this->actingAs($this->psm)->post(
            route('portal.impersonate.start', $contact)
        );

        $response->assertForbidden();
    }

    public function test_cannot_impersonate_contact_without_portal_access(): void
    {
        $contact = Contact::factory()->create([
            'partner_id' => $this->partner->id,
            'portal_access' => false,
            'portal_role' => 'partner_admin',
        ]);

        $response = $this->actingAs($this->psm)->post(
            route('portal.impersonate.start', $contact)
        );

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertFalse(session()->has('impersonating_portal'));
    }

    public function test_cannot_impersonate_contact_without_portal_role(): void
    {
        $contact = Contact::factory()->create([
            'partner_id' => $this->partner->id,
            'portal_access' => true,
            'portal_role' => null,
        ]);

        $response = $this->actingAs($this->psm)->post(
            route('portal.impersonate.start', $contact)
        );

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    // ─── Impersonation Stop ───────────────────────────────────────

    public function test_stop_impersonation_clears_session(): void
    {
        $contact = $this->createPortalContact('admin');

        // Start impersonation
        $this->actingAs($this->psm)->post(route('portal.impersonate.start', $contact));

        // Stop impersonation
        $response = $this->actingAs($this->psm)->post(route('portal.impersonate.stop'));

        $response->assertRedirect(route('partners.show', $this->partner->id));
        $this->assertFalse(session()->has('impersonating_portal'));
        $this->assertFalse(session()->has('impersonator_user_id'));
        $this->assertFalse(Auth::guard('portal')->check());
    }

    // ─── Portal Auth Middleware ───────────────────────────────────

    public function test_impersonated_session_bypasses_portal_access_check(): void
    {
        $contact = Contact::factory()->create([
            'partner_id' => $this->partner->id,
            'portal_access' => false,
            'portal_role' => 'partner_admin',
            'password' => 'password',
        ]);

        // Manually simulate impersonation session
        Auth::guard('portal')->login($contact);

        $response = $this->actingAs($this->psm)
            ->withSession([
                'impersonating_portal' => true,
                'impersonator_user_id' => $this->psm->id,
                'impersonator_user_name' => $this->psm->name,
                'impersonation_partner_id' => $this->partner->id,
            ])
            ->actingAs($contact, 'portal')
            ->get(route('portal.dashboard'));

        $response->assertOk();
    }

    // ─── Portal Logout During Impersonation ───────────────────────

    public function test_portal_logout_during_impersonation_redirects_to_stop(): void
    {
        $contact = $this->createPortalContact('admin');

        // Start impersonation
        $this->actingAs($this->psm)->post(route('portal.impersonate.start', $contact));

        // Try to logout via portal
        $response = $this->actingAs($contact, 'portal')
            ->withSession([
                'impersonating_portal' => true,
                'impersonator_user_id' => $this->psm->id,
                'impersonation_partner_id' => $this->partner->id,
            ])
            ->post(route('portal.logout'));

        $response->assertRedirect(route('portal.impersonate.stop'));
    }
}
