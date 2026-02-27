<?php

namespace Tests\Feature;

use App\Ai\Agents\ProductLine\FinanceExpert;
use App\Ai\Agents\ProductLine\SupplyChainExpert;
use App\Models\User;
use App\Models\ZoomChatResponder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Tests\TestCase;

class ZoomChatResponderTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        Feature::for(null)->activate('zoom_chat');
    }

    public function test_index_lists_responders(): void
    {
        ZoomChatResponder::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)->get(route('zoom_chat.responders.index'));

        $response->assertOk();
        $response->assertViewHas('responders');
    }

    public function test_create_shows_form(): void
    {
        $response = $this->actingAs($this->admin)->get(route('zoom_chat.responders.create'));

        $response->assertOk();
        $response->assertSee('New Chat Responder');
    }

    public function test_store_creates_responder(): void
    {
        $response = $this->actingAs($this->admin)->post(route('zoom_chat.responders.store'), [
            'zoom_meeting_id' => '85012345678',
            'agent_class' => SupplyChainExpert::class,
            'display_name' => 'SCM Bot',
            'trigger_prefix' => '@scm',
            'max_response_tokens' => 512,
            'rate_limit_per_minute' => 10,
        ]);

        $response->assertRedirect(route('zoom_chat.responders.index'));

        $this->assertDatabaseHas('zoom_chat_responders', [
            'zoom_meeting_id' => '85012345678',
            'agent_class' => SupplyChainExpert::class,
            'display_name' => 'SCM Bot',
            'trigger_prefix' => '@scm',
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_store_validates_agent_class(): void
    {
        $response = $this->actingAs($this->admin)->post(route('zoom_chat.responders.store'), [
            'zoom_meeting_id' => '85012345678',
            'agent_class' => 'App\\Invalid\\Agent',
            'display_name' => 'Bot',
            'trigger_prefix' => '@bot',
            'max_response_tokens' => 512,
            'rate_limit_per_minute' => 10,
        ]);

        $response->assertSessionHasErrors('agent_class');
    }

    public function test_show_displays_responder(): void
    {
        $responder = ZoomChatResponder::factory()->create();

        $response = $this->actingAs($this->admin)->get(route('zoom_chat.responders.show', $responder));

        $response->assertOk();
        $response->assertSee($responder->display_name);
    }

    public function test_update_modifies_responder(): void
    {
        $responder = ZoomChatResponder::factory()->create();

        $response = $this->actingAs($this->admin)->put(route('zoom_chat.responders.update', $responder), [
            'zoom_meeting_id' => $responder->zoom_meeting_id,
            'agent_class' => FinanceExpert::class,
            'display_name' => 'Finance Bot',
            'trigger_prefix' => '@finance',
            'max_response_tokens' => 256,
            'rate_limit_per_minute' => 5,
        ]);

        $response->assertRedirect(route('zoom_chat.responders.show', $responder));

        $responder->refresh();
        $this->assertEquals(FinanceExpert::class, $responder->agent_class);
        $this->assertEquals('Finance Bot', $responder->display_name);
    }

    public function test_destroy_deletes_responder(): void
    {
        $responder = ZoomChatResponder::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route('zoom_chat.responders.destroy', $responder));

        $response->assertRedirect(route('zoom_chat.responders.index'));
        $this->assertDatabaseMissing('zoom_chat_responders', ['id' => $responder->id]);
    }

    public function test_toggle_switches_active_status(): void
    {
        $responder = ZoomChatResponder::factory()->create(['is_active' => false]);

        $response = $this->actingAs($this->admin)->postJson(route('zoom_chat.responders.toggle', $responder));

        $response->assertOk();
        $response->assertJson(['is_active' => true]);

        $this->assertTrue($responder->fresh()->is_active);
    }

    public function test_toggle_deactivates_active_responder(): void
    {
        $responder = ZoomChatResponder::factory()->create(['is_active' => true]);

        $response = $this->actingAs($this->admin)->postJson(route('zoom_chat.responders.toggle', $responder));

        $response->assertOk();
        $response->assertJson(['is_active' => false]);

        $this->assertFalse($responder->fresh()->is_active);
    }
}
