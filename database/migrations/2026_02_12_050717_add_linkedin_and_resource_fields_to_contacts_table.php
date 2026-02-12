<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            // LinkedIn fields
            $table->string('linkedin_url')->nullable()->after('notes');
            $table->string('linkedin_profile_id')->nullable()->after('linkedin_url');
            $table->string('linkedin_headline')->nullable()->after('linkedin_profile_id');
            $table->string('linkedin_image_url')->nullable()->after('linkedin_headline');
            $table->timestamp('linkedin_synced_at')->nullable()->after('linkedin_image_url');

            // Resource role (for contact_type = 'resource')
            $table->string('resource_role')->nullable()->after('linkedin_synced_at');
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn([
                'linkedin_url',
                'linkedin_profile_id',
                'linkedin_headline',
                'linkedin_image_url',
                'linkedin_synced_at',
                'resource_role',
            ]);
        });
    }
};
