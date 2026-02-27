<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->string('logo_path')->nullable()->after('region');
            $table->string('phone')->nullable()->after('logo_path');
            $table->string('sales_territory')->nullable()->after('phone');
            $table->string('mailing_country')->nullable()->after('sales_territory');
            $table->string('mailing_state')->nullable()->after('mailing_country');
            $table->string('mailing_street')->nullable()->after('mailing_state');
            $table->string('mailing_suite')->nullable()->after('mailing_street');
            $table->string('mailing_city')->nullable()->after('mailing_suite');
            $table->string('mailing_postal_code')->nullable()->after('mailing_city');
            $table->text('partner_locator_emails')->nullable()->after('mailing_postal_code');
            $table->string('headline')->nullable()->after('partner_locator_emails');
            $table->text('company_description')->nullable()->after('headline');
        });

        Schema::create('partner_marketplace_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained()->cascadeOnDelete();
            $table->string('category');
            $table->string('title');
            $table->string('link', 512);
            $table->text('description');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['partner_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_marketplace_entries');

        Schema::table('partners', function (Blueprint $table) {
            $table->dropColumn([
                'logo_path', 'phone', 'sales_territory',
                'mailing_country', 'mailing_state', 'mailing_street',
                'mailing_suite', 'mailing_city', 'mailing_postal_code',
                'partner_locator_emails', 'headline', 'company_description',
            ]);
        });
    }
};
