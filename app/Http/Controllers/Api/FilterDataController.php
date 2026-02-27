<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class FilterDataController extends Controller
{
    /**
     * Get PSM-Partner mapping for cascading filters
     * PSMs are users with role='psm' assigned to partners via user_partner pivot
     */
    public function psmPartnerMapping()
    {
        // Get all PSM-Partner relationships from user_partner pivot table
        $psmPartnerMap = DB::table('user_partner')
            ->join('users', 'user_partner.user_id', '=', 'users.id')
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->where('user_partner.role', 'psm')
            ->where('roles.slug', 'psm')
            ->select('users.name as psm_name', 'user_partner.partner_id')
            ->get()
            ->groupBy('psm_name')
            ->map(function ($assignments) {
                return $assignments->pluck('partner_id')->toArray();
            })
            ->toArray();

        return response()->json($psmPartnerMap);
    }

    /**
     * Get Geography-Region mapping for cascading filters
     */
    public function geoRegionMapping()
    {
        // Get all unique geography-region combinations
        $geoRegionMap = Partner::whereNotNull('geography')
            ->whereNotNull('region')
            ->select('geography', 'region')
            ->distinct()
            ->get()
            ->groupBy('geography')
            ->map(function ($regions) {
                return $regions->pluck('region')->unique()->values()->toArray();
            })
            ->toArray();

        return response()->json($geoRegionMap);
    }

    /**
     * Get all filter options for the analysis page
     */
    public function allFilterOptions()
    {
        // Get unique PSMs from users table
        $psms = User::whereRelation('role', 'slug', 'psm')
            ->orderBy('name')
            ->pluck('name')
            ->toArray();

        // Get unique geographies
        $geographies = Partner::whereNotNull('geography')
            ->distinct()
            ->pluck('geography')
            ->sort()
            ->values()
            ->toArray();

        // Get unique regions
        $regions = Partner::whereNotNull('region')
            ->distinct()
            ->pluck('region')
            ->sort()
            ->values()
            ->toArray();

        return response()->json([
            'psms' => $psms,
            'geographies' => $geographies,
            'regions' => $regions,
            'psm_partner_map' => $this->psmPartnerMapping()->getData(true),
            'geo_region_map' => $this->geoRegionMapping()->getData(true),
        ]);
    }
}
