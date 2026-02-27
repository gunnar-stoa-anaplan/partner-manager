<?php

namespace App\Services\IdeaRoom\Orchestrator\Room;

use App\Models\IdeaRoom\Room;
use App\Models\User;

class RoomSystemPromptBuilder
{
    public function build(Room $room, User $user): string
    {
        $idea = $room->idea()->with(['buckets.parent', 'tags'])->first();

        $notes = $idea->notes()
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn ($event) => $event->payload['note'] ?? '')
            ->filter()
            ->values()
            ->all();

        $memories = $room->memories()
            ->where('is_active', true)
            ->orderByDesc('importance')
            ->limit(config('idea_room.room_agent.max_memories_in_context', 30))
            ->get();

        $artifacts = $room->artifacts()->get(['uuid', 'title', 'type']);

        $objective = $room->agent_config['objective'] ?? null;

        $ideaContext = $this->buildIdeaContext($idea);
        $notesContext = $this->buildNotesContext($notes);
        $memoriesContext = $this->buildMemoriesContext($memories);
        $artifactsContext = $this->buildArtifactsContext($artifacts);
        $objectiveContext = $objective
            ? "Current session objective: {$objective}"
            : 'No session objective set yet. Consider defining one with `set_objective`.';

        return <<<PROMPT
        You are a challenging brainstorming partner inside an Idea Room. Your role is NOT to be agreeable — it is to push the user toward clarity, rigor, and breakthrough thinking.

        ## Your Personality
        - You challenge weak assumptions and vague thinking
        - You ask probing, uncomfortable questions that force deeper reasoning
        - You celebrate genuine breakthroughs and strong insights
        - You push the user to define clear objectives when the conversation lacks direction
        - You do NOT just agree with everything — you play devil's advocate when needed
        - You are intellectually honest: if an idea has holes, you say so directly

        ## Your Capabilities
        - Create artifacts when ideas crystallize using `save_artifact`
        - Update existing artifacts as they evolve using `update_artifact`
        - Add notes to the parent idea using `add_note`
        - Transition the idea's lifecycle state using `update_idea_state`
        - Save key facts, decisions, assumptions to memory using `save_memory`
        - Set or update the session objective using `set_objective`

        ## Idea Context
        {$ideaContext}

        ## Notes History
        {$notesContext}

        ## Memories
        {$memoriesContext}

        ## Artifacts
        {$artifactsContext}

        ## Objective
        {$objectiveContext}
        PROMPT;
    }

    private function buildIdeaContext($idea): string
    {
        $buckets = $idea->buckets->map(function ($bucket) {
            $parent = $bucket->parent ? "{$bucket->parent->name} > " : '';

            return $parent.$bucket->name;
        })->implode(', ');

        $tags = $idea->tags->pluck('name')->implode(', ');

        return implode("\n", array_filter([
            "Title: {$idea->title}",
            "State: {$idea->lifecycle_state}",
            $idea->original_text ? "Original text: {$idea->original_text}" : null,
            $buckets ? "Buckets: {$buckets}" : null,
            $tags ? "Tags: {$tags}" : null,
        ]));
    }

    /**
     * @param  array<int, string>  $notes
     */
    private function buildNotesContext(array $notes): string
    {
        if (empty($notes)) {
            return 'No notes yet.';
        }

        return collect($notes)
            ->map(fn (string $note, int $index) => ($index + 1).". {$note}")
            ->implode("\n");
    }

    private function buildMemoriesContext($memories): string
    {
        if ($memories->isEmpty()) {
            return 'No memories saved yet.';
        }

        return $memories
            ->map(fn ($memory) => "[{$memory->memory_type}] (importance: {$memory->importance}) {$memory->content}")
            ->implode("\n");
    }

    private function buildArtifactsContext($artifacts): string
    {
        if ($artifacts->isEmpty()) {
            return 'No artifacts created yet.';
        }

        return $artifacts
            ->map(fn ($artifact) => "- {$artifact->title} ({$artifact->type}) [uuid: {$artifact->uuid}]")
            ->implode("\n");
    }
}
