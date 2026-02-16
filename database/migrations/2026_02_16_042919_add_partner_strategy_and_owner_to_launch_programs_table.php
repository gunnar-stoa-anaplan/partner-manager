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
        Schema::table('launch_programs', function (Blueprint $table) {
            $table->string('partner_strategy')->default('include')->after('max_partners');
            $table->text('exclusion_reason')->nullable()->after('partner_strategy');
            $table->foreignId('owner_id')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('launch_programs', function (Blueprint $table) {
            $table->dropForeign(['owner_id']);
            $table->dropColumn(['partner_strategy', 'exclusion_reason', 'owner_id']);
        });
    }
};
