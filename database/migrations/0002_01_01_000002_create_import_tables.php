<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Imports
        Schema::create('imports', function (Blueprint $table) {
            $table->id();
            $table->string('type')->index();
            $table->string('file_name');
            $table->string('file_path');
            $table->integer('total_rows')->default(0);
            $table->integer('successful_rows')->default(0);
            $table->integer('failed_rows')->default(0);
            $table->integer('partners_created')->default(0);
            $table->integer('partners_updated')->default(0);
            $table->integer('opportunities_created')->default(0);
            $table->integer('opportunities_updated')->default(0);
            $table->integer('resources_created')->default(0);
            $table->string('status')->default('pending')->index();
            $table->longText('error_log')->nullable();
            $table->json('summary')->nullable();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        // Opportunities
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->string('salesforce_id')->unique()->nullable();
            $table->string('opportunity_name');
            $table->string('account_name')->nullable();
            $table->foreignId('partner_id')->nullable()->constrained()->nullOnDelete();
            $table->string('stage')->nullable();
            $table->decimal('amount', 15, 2)->nullable();
            $table->date('close_date')->nullable()->index();
            $table->string('fiscal_quarter', 10)->nullable()->index();
            $table->integer('fiscal_year')->nullable()->index();
            $table->string('type')->nullable();
            $table->text('description')->nullable();
            $table->string('lead_source')->nullable();
            $table->decimal('probability', 5, 2)->nullable();
            $table->string('owner')->nullable();
            $table->json('excel_data')->nullable();
            $table->foreignId('import_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        // Resources (Certifications)
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('role')->nullable()->index();
            $table->string('program')->index();
            $table->string('status')->index();
            $table->date('date_certified')->nullable();
            $table->foreignId('import_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resources');
        Schema::dropIfExists('opportunities');
        Schema::dropIfExists('imports');
    }
};
