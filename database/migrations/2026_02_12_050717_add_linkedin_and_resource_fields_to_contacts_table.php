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
            $table->string('linkedin_url')->nullable();
            $table->string('linkedin_profile_id')->nullable();
            $table->string('linkedin_headline')->nullable();
            $table->string('linkedin_image_url')->nullable();
            $table->timestamp('linkedin_synced_at')->nullable();

            // Resource role (for contact_type = 'resource')
            $table->string('resource_role')->nullable();
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
