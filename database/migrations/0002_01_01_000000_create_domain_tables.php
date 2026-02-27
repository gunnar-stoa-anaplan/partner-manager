<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Accounts
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // Partners
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->string('salesforce_id')->unique()->nullable();
            $table->string('name');
            $table->string('website')->nullable();
            $table->string('industry')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('psm')->nullable()->index();
            $table->string('geography')->nullable()->index();
            $table->string('region')->nullable()->index();
            $table->timestamps();
        });

        // Objectives
        Schema::create('objectives', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // Salesforce Credentials
        Schema::create('salesforce_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('client_id');
            $table->string('username');
            $table->text('client_secret_encrypted');
            $table->string('instance_url')->default('https://login.salesforce.com');
            $table->string('redirect_uri')->nullable();
            $table->string('auth_method')->default('client_credentials');
            $table->timestamps();
        });

        // Contacts
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained()->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('title')->nullable();
            $table->string('avatar')->nullable();
            $table->timestamps();
        });

        // Projects
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('partner_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->date('start_date')->nullable();
            $table->unsignedBigInteger('cal_id')->nullable();
            $table->unsignedBigInteger('tal_id')->nullable();
            $table->unsignedBigInteger('ae_id')->nullable();
            $table->decimal('acv', 12, 2)->nullable();
            $table->string('status')->default('planning');
            $table->unsignedTinyInteger('percent_complete')->default(0);
            $table->unsignedTinyInteger('total_sprints')->default(0);
            $table->unsignedTinyInteger('current_sprint')->default(0);
            $table->text('delivery_team')->nullable();
            $table->timestamps();
        });

        // Leads
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('company')->nullable();
            $table->string('title')->nullable();
            $table->string('source', 50);
            $table->string('status', 50)->default('unassigned');
            $table->foreignId('partner_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Fundings
        Schema::create('fundings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->decimal('amount', 15, 2)->nullable();
            $table->string('status')->nullable();
            $table->string('type')->nullable();
            $table->text('description')->nullable();
            $table->date('date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fundings');
        Schema::dropIfExists('leads');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('contacts');
        Schema::dropIfExists('salesforce_credentials');
        Schema::dropIfExists('objectives');
        Schema::dropIfExists('partners');
        Schema::dropIfExists('accounts');
    }
};
