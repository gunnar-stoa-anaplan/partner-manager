<?php

namespace App\Services\IdeaRoom\Orchestrator\Room;

class RoomToolRegistry
{
    /**
     * @return array<int, array{type: string, function: array}>
     */
    public function definitions(): array
    {
        return [
            $this->saveArtifact(),
            $this->updateArtifact(),
            $this->addNote(),
            $this->updateIdeaState(),
            $this->saveMemory(),
            $this->setObjective(),
        ];
    }

    private function saveArtifact(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => 'save_artifact',
                'description' => 'Create a new artifact for this idea room.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'type' => [
                            'type' => 'string',
                            'enum' => ['note', 'outline', 'prd', 'checklist', 'document'],
                            'description' => 'The type of artifact to create',
                        ],
                        'title' => [
                            'type' => 'string',
                            'description' => 'A concise title for the artifact',
                        ],
                        'content' => [
                            'type' => 'string',
                            'description' => 'The full content of the artifact (markdown supported)',
                        ],
                    ],
                    'required' => ['type', 'title', 'content'],
                    'additionalProperties' => false,
                ],
            ],
        ];
    }

    private function updateArtifact(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => 'update_artifact',
                'description' => 'Update the content of an existing artifact. A version snapshot is saved automatically.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'artifact_uuid' => [
                            'type' => 'string',
                            'description' => 'The UUID of the artifact to update',
                        ],
                        'content' => [
                            'type' => 'string',
                            'description' => 'The new content for the artifact',
                        ],
                    ],
                    'required' => ['artifact_uuid', 'content'],
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
                'description' => 'Add a note to the idea associated with this room.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'note' => [
                            'type' => 'string',
                            'description' => 'The note content to add',
                        ],
                    ],
                    'required' => ['note'],
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
                'description' => 'Transition the idea to a new lifecycle state.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
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
                    'required' => ['new_state'],
                    'additionalProperties' => false,
                ],
            ],
        ];
    }

    private function saveMemory(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => 'save_memory',
                'description' => 'Save important information to long-term memory.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'memory_type' => [
                            'type' => 'string',
                            'enum' => ['fact', 'decision', 'assumption', 'open_question'],
                            'description' => 'The type of memory to save',
                        ],
                        'content' => [
                            'type' => 'string',
                            'description' => 'The memory content to persist',
                        ],
                        'importance' => [
                            'type' => 'integer',
                            'description' => 'Importance score from 1 (low) to 10 (critical)',
                        ],
                    ],
                    'required' => ['memory_type', 'content'],
                    'additionalProperties' => false,
                ],
            ],
        ];
    }

    private function setObjective(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => 'set_objective',
                'description' => 'Set or update the session objective.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'objective' => [
                            'type' => 'string',
                            'description' => 'The objective or goal for this session',
                        ],
                    ],
                    'required' => ['objective'],
                    'additionalProperties' => false,
                ],
            ],
        ];
    }
}
