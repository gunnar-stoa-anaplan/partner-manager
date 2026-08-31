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
        // The old (workshop_id, slug) unique index is what satisfies InnoDB's
        // "the FK column needs an index" requirement for workshop_id, since
        // it's the composite index's leftmost column. The replacement
        // composite unique must exist BEFORE the old one is dropped, or
        // MySQL refuses the drop with "needed in a foreign key constraint".
        Schema::table('workshop_pages', function (Blueprint $table) {
            $table->unsignedInteger('version')->default(1)->after('slug');
            $table->foreignId('parent_workshop_page_id')->nullable()->after('version')
                ->constrained('workshop_pages')->nullOnDelete();
            $table->timestamp('superseded_at')->nullable()->after('parent_workshop_page_id');
        });

        Schema::table('workshop_pages', function (Blueprint $table) {
            $table->unique(['workshop_id', 'slug', 'version'], 'workshop_pages_workshop_slug_version_unique');
        });

        Schema::table('workshop_pages', function (Blueprint $table) {
            $table->dropUnique('workshop_pages_workshop_slug_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workshop_pages', function (Blueprint $table) {
            $table->unique(['workshop_id', 'slug'], 'workshop_pages_workshop_slug_unique');
        });

        Schema::table('workshop_pages', function (Blueprint $table) {
            $table->dropUnique('workshop_pages_workshop_slug_version_unique');
            $table->dropConstrainedForeignId('parent_workshop_page_id');
            $table->dropColumn(['version', 'superseded_at']);
        });
    }
};
