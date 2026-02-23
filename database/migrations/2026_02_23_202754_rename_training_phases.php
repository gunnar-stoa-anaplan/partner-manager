<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Rename training phases: watch → tell, try → show.
     */
    public function up(): void
    {
        DB::table('activation_steps')->where('phase', 'watch')->update(['phase' => 'tell']);
        DB::table('activation_steps')->where('phase', 'try')->update(['phase' => 'show']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('activation_steps')->where('phase', 'tell')->update(['phase' => 'watch']);
        DB::table('activation_steps')->where('phase', 'show')->update(['phase' => 'try']);
    }
};
