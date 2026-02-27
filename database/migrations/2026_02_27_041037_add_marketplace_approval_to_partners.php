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
        Schema::table('partners', function (Blueprint $table) {
            $table->string('marketplace_status')->default('draft')->after('company_description');
            $table->foreignId('marketplace_reviewed_by')->nullable()->after('marketplace_status')
                ->constrained('users')->nullOnDelete();
            $table->datetime('marketplace_reviewed_at')->nullable()->after('marketplace_reviewed_by');
            $table->text('marketplace_rejection_reason')->nullable()->after('marketplace_reviewed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->dropForeign(['marketplace_reviewed_by']);
            $table->dropColumn([
                'marketplace_status',
                'marketplace_reviewed_by',
                'marketplace_reviewed_at',
                'marketplace_rejection_reason',
            ]);
        });
    }
};
