<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\PartnerPlanCommit;
use App\Services\PartnerSuccess\MetricService;
use App\Support\PsmEngagementFilters;
use App\Support\VisibilityContext;
use Illuminate\Http\Request;

/**
 * Partner Success dashboard — audited, warehouse-backed. Every number drills to
 * its source rows + SQL + weekly history; GEO / PARTNER filters re-run all of it.
 * Row-level visibility: non-admin actors only ever see their own partners, so
 * "all partners" means "all of MY partners".
 */
class PartnerSuccessController extends Controller
{
    public function __construct(
        private readonly MetricService $metrics,
        private readonly VisibilityContext $visibility,
    ) {}

    /**
     * MetricService scoped to the partners the current actor may see. Resolved
     * per request (NOT in the constructor, where auth is not yet available).
     */
    private function metrics(): MetricService
    {
        return $this->metrics
            ->restrictTo($this->visibility->visiblePartnerNames())
            ->withScope(PsmEngagementFilters::get());
    }

    /**
     * PSM Engagement — the app-native dashboard built on the Insight Kit. All
     * five sections (Pipeline / Delivery / Capability / Enablement / Catalog),
     * every KPI drillable to source, visibility-scoped. Uses the shared
     * SmartAdmin design language so it matches Workshops and the rest of the app.
     */
    public function index(?string $section = null)
    {
        $order = ['Pipeline', 'Delivery', 'Capability', 'Enablement', 'Catalog'];
        $svc = $this->metrics();
        $sections = [];
        foreach ($svc->metrics() as $m) {
            $sections[$m['section'] ?: 'Other'][] = $m;
        }
        uksort($sections, fn ($a, $b) => (array_search($a, $order) === false ? 99 : array_search($a, $order))
            <=> (array_search($b, $order) === false ? 99 : array_search($b, $order)));

        // Optional single-section focus (submenu, distinct path e.g. /partner-success/pipeline).
        $focus = ucfirst(strtolower((string) $section));
        $pageTitle = 'PSM Engagement';
        if ($focus && isset($sections[$focus])) {
            $sections = [$focus => $sections[$focus]];
            $pageTitle = $focus;
        } else {
            $focus = null;
        }

        // Pinned filter panel data.
        $options = $svc->filterOptions();
        $visible = $this->visibility->visiblePartnerNames();
        $options['partners'] = $visible ?? $svc->partners();
        sort($options['partners']);
        $active = PsmEngagementFilters::get();

        return view('partner-success.overview', compact('sections', 'options', 'active', 'focus', 'pageTitle'));
    }

    /**
     * PSM Engagement → Business Plans. Revenue attribution + delivery + revenue
     * by line of business + certified-vs-committed headcount by role. Honors the
     * pinned scope filters and shows only the user's partners.
     */
    public function businessPlans()
    {
        $svc = $this->metrics();
        $plan = $svc->businessPlan();

        // Which partners are in the effective scope (for the headcount commit).
        $visibleIds = $this->visibility->visiblePartnerIds(); // null = all (admin/CoE)
        $sessionPartners = PsmEngagementFilters::get()['partners'] ?? [];
        if ($sessionPartners) {
            $scopedIds = Partner::query()->withoutGlobalScopes()
                ->whereIn('name', $sessionPartners)
                ->when($visibleIds !== null, fn ($q) => $q->whereIn('id', $visibleIds))
                ->pluck('id')->all();
        } else {
            $scopedIds = $visibleIds; // null = all partners
        }

        $commits = PartnerPlanCommit::query()
            ->when($scopedIds !== null, fn ($q) => $q->whereIn('partner_id', $scopedIds))
            ->get()->groupBy('role')->map(fn ($g) => (int) $g->sum('committed'));

        foreach ($plan['roles'] as $role => &$r) {
            $r['committed'] = $commits[$role] ?? 0;
            $r['to_assign'] = max(0, $r['committed'] - $r['certified']);
        }
        unset($r);

        // Commit is editable only when exactly one partner is in scope (unambiguous).
        $editablePartner = (is_array($scopedIds) && count($scopedIds) === 1)
            ? Partner::query()->withoutGlobalScopes()->find($scopedIds[0])
            : null;
        $editableCommits = $editablePartner
            ? PartnerPlanCommit::where('partner_id', $editablePartner->id)->pluck('committed', 'role')->all()
            : [];

        $options = $svc->filterOptions();
        $options['partners'] = $visibleIds !== null
            ? ($this->visibility->visiblePartnerNames() ?? [])
            : $svc->partners();
        sort($options['partners']);
        $active = PsmEngagementFilters::get();

        return view('partner-success.business-plan', compact('plan', 'options', 'active', 'editablePartner', 'editableCommits'));
    }

