<?php

namespace App\Services\IdeaRoom\Orchestrator\Contracts;

use App\Models\User;
use App\Services\IdeaRoom\Orchestrator\ToolResult;

interface ExecutesTool
{
    public function execute(User $user, array $arguments): ToolResult;
}
