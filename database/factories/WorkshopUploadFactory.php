<?php

namespace Database\Factories;

use App\Models\WorkshopThread;
use App\Models\WorkshopUpload;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkshopUpload>
 */
class WorkshopUploadFactory extends Factory
{
    protected $model = WorkshopUpload::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workshop_thread_id' => WorkshopThread::factory(),
            'filename' => 'attachment.pdf',
            'stored_path' => 'workshop-uploads/test.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 1024,
        ];
    }
}
