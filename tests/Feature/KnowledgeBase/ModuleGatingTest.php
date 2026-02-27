<?php

namespace Tests\Feature\KnowledgeBase;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModuleGatingTest extends TestCase
{
    use RefreshDatabase;

    public function test_knowledge_base_dashboard_requires_authentication(): void
    {
        $response = $this->get('/knowledge-base');

        $response->assertRedirect(route('login'));
    }

    public function test_knowledge_base_returns_403_when_module_disabled(): void
    {
        config(['modules.knowledge_base.enabled' => false]);

        $user = User::factory()->psm()->create();

        $response = $this->actingAs($user)->get('/knowledge-base');

        $response->assertForbidden();
    }

    public function test_knowledge_base_documents_requires_authentication(): void
    {
        $response = $this->get('/knowledge-base/documents');

        $response->assertRedirect(route('login'));
    }

    public function test_knowledge_base_ingest_page_requires_authentication(): void
    {
        $response = $this->get('/knowledge-base/ingest');

        $response->assertRedirect(route('login'));
    }
}
