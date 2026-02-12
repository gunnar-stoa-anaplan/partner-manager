<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('opportunities', function (Blueprint $table) {
            $table->string('previous_stage')->nullable()->after('stage');
            $table->date('previous_close_date')->nullable()->after('close_date');
            $table->dateTime('first_seen_at')->nullable()->after('import_id');
        });
    }

    public function down(): void
    {
        Schema::table('opportunities', function (Blueprint $table) {
            $table->dropColumn(['previous_stage', 'previous_close_date', 'first_seen_at']);
        });
    }
};
