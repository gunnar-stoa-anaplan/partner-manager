<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enablement_sessions', function (Blueprint $table) {
            $table->foreignId('launch_program_id')->nullable()->after('created_by')
                ->constrained('launch_programs')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('enablement_sessions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('launch_program_id');
        });
    }
};
