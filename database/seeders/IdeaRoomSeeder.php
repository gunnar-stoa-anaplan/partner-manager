<?php

namespace Database\Seeders;

use App\Enums\IdeaRoom\EventSource;
use App\Enums\IdeaRoom\EventType;
use App\Enums\IdeaRoom\LifecycleState;
use App\Models\IdeaRoom\Bucket;
use App\Models\IdeaRoom\Edge;
use App\Models\IdeaRoom\Idea;
use App\Models\IdeaRoom\IdeaEvent;
use App\Models\IdeaRoom\Room;
use App\Models\IdeaRoom\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class IdeaRoomSeeder extends Seeder
{
    /**
     * Seed the Idea Room with sample ideas, buckets, tags, edges, and rooms.
     */
    public function run(): void
    {
        $user = User::where('email', 'gunnar.stoa@gmail.com')->first()
            ?? User::query()->first();

        if (! $user) {
            $this->command->warn('No user found — skipping IdeaRoomSeeder.');

            return;
        }

        if (Idea::where('user_id', $user->id)->exists()) {
            $this->command->info('Idea Room data already exists for '.$user->name.' — skipping.');

            return;
        }

        // Create parent buckets
        $parentBucketData = [
            ['name' => 'personal', 'color' => '#a855f7', 'icon' => 'heart'],
            ['name' => 'business', 'color' => '#f59e0b', 'icon' => 'briefcase'],
            ['name' => 'engineering', 'color' => '#22c55e', 'icon' => 'wrench'],
            ['name' => 'creative', 'color' => '#ec4899', 'icon' => 'paint-brush'],
            ['name' => 'research', 'color' => '#06b6d4', 'icon' => 'magnifying-glass'],
            ['name' => 'inbox', 'color' => '#71717a', 'icon' => 'inbox'],
        ];

        $buckets = [];
        foreach ($parentBucketData as $data) {
            $buckets[$data['name']] = Bucket::query()->firstOrCreate(
                ['user_id' => $user->id, 'name' => $data['name'], 'parent_bucket_id' => null],
                ['color' => $data['color'], 'icon' => $data['icon'], 'sort_order' => 0, 'is_default' => $data['name'] === 'inbox', 'depth' => 0],
            );
        }

        // Create child buckets
        $childBucketData = [
            ['parent' => 'personal', 'name' => 'health', 'color' => '#a855f7'],
            ['parent' => 'personal', 'name' => 'travel', 'color' => '#a855f7'],
            ['parent' => 'personal', 'name' => 'home tasks', 'color' => '#a855f7'],
            ['parent' => 'personal', 'name' => 'learning', 'color' => '#a855f7'],
            ['parent' => 'business', 'name' => 'product ideas', 'color' => '#f59e0b'],
            ['parent' => 'business', 'name' => 'side projects', 'color' => '#f59e0b'],
            ['parent' => 'business', 'name' => 'revenue', 'color' => '#f59e0b'],
            ['parent' => 'engineering', 'name' => 'tools', 'color' => '#22c55e'],
            ['parent' => 'engineering', 'name' => 'open source', 'color' => '#22c55e'],
            ['parent' => 'creative', 'name' => 'writing', 'color' => '#ec4899'],
            ['parent' => 'creative', 'name' => 'design', 'color' => '#ec4899'],
            ['parent' => 'research', 'name' => 'ml experiments', 'color' => '#06b6d4'],
        ];

        foreach ($childBucketData as $data) {
            $parent = $buckets[$data['parent']];
            $key = $data['parent'].'>'.$data['name'];
            $buckets[$key] = Bucket::query()->firstOrCreate(
                ['user_id' => $user->id, 'name' => $data['name'], 'parent_bucket_id' => $parent->id],
                ['color' => $data['color'], 'sort_order' => 0, 'is_default' => false, 'depth' => 1],
            );
        }

        // Create tags
        $tagNames = [
            'ai', 'ml', 'api', 'ux', 'mobile', 'web', 'automation', 'analytics',
            'collaboration', 'personal', 'health', 'travel', 'home', 'reading',
            'writing', 'side-project', 'saas', 'open-source', 'design', 'data',
        ];

        $tags = [];
        foreach ($tagNames as $name) {
            $tags[$name] = Tag::query()->firstOrCreate(
                ['user_id' => $user->id, 'name' => $name],
            );
        }

        // Define ideas with relationships
        $ideaDefinitions = [
            [
                'title' => 'Voice-controlled smart home dashboard',
                'original_text' => 'Build a dashboard that aggregates all smart home devices and allows voice control through a single interface. Could integrate with Home Assistant and use local LLMs for privacy.',
                'state' => LifecycleState::Growing,
                'buckets' => [['name' => 'business>product ideas', 'confidence' => 0.9], ['name' => 'engineering>tools', 'confidence' => 0.85]],
                'tags' => ['ai', 'automation', 'ux', 'side-project'],
            ],
            [
                'title' => 'AI-powered recipe generator from fridge contents',
                'original_text' => 'Take a photo of your fridge, AI identifies ingredients, suggests recipes based on what you have. Could factor in dietary preferences and expiration dates.',
                'state' => LifecycleState::Spark,
                'buckets' => [['name' => 'business>product ideas', 'confidence' => 0.8], ['name' => 'creative', 'confidence' => 0.7]],
                'tags' => ['ai', 'mobile', 'side-project'],
            ],
            [
                'title' => 'Weekly journaling habit with AI reflection',
                'original_text' => 'Create a journaling app that uses AI to surface patterns in mood, productivity, and themes over time. Weekly summaries with insights.',
                'state' => LifecycleState::Developed,
                'buckets' => [['name' => 'personal>health', 'confidence' => 0.95], ['name' => 'business>product ideas', 'confidence' => 0.6]],
                'tags' => ['ai', 'writing', 'health', 'personal'],
            ],
            [
                'title' => 'Open-source API rate limiter with sliding window',
                'original_text' => 'Build a Redis-backed rate limiter package that supports sliding window, token bucket, and leaky bucket algorithms. Publish as open source.',
                'state' => LifecycleState::Growing,
                'buckets' => [['name' => 'engineering>open source', 'confidence' => 0.95], ['name' => 'engineering>tools', 'confidence' => 0.7]],
                'tags' => ['api', 'open-source', 'side-project'],
            ],
            [
                'title' => 'Passive income through niche newsletter',
                'original_text' => 'Start a weekly newsletter about AI tools for small businesses. Monetize through sponsorships and affiliate links. Research shows 40% open rates in this niche.',
                'state' => LifecycleState::Spark,
                'buckets' => [['name' => 'business>revenue', 'confidence' => 0.9], ['name' => 'creative>writing', 'confidence' => 0.7]],
                'tags' => ['ai', 'writing', 'saas'],
            ],
            [
                'title' => 'Learn Rust by building a CLI tool',
                'original_text' => 'Pick up Rust by building a practical CLI tool - maybe a git log visualizer or a markdown-to-slide converter. Focus on ownership model and error handling.',
                'state' => LifecycleState::Spark,
                'buckets' => [['name' => 'personal>learning', 'confidence' => 0.9], ['name' => 'engineering>tools', 'confidence' => 0.6]],
                'tags' => ['side-project', 'open-source'],
            ],
            [
                'title' => 'Collaborative whiteboard with AI suggestions',
                'original_text' => 'Real-time whiteboard where teams brainstorm, and AI suggests connections between sticky notes, groups related concepts, and proposes next steps.',
                'state' => LifecycleState::Growing,
                'buckets' => [['name' => 'business>product ideas', 'confidence' => 0.95], ['name' => 'creative>design', 'confidence' => 0.8]],
                'tags' => ['ai', 'collaboration', 'ux', 'design'],
            ],
            [
                'title' => 'Automate monthly expense categorization',
                'original_text' => 'Script that pulls bank transactions via Plaid, categorizes them using AI, and generates a monthly report. Send to Notion or Google Sheets.',
                'state' => LifecycleState::Developed,
                'buckets' => [['name' => 'personal', 'confidence' => 0.85], ['name' => 'engineering>tools', 'confidence' => 0.7]],
                'tags' => ['automation', 'ai', 'personal', 'data'],
            ],
            [
                'title' => 'Photography road trip through Norway',
                'original_text' => 'Plan a 10-day road trip through the Lofoten Islands and northern Norway. Focus on landscape photography, Northern Lights if timing works. Research camper van rentals.',
                'state' => LifecycleState::Spark,
                'buckets' => [['name' => 'personal>travel', 'confidence' => 0.95], ['name' => 'creative', 'confidence' => 0.6]],
                'tags' => ['travel', 'personal', 'reading'],
            ],
            [
                'title' => 'Micro-SaaS: screenshot annotation tool',
                'original_text' => 'Simple web tool to annotate screenshots with arrows, text, blur sensitive areas. No account needed, generates shareable link. Freemium model.',
                'state' => LifecycleState::Spark,
                'buckets' => [['name' => 'business>side projects', 'confidence' => 0.9], ['name' => 'business>revenue', 'confidence' => 0.85]],
                'tags' => ['saas', 'web', 'design', 'side-project'],
            ],
            [
                'title' => 'ML model for predicting code review time',
                'original_text' => 'Train a model on git history to predict how long a PR will take to review based on diff size, files changed, author history, and description quality.',
                'state' => LifecycleState::Spark,
                'buckets' => [['name' => 'research>ml experiments', 'confidence' => 0.9], ['name' => 'engineering', 'confidence' => 0.85]],
                'tags' => ['ml', 'data', 'analytics', 'ai'],
            ],
            [
                'title' => 'Build a reading list tracker with progress API',
                'original_text' => 'App to track books being read, reading progress, notes per chapter. Expose an API so I can show currently reading on my personal site.',
                'state' => LifecycleState::Growing,
                'buckets' => [['name' => 'personal>learning', 'confidence' => 0.8], ['name' => 'business>side projects', 'confidence' => 0.7]],
                'tags' => ['reading', 'api', 'side-project', 'web'],
            ],
        ];

        $ideas = [];
        foreach ($ideaDefinitions as $def) {
            $idea = Idea::query()->create([
                'user_id' => $user->id,
                'title' => $def['title'],
                'original_text' => $def['original_text'],
                'lifecycle_state' => $def['state']->value,
                'last_activity_at' => now()->subHours(rand(1, 720)),
            ]);

            IdeaEvent::query()->create([
                'idea_id' => $idea->id,
                'user_id' => $user->id,
                'event_type' => EventType::Created->value,
                'source' => EventSource::Orchestrator->value,
                'payload' => ['title' => $idea->title],
            ]);

            // Attach buckets
            foreach ($def['buckets'] as $bucketDef) {
                if (isset($buckets[$bucketDef['name']])) {
                    $idea->buckets()->attach($buckets[$bucketDef['name']]->id, [
                        'confidence' => $bucketDef['confidence'],
                        'confirmed' => $bucketDef['confidence'] >= 0.9,
                        'created_at' => now(),
                    ]);
                }
            }

            // Attach tags
            foreach ($def['tags'] as $tagName) {
                if (isset($tags[$tagName])) {
                    $idea->tags()->attach($tags[$tagName]->id, [
                        'source' => EventSource::Orchestrator->value,
                        'created_at' => now(),
                    ]);
                }
            }

            // Create a Room for growing/developed ideas
            if (in_array($def['state'], [LifecycleState::Growing, LifecycleState::Developed])) {
                Room::query()->create([
                    'idea_id' => $idea->id,
                    'user_id' => $user->id,
                    'name' => $idea->title,
                    'status' => 'active',
                    'agent_persona' => 'creative-partner',
                    'agent_config' => ['temperature' => 0.8],
                ]);
            }

            $ideas[] = $idea;
        }

        // Create edges (connections between related ideas)
        $edgeDefinitions = [
            [0, 7, 'related', 0.7, 'Both involve home automation and personal productivity'],
            [1, 7, 'related', 0.6, 'Both use AI for personal life optimization'],
            [2, 7, 'supports', 0.8, 'Journaling insights could include expense patterns'],
            [3, 6, 'supports', 0.7, 'Rate limiter could be used in the whiteboard API'],
            [4, 9, 'related', 0.75, 'Both are micro-business / passive income ideas'],
            [5, 3, 'related', 0.6, 'Learning Rust could help build the rate limiter'],
            [6, 10, 'related', 0.65, 'AI suggestions in whiteboard relate to ML code review predictions'],
            [0, 1, 'related', 0.5, 'Both involve AI-powered interfaces for physical world'],
            [9, 4, 'supports', 0.7, 'Screenshot tool could be promoted through the newsletter'],
            [11, 2, 'related', 0.6, 'Reading tracker and journal both support personal reflection'],
        ];

        foreach ($edgeDefinitions as $edgeDef) {
            Edge::query()->create([
                'source_idea_id' => $ideas[$edgeDef[0]]->id,
                'target_idea_id' => $ideas[$edgeDef[1]]->id,
                'edge_type' => $edgeDef[2],
                'status' => 'proposed',
                'proposed_by' => 'orchestrator',
                'confidence' => $edgeDef[3],
                'reason' => $edgeDef[4],
            ]);
        }

        $this->command->info("Seeded Idea Room for {$user->name}: ".count($ideas).' ideas, '.count($edgeDefinitions).' edges, '.count($parentBucketData).' parent buckets, '.count($childBucketData).' child buckets, '.count($tagNames).' tags.');
    }
}
