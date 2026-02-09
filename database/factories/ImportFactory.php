<?php

namespace Database\Factories;

use App\Models\Import;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ImportFactory extends Factory
{
    protected $model = Import::class;

    public function definition(): array
    {
        $failedRows = $this->faker->numberBetween(0, 5);

        return [
            'type' => $this->faker->randomElement(['opportunities', 'resources', 'psm']),
            'file_name' => $this->faker->uuid() . '_import.xlsx',
            'file_path' => 'imports/' . $this->faker->uuid() . '_import.xlsx',
            'status' => 'completed',
            'user_id' => User::factory(),
            'started_at' => now(),
            'completed_at' => now()->addMinutes($this->faker->numberBetween(1, 5)),
            'total_rows' => $this->faker->numberBetween(50, 500),
            'successful_rows' => $this->faker->numberBetween(45, 495),
            'failed_rows' => $failedRows,
            'partners_created' => 0,
            'partners_updated' => 0,
            'opportunities_created' => 0,
            'opportunities_updated' => 0,
            'resources_created' => 0,
            'error_log' => $failedRows > 0 ? $this->generateSampleErrors($failedRows) : null,
            'summary' => [],
        ];
    }

    /**
     * Generate sample error logs for failed imports.
     */
    private function generateSampleErrors(int $count): string
    {
        $errors = [];
        $errorTypes = [
            'Invalid date format in \'close_date\' column',
            'Missing required field \'opportunity_name\'',
            'Missing required field \'partner_name\'',
            'Invalid amount format',
            'Partner not found',
            'Duplicate opportunity name',
            'Invalid stage value',
            'Close date cannot be in the past',
            'Amount must be greater than 0',
            'Invalid email format for contact',
            'Missing required certification type',
            'Invalid resource status',
            'Partner name exceeds maximum length',
            'Invalid fiscal quarter format',
        ];

        for ($i = 0; $i < min($count, 100); $i++) {
            $rowNumber = $this->faker->numberBetween(2, 500);
            $error = $this->faker->randomElement($errorTypes);
            $errors[] = "Row {$rowNumber}: {$error}";
        }

        return implode("\n", $errors);
    }

    /**
     * Indicate that the import is for opportunities.
     */
    public function opportunities(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'opportunities',
            'partners_created' => $this->faker->numberBetween(0, 10),
            'partners_updated' => $this->faker->numberBetween(5, 50),
            'opportunities_created' => $this->faker->numberBetween(10, 50),
            'opportunities_updated' => $this->faker->numberBetween(20, 100),
            'resources_created' => 0,
        ]);
    }

    /**
     * Indicate that the import is for resources.
     */
    public function resources(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'resources',
            'partners_created' => 0,
            'partners_updated' => $this->faker->numberBetween(5, 20),
            'opportunities_created' => 0,
            'opportunities_updated' => 0,
            'resources_created' => $this->faker->numberBetween(100, 500),
        ]);
    }

    /**
     * Indicate that the import is for PSM assignments.
     */
    public function psm(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'psm',
            'partners_created' => 0,
            'partners_updated' => $this->faker->numberBetween(5, 20),
            'opportunities_created' => 0,
            'opportunities_updated' => 0,
            'resources_created' => 0,
        ]);
    }

    /**
     * Set a specific date for the import.
     */
    public function onDate($date): static
    {
        return $this->state(fn (array $attributes) => [
            'started_at' => $date,
            'completed_at' => \Carbon\Carbon::parse($date)->addMinutes($this->faker->numberBetween(1, 5)),
        ]);
    }
}
