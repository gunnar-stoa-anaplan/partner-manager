<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Increment 1 of the authorization model (docs/authorization-spec.md).
 *
 * Unrestricted partner visibility used to be an implicit consequence of the
 * `coe` role slug, hard-coded in VisibilityContext. It is now an explicit
 * capability grant (`data_scope.all_partners`). This migration grants that
 * privilege to the existing CoE role so behavior is preserved on databases that
 * were seeded before the change. Idempotent.
 */
return new class extends Migration
{
    private const PRIVILEGE = 'data_scope.all_partners';

    public function up(): void
    {
        $coeId = DB::table('roles')->where('slug', 'coe')->value('id');

        if ($coeId === null) {
            return; // no CoE role on this instance — nothing to preserve
        }

        $exists = DB::table('role_permissions')
            ->where('role_id', $coeId)
            ->where('module_slug', self::PRIVILEGE)
            ->exists();

        if ($exists) {
            return;
        }

        DB::table('role_permissions')->insert([
            'role_id' => $coeId,
            'module_slug' => self::PRIVILEGE,
            'access_level' => 'read',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        $coeId = DB::table('roles')->where('slug', 'coe')->value('id');

        if ($coeId === null) {
            return;
        }

        DB::table('role_permissions')
            ->where('role_id', $coeId)
            ->where('module_slug', self::PRIVILEGE)
            ->delete();
    }
};
