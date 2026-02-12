<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('imports', function (Blueprint $table) {
            $table->string('source')->default('web')->after('type');
            $table->unsignedBigInteger('api_token_id')->nullable()->after('user_id');
            $table->integer('projects_created')->default(0)->after('opportunities_updated');
            $table->integer('projects_updated')->default(0)->after('projects_created');
            $table->integer('contacts_created')->default(0)->after('projects_updated');
            $table->integer('contacts_updated')->default(0)->after('contacts_created');
        });
    }

    public function down(): void
    {
        Schema::table('imports', function (Blueprint $table) {
            $table->dropColumn(['source', 'api_token_id', 'projects_created', 'projects_updated', 'contacts_created', 'contacts_updated']);
        });
    }
};
