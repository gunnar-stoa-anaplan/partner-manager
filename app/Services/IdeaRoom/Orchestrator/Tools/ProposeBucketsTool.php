<?php

namespace App\Services\IdeaRoom\Orchestrator\Tools;

use App\Enums\IdeaRoom\EventSource;
use App\Enums\IdeaRoom\EventType;
use App\Models\IdeaRoom\Bucket;
use App\Models\IdeaRoom\Idea;
use App\Models\IdeaRoom\IdeaEvent;
use App\Models\User;
use App\Services\IdeaRoom\Orchestrator\Contracts\ExecutesTool;
use App\Services\IdeaRoom\Orchestrator\ToolResult;

class ProposeBucketsTool implements ExecutesTool
{
    public function execute(User $user, array $arguments): ToolResult
    {
        $idea = Idea::query()
            ->where('user_id', $user->id)
            ->where('uuid', $arguments['idea_uuid'])
            ->first();

        if (! $idea) {
            return new ToolResult(
                tool: 'propose_buckets',
                success: false,
                data: [],
                error: "Idea not found with UUID: {$arguments['idea_uuid']}",
            );
        }

        $attached = [];

        foreach ($arguments['proposals'] as $proposal) {
            $parentBucket = null;

            if (! empty($proposal['parent_bucket_name'])) {
                $parentBucket = Bucket::query()->firstOrCreate(
                    ['user_id' => $user->id, 'name' => $proposal['parent_bucket_name'], 'parent_bucket_id' => null],
                    ['sort_order' => 0, 'is_default' => false, 'depth' => 0],
                );
            }

            $bucket = Bucket::query()->firstOrCreate(
                [
                    'user_id' => $user->id,
                    'name' => $proposal['bucket_name'],
                    'parent_bucket_id' => $parentBucket?->id,
                ],
                [
                    'sort_order' => 0,
                    'is_default' => false,
                    'depth' => $parentBucket ? 1 : 0,
                ],
            );

            $confidence = min(1.0, max(0.0, (float) $proposal['confidence']));

            $idea->buckets()->syncWithoutDetaching([
                $bucket->id => [
                    'confidence' => $confidence,
                    'confirmed' => false,
                    'created_at' => now(),
                ],
            ]);

            $attached[] = [
                'bucket_name' => $bucket->name,
                'bucket_uuid' => $bucket->uuid,
                'parent_bucket_name' => $parentBucket?->name,
                'parent_bucket_uuid' => $parentBucket?->uuid,
                'full_path' => $bucket->fullPath(),
                'confidence' => $confidence,
            ];
        }

        IdeaEvent::query()->create([
            'idea_id' => $idea->id,
            'user_id' => $user->id,
            'event_type' => EventType::BucketProposed->value,
            'source' => EventSource::Orchestrator->value,
            'payload' => ['proposals' => $attached],
        ]);

        return new ToolResult(
            tool: 'propose_buckets',
            success: true,
            data: ['proposals' => $attached],
        );
    }
}