    /** Set a per-partner Business Plan headcount commit (single-partner scope). */
    public function setCommit(Request $request)
    {
        $data = $request->validate([
            'partner_id' => 'required|integer',
            'role' => 'required|string|in:GTM,PreSales,Delivery',
            'committed' => 'required|integer|min:0',
        ]);

        // Only allow committing for a partner the actor can see.
        $visibleIds = $this->visibility->visiblePartnerIds();
        abort_if($visibleIds !== null && ! in_array((int) $data['partner_id'], $visibleIds, true), 404);

        PartnerPlanCommit::updateOrCreate(
            ['partner_id' => $data['partner_id'], 'role' => $data['role']],
            ['committed' => $data['committed']],
        );

        return back();
    }

    /** PSM Engagement → Readiness Plan (scaffold — to be built out). */
    public function readiness()
    {
        return view('partner-success.placeholder', [
            'pageTitle' => 'Readiness Plan',
            'blurb' => 'Partner readiness — the plan to get each partner to delivery-ready across capability and enablement. Coming soon.',
        ]);
    }

    /** Persist the pinned-panel scope to the session (applies to all PSM Engagement views). */
    public function setFilters(Request $request)
    {
        PsmEngagementFilters::put($request->only(PsmEngagementFilters::DIMENSIONS));

        if ($request->wantsJson()) {
            return response()->json(['active' => PsmEngagementFilters::get()]);
        }

        return redirect()->route('partner-success.index');
    }

    /** Clear the pinned-panel scope. */
    public function clearFilters(Request $request)
    {
        PsmEngagementFilters::clear();

        if ($request->wantsJson()) {
            return response()->json(['active' => PsmEngagementFilters::get()]);
        }

        return redirect()->route('partner-success.index');
    }

    /** Legacy imported dashboard (custom design), kept for reference only. */
    public function legacy()
    {
        return view('partner-success.dashboard');
    }

    public function apiMetrics(Request $request)
    {
        return response()->json($this->metrics()->metrics($request->query('geo'), $request->query('partner')));
    }

    public function apiPartners()
    {
        $partners = $this->metrics()->partners();

        // Restrict the picker to partners this actor may see.
        $visible = $this->visibility->visiblePartnerNames();
        if ($visible !== null) {
            $allowed = array_flip($visible);
            $partners = array_values(array_filter($partners, fn ($p) => isset($allowed[$p])));
        }

        return response()->json($partners);
    }

    public function apiDrill(Request $request, string $name)
    {
        return response()->json($this->metrics()->drill($name, $request->query('geo'), $request->query('partner')));
    }

    public function apiTrend(Request $request, string $name)
    {
        return response()->json($this->metrics()->trend($name, $request->query('geo'), $request->query('partner')));
    }

    public function apiBreakdown(Request $request, string $key)
    {
        return response()->json($this->metrics()->breakdown($key, $request->query('geo'), $request->query('partner')));
    }

    public function apiDrillBreakdown(Request $request, string $key)
    {
        return response()->json($this->metrics()->drillBreakdown(
            $key, (string) $request->query('value', ''), $request->query('geo'), $request->query('partner')
        ));
    }
}
