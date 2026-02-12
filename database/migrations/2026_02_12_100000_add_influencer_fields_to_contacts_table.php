<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->string('contact_type')->default('general')->after('avatar');
            $table->string('influencer_role')->nullable()->after('contact_type');
            $table->string('organization')->nullable()->after('influencer_role');
            $table->text('notes')->nullable()->after('organization');
            $table->index('contact_type');
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropIndex(['contact_type']);
            $table->dropColumn(['contact_type', 'influencer_role', 'organization', 'notes']);
        });
    }
};
