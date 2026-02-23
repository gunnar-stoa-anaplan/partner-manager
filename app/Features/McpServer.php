<?php

namespace App\Features;

use Laravel\Pennant\Attributes\Name;

#[Name('mcp_server')]
class McpServer
{
    /**
     * Resolve the feature's initial value.
     */
    public function resolve(mixed $scope): bool
    {
        return false;
    }
}
