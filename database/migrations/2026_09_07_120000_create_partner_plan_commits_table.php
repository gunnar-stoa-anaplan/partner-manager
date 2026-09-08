<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-partner Business Plan headcount commit: how many certified people the
 * partner commits to for each role (GTM / PreSales / Delivery). Drives the
 * coverage / to-assign figures on the Business Plan screen.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partner_plan_commits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained()->cascadeOnDelete();
            $table->string('role');
            $table->unsignedInteger('committed')->default(0);
            $table->timestamps();
            $table->unique(['partner_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_plan_commits');
    }
};
