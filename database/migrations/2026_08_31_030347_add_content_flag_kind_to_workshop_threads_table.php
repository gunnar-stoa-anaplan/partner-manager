<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        \App\Support\DatabasePortability::modifyEnum('workshop_threads', 'kind', ['question', 'comment', 'content_flag'], true, 'question');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("UPDATE workshop_threads SET kind = 'question' WHERE kind = 'content_flag'");
        \App\Support\DatabasePortability::modifyEnum('workshop_threads', 'kind', ['question', 'comment'], true, 'question');
    }
};
