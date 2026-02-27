<?php

namespace App\Services\IdeaRoom\Orchestrator;

class ToolRegistry
{
    /**
     * @return array<int, array{type: string, function: array}>
     */
    public function definitions(): array
    {
        return [
            $this->createIdea(),
            $this->proposeBuckets(),
            $this->updateIdeaState(),
            $this->searchIdeas(),
            $this->addNote(),
            $this->suggestTriage(),
        ];
    }

    private function createIdea(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => 'create_idea',
                'description' => 'Create a new idea from the user\'s input. Use when the user describes a new idea, concept, or thought they want to capture.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'title' => [
                            'type' => 'string',
                            'description' => 'A concise title for the idea (max 500 chars)',
                        ],
                        'original_text' => [
                            'type' => 'string',
                            'description' => 'The full text of the idea as described by the user',
                        ],
                        'tags' => [
                            'type' => 'array',
                            'items' => ['type' => 'string'],
                            'description' => 'Suggested tags for the idea (lowercase, short)',
                        ],
                    ],
                    'required' => ['title', 'original_text'],
                    'additionalProperties' => false,
                ],
            ],
        ];
    }

    private function proposeBuckets(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => 'propose_buckets',
                'description' => 'Propose which buckets an idea belongs to, with confidence scores. Supports hierarchical grouping via optional parent_bucket_name.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'idea_uuid' => [
                            'type' => 'string',
                            'description' => 'The UUID of the idea to categorize',
                        ],
                        'proposals' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'bucket_name' => [
                                        'type' => 'string',
                                        'description' => 'Name of the bucket',
                                    ],
                                    'parent_bucket_name' => [
                                        'type' => 'string',
                                        'description' => 'Optional parent bucket name for hierarchical grouping',
                                    ],
                                    'confidence' => [
                                        'type' => 'number',
                                        'description' => 'Confidence from 0.0 to 1.0',
                                    ],
                                ],
                                'required' => ['bucket_name', 'confidence'],
                                'additionalProperties' => false,
                            ],
                            'description' => 'List of bucket proposals with confidence',
                        ],
                    ],
                    'required' => ['idea_uuid', 'proposals'],
                    'additionalProperties' => false,
                ],
            ],
        ];
    }

    private function updateIdeaState(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => 'update_idea_state',
                'description' => 'Transition an idea to a new lifecycle state. Valid states: spark, growing, developed, archived, dropped.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'idea_uuid' => [
                            'type' => 'string',
                            'description' => 'The UUID of the idea to update',
                        ],
                        'new_state' => [
                            'type' => 'string',
                            'enum' => ['spark', 'growing', 'developed', 'archived', 'dropped'],
                            'description' => 'The new lifecycle state',
                        ],
                        'reason' => [
                            'type' => 'string',
                            'description' => 'Why the state is being changed',
                        ],
                    ],
                    'required' => ['idea_uuid', 'new_state'],
                    'additionalProperties' => false,
                ],
            ],
        ];
    }

    private function searchIdeas(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => 'search_ideas',
                'description' => 'Search through the user\'s ideas by keyword.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'query' => [
                            'type' => 'string',
                            'description' => 'Search keywords to match against idea titles and text',
                        ],
                        'lifecycle_state' => [
                            'type' => 'string',
                            'enum' => ['spark', 'growing', 'developed', 'archived', 'dropped'],
                            'description' => 'Optional: filter by lifecycle state',
                        ],
                        'limit' => [
                            'type' => 'integer',
                            'description' => 'Maximum results to return (default 10)',
                        ],
                    ],
                    'required' => ['query'],
                    'additionalProperties' => false,
                ],
            ],
        ];
    }

    private function suggestTriage(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => 'suggest_triage',
                'description' => 'After creating new ideas, call this to trigger a visual triage experience. ALWAYS call this after creating 1 or more ideas.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'suggestions' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'idea_uuid' => [
                                        'type' => 'string',
                                        'description' => 'UUID of the newly created idea',
                                    ],
                                    'emoji' => [
                                        'type' => 'string',
                                        'description' => 'A single emoji that visually represents this idea',
                                    ],
                                    'suggested_room_uuid' => [
                                        'type' => 'string',
                                        'description' => 'UUID of an existing room to pre-route this idea to',
                                    ],
                                ],
                                'required' => ['idea_uuid', 'emoji'],
                                'additionalProperties' => false,
                            ],
                        ],
                    ],
                    'required' => ['suggestions'],
                    'additionalProperties' => false,
                ],
            ],
        ];
    }

    private function addNote(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => 'add_note',
                'description' => 'Add a note or additional context to an existing idea.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'idea_uuid' => [
                            'type' => 'string',
                            'description' => 'The UUID of the idea to add a note to',
                        ],
                        'note' => [
                            'type' => 'string',
                            'description' => 'The note content to add',
                        ],
                    ],
                    'required' => ['idea_uuid', 'note'],
                    'additionalProperties' => false,
                ],
            ],
        ];
    }
}
