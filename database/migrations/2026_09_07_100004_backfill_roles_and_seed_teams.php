<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Backfill the multi-role pivots from the existing single-role columns, and seed
 * the four GTM function teams. Idempotent (insertOrIgnore).
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('users')->whereNotNull('role_id')->orderBy('id')
            ->select('id', 'role_id')->chunk(500, function ($rows) use ($now) {
                foreach ($rows as $r) {
                    DB::table('role_user')->insertOrIgnore([
                        'user_id' => $r->id, 'role_id' => $r->role_id,
                        'created_at' => $now, 'updated_at' => $now,
                    ]);
                }
            });

        DB::table('contacts')->whereNotNull('portal_role_id')->orderBy('id')
            ->select('id', 'portal_role_id')->chunk(500, function ($rows) use ($now) {
                foreach ($rows as $r) {
                    DB::table('role_contact')->insertOrIgnore([
                        'contact_id' => $r->id, 'role_id' => $r->portal_role_id,
                        'created_at' => $now, 'updated_at' => $now,
                    ]);
                }
            });

        foreach ([
            ['Sales', 'sales', 'Revenue generation — pipeline, deals, and leads.'],
            ['Marketing', 'marketing', 'Demand generation, campaigns, and events.'],
            ['Service', 'service', 'Customer success and ongoing engagement.'],
            ['Implementation', 'implementation', 'Delivery, projects, and technical execution.'],
        ] as [$name, $slug, $desc]) {
            DB::table('teams')->insertOrIgnore([
                'name' => $name, 'slug' => $slug, 'description' => $desc,
                'is_active' => true, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('role_user')->truncate();
        DB::table('role_contact')->truncate();
        DB::table('teams')->whereIn('slug', ['sales', 'marketing', 'service', 'implementation'])->delete();
    }
};
