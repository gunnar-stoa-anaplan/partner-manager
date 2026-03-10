<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('enablement_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // PSM who owns the target
            $table->string('category'); // config key: sales, pre_sales, delivery_innovation, delivery_apps
            $table->unsignedInteger('target')->default(0);
            $table->unsignedInteger('baseline')->default(0);
            $table->timestamps();

            $table->unique(['partner_id', 'user_id', 'category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enablement_goals');
    }
};
