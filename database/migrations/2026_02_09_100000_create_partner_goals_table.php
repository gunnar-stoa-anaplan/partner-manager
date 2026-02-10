<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partner_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('fiscal_year');
            $table->decimal('sourced_nnacv_target', 15, 2)->default(0);
            $table->decimal('influenced_nnacv_target', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['partner_id', 'fiscal_year'], 'partner_goals_partner_fy_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_goals');
    }
};
