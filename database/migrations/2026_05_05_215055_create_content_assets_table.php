<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_assets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', [
                'pitch_deck',
                'icp',
                'datasheet',
                'faq',
                'positioning',
                'seismic_course',
                'consensus_demo',
                'feature_demo',
                'technical_doc',
                'academy_course',
            ]);
            $table->enum('lob', ['Finance', 'HR', 'RPM', 'Retail', 'SCM', 'Platform']);
            $table->string('application')->nullable();
            $table->enum('persona', ['sales', 'pre_sales', 'delivery', 'all']);
            $table->string('url', 1024)->nullable();
            $table->string('owner_name')->nullable();
            $table->string('secondary_owner_name')->nullable();
            $table->enum('status', ['available', 'partial', 'not_available', 'deprecated'])->default('not_available');
            $table->enum('core_app', ['yes', 'no', 'na'])->nullable();
            $table->string('launch_year', 16)->nullable();
            $table->boolean('is_priority_need')->default(false);
            $table->boolean('is_launching_in_progress')->default(false);
            $table->timestamp('validated_at')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['lob', 'persona']);
            $table->index(['application', 'persona']);
            $table->index(['type', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_assets');
    }
};
