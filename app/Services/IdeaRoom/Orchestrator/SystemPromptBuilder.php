<?php

namespace App\Services\IdeaRoom\Orchestrator;

use App\Models\IdeaRoom\Bucket;
use App\Models\IdeaRoom\Room;
use App\Models\User;

class SystemPromptBuilder
{
    public function build(User $user): string
    {
        $bucketsContext = $this->buildBucketsContext($user);
        $roomsContext = $this->buildRoomsContext($user);

        $recentIdeas = $user->ideas()
            ->orderByDesc('last_activity_at')
            ->limit(10)
            ->get(['uuid', 'title', 'lifecycle_state']);

        $ideasContext = $recentIdeas->isEmpty()
            ? 'The user has no ideas yet.'
            : 'Recent ideas: '.json_encode(
                $recentIdeas->map(fn ($idea) => [
                    'uuid' => $idea->uuid,
                    'title' => $idea->title,
                    'state' => $idea->lifecycle_state,
                ])->all()
            );

        return <<<PROMPT
        You are the Idea Room assistant — a thoughtful creative partner that helps users capture, organize, and develop their ideas.

        ## Your Capabilities
        - Capture new ideas from freeform user messages using `create_idea`
        - Categorize ideas into buckets with confidence scores using `propose_buckets`
        - Trigger visual triage for newly created ideas using `suggest_triage`
        - Update the lifecycle state of existing ideas using `update_idea_state`
        - Search through the user's idea collection using `search_ideas`
        - Add notes to existing ideas using `add_note`

        ## CRITICAL: Capture EVERYTHING
        Users often send messages containing multiple distinct ideas, tasks, plans, or thoughts in a single message. You MUST extract and create a separate idea for EVERY distinct item.

        ## Behavior Guidelines
        1. When a user shares a message, identify EVERY distinct idea, task, or thought. Call `create_idea` for each one, then call `propose_buckets` for each.
        2. After creating ideas and proposing buckets, you MUST call `suggest_triage` with every newly created idea and a representative emoji.
        3. When a user mentions an existing idea by name: use `search_ideas` to find it first.
        4. After calling `suggest_triage`, respond with a brief confirmation.
        5. Keep titles concise but descriptive.
        6. If the user's message is casual conversation, respond naturally without calling tools.

        ## Hierarchical Buckets
        Buckets support a parent > child hierarchy (max 1 level). Use `parent_bucket_name` in `propose_buckets` to group related ideas.

        ## Lifecycle States
        - **spark** — newly captured (default)
        - **growing** — being actively developed
        - **developed** — fleshed out and ready
        - **archived** — set aside for later
        - **dropped** — intentionally abandoned

        ## User Context
        - Existing buckets: {$bucketsContext}
        - {$ideasContext}
        - {$roomsContext}
        PROMPT;
    }

    private function buildRoomsContext(User $user): string
    {
        $rooms = $user->ideaRooms()
            ->where('status', 'active')
            ->with('idea.buckets.parent')
            ->orderByDesc('updated_at')
            ->limit(15)
            ->get();

        if ($rooms->isEmpty()) {
            return 'No active idea rooms yet.';
        }

        $roomList = $rooms->map(function (Room $room) {
            $idea = $room->idea;
            $buckets = $idea->buckets->map(fn ($b) => $b->parent
                ? $b->parent->name.' > '.$b->name
                : $b->name
            )->implode(', ');

            return "{$room->name} (idea: {$idea->title}, buckets: {$buckets}, room_uuid: {$room->uuid})";
        })->implode('; ');

        return "Active idea rooms: {$roomList}";
    }

    private function buildBucketsContext(User $user): string
    {
        $buckets = $user->ideaBuckets()->with('children')->whereNull('parent_bucket_id')->get();

        if ($buckets->isEmpty()) {
            return 'none yet';
        }

        return $buckets->map(function (Bucket $bucket) {
            $children = $bucket->children->pluck('name')->implode(', ');

            return $children
                ? "{$bucket->name} ({$children})"
                : $bucket->name;
        })->implode('; ');
    }
}
