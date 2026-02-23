<?php

namespace App\Services;

class IngestResult
{
    public int $created = 0;

    public int $updated = 0;

    public int $failed = 0;

    public int $total = 0;

    /** @var array<int, string> */
    public array $errors = [];

    public ?int $importId = null;

    public int $partnersCreated = 0;

    public function addError(int $rowIndex, string $message): void
    {
        if (count($this->errors) < 50) {
            $this->errors[] = "Row {$rowIndex}: {$message}";
        }
    }
}
