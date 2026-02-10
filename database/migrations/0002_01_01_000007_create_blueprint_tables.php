<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blueprints', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('category', ['application', 'product']);
            $table->string('product_area')->nullable();
            $table->text('description')->nullable();
            $table->string('version')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('blueprint_outcomes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blueprint_id')->constrained('blueprints')->cascadeOnDelete();
            $table->unsignedInteger('sort_order');
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['blueprint_id', 'sort_order']);
        });

        Schema::create('blueprint_content_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blueprint_outcome_id')->constrained('blueprint_outcomes')->cascadeOnDelete();
            $table->unsignedInteger('sort_order');
            $table->string('title');
            $table->string('url')->nullable();
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->string('duration_display')->nullable();
            $table->string('source')->nullable();
            $table->string('prerequisites')->nullable();
            $table->enum('gtm_applicable', ['required', 'optional', 'na'])->default('na');
            $table->enum('pre_sales_applicable', ['required', 'optional', 'na'])->default('na');
            $table->enum('delivery_applicable', ['required', 'optional', 'na'])->default('na');
            $table->timestamps();

            $table->index(['blueprint_outcome_id', 'sort_order']);
        });

        Schema::create('partner_blueprint_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained('partners')->cascadeOnDelete();
            $table->foreignId('blueprint_id')->constrained('blueprints')->cascadeOnDelete();
            $table->enum('role', ['gtm', 'pre_sales', 'delivery']);
            $table->foreignId('assigned_by')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['active', 'completed', 'paused'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['partner_id', 'blueprint_id', 'role'], 'assignment_partner_blueprint_role_unique');
            $table->index('partner_id');
            $table->index('blueprint_id');
        });

        Schema::create('blueprint_enrollments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('partner_blueprint_assignment_id');
            $table->foreign('partner_blueprint_assignment_id', 'enrollments_assignment_fk')
                ->references('id')->on('partner_blueprint_assignments')->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained('contacts')->cascadeOnDelete();
            $table->foreignId('enrolled_by')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['enrolled', 'in_progress', 'completed'])->default('enrolled');
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['partner_blueprint_assignment_id', 'contact_id'], 'enrollment_assignment_contact_unique');
        });

        Schema::create('blueprint_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blueprint_enrollment_id')->constrained('blueprint_enrollments')->cascadeOnDelete();
            $table->foreignId('blueprint_content_item_id')->constrained('blueprint_content_items')->cascadeOnDelete();
            $table->boolean('completed')->default(false);
            $table->dateTime('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['blueprint_enrollment_id', 'blueprint_content_item_id'], 'progress_enrollment_item_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blueprint_progress');
        Schema::dropIfExists('blueprint_enrollments');
        Schema::dropIfExists('partner_blueprint_assignments');
        Schema::dropIfExists('blueprint_content_items');
        Schema::dropIfExists('blueprint_outcomes');
        Schema::dropIfExists('blueprints');
    }
};
