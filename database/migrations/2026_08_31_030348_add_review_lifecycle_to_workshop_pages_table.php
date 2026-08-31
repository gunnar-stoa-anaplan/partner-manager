<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('workshop_pages', function (Blueprint $table) {
            $table->timestamp('published_at')->nullable()->after('superseded_at');
            $table->foreignId('published_by')->nullable()->after('published_at')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable()->after('published_by');
            $table->foreignId('rejected_by')->nullable()->after('rejected_at')
                ->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable()->after('rejected_by');
        });

        // Every row that exists already was, by definition, live content
        // before this review gate existed - backfill so it doesn't
        // disappear from Workshop::pages() once that relation requires
        // published_at to be set.
        DB::table('workshop_pages')->whereNull('published_at')->update([
            'published_at' => DB::raw('created_at'),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workshop_pages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('published_by');
            $table->dropConstrainedForeignId('rejected_by');
            $table->dropColumn(['published_at', 'rejected_at', 'rejection_reason']);
        });
    }
};
