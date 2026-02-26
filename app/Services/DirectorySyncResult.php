<?php

namespace App\Services;

readonly class DirectorySyncResult
{
    /**
     * @param  array<int, string>  $errors
     */
    public function __construct(
        public int $created = 0,
        public int $updated = 0,
        public int $skipped = 0,
        public array $errors = [],
    ) {}

    public function total(): int
    {
        return $this->created + $this->updated + $this->skipped;
    }

    public function hasErrors(): bool
    {
        return count($this->errors) > 0;
    }
}
