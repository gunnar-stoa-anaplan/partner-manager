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
        \App\Support\DatabasePortability::modifyEnum('blueprints', 'category', ['application', 'product', 'delivery']);
    }

    public function down(): void
    {
        \App\Support\DatabasePortability::modifyEnum('blueprints', 'category', ['application', 'product']);
    }
};
