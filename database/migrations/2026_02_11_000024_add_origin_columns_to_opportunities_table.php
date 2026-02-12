<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('opportunities', function (Blueprint $table) {
            $table->string('origin')->nullable();
            $table->string('win_loss_reason')->nullable();
            $table->string('lost_to_competitor')->nullable();
        });

        Schema::table('opportunity_snapshots', function (Blueprint $table) {
            $table->string('origin')->nullable();
            $table->string('win_loss_reason')->nullable();
            $table->string('lost_to_competitor')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('opportunities', function (Blueprint $table) {
            $table->dropColumn(['origin', 'win_loss_reason', 'lost_to_competitor']);
        });

        Schema::table('opportunity_snapshots', function (Blueprint $table) {
            $table->dropColumn(['origin', 'win_loss_reason', 'lost_to_competitor']);
        });
    }
};
