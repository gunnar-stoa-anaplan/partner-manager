<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Opportunity Snapshots (Historical tracking)
        Schema::create('opportunity_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opportunity_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('import_id')->nullable()->constrained()->nullOnDelete();
            $table->date('snapshot_date')->index();
            $table->string('opportunity_name');
            $table->string('account_name')->nullable();
            $table->foreignId('partner_id')->nullable()->constrained()->nullOnDelete();
            $table->string('stage')->nullable();
            $table->decimal('amount', 15, 2)->nullable();
            $table->date('close_date')->nullable();
            $table->string('fiscal_quarter')->nullable();
            $table->integer('fiscal_year')->nullable();
            $table->string('type')->nullable();
            $table->string('lead_source')->nullable();
            $table->decimal('probability', 5, 2)->nullable();
            $table->string('owner')->nullable();
            $table->timestamps();

            $table->index(['opportunity_id', 'snapshot_date']);
        });

        // Resource Snapshots (Historical tracking)
        Schema::create('resource_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained()->cascadeOnDelete();
            $table->foreignId('import_id')->nullable()->constrained()->nullOnDelete();
            $table->date('snapshot_date')->index();
            $table->string('program')->index();
            $table->string('role')->nullable();
            $table->integer('started_count')->default(0);
            $table->integer('completed_count')->default(0);
            $table->integer('total_count')->default(0);
            $table->integer('unique_people_count')->default(0);
            $table->timestamps();

            $table->index(['partner_id', 'snapshot_date']);
            $table->index(['partner_id', 'program', 'snapshot_date']);
            $table->unique(['partner_id', 'import_id', 'program', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resource_snapshots');
        Schema::dropIfExists('opportunity_snapshots');
    }
};
