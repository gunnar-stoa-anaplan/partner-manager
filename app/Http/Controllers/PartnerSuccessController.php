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

        $allCommits = PartnerPlanCommit::query()
            ->when($scopedIds !== null, fn ($q) => $q->whereIn('partner_id', $scopedIds))
            ->get();

        // Practice-wide role commits (lob = '') drive the role coverage tiles.
        $commits = $allCommits->where('lob', '')->groupBy('role')->map(fn ($g) => (int) $g->sum('committed'));
        foreach ($plan['roles'] as $role => &$r) {
            $r['committed'] = $commits[$role] ?? 0;
            $r['to_assign'] = max(0, $r['committed'] - $r['certified']);
        }
        unset($r);

        // Per-LOB commits (lob <> '') drive the "People committed by line of
        // business" matrix: $lobCommits[lob][role] = committed across scope.
        $lobCommits = [];
        foreach ($allCommits->where('lob', '!=', '') as $c) {
            $lobCommits[$c->lob][$c->role] = ($lobCommits[$c->lob][$c->role] ?? 0) + (int) $c->committed;
        }

        // Commit is editable only when exactly one partner is in scope (unambiguous).
        $editablePartner = (is_array($scopedIds) && count($scopedIds) === 1)
            ? Partner::query()->withoutGlobalScopes()->find($scopedIds[0])
            : null;
        $editableCommits = [];
        $editableLobCommits = [];
        if ($editablePartner) {
            foreach (PartnerPlanCommit::where('partner_id', $editablePartner->id)->get() as $c) {
                if ($c->lob === '') {
                    $editableCommits[$c->role] = (int) $c->committed;
                } else {
                    $editableLobCommits[$c->lob][$c->role] = (int) $c->committed;
                }
            }
        }

        $options = $svc->filterOptions();
        $options['partners'] = $visibleIds !== null
            ? ($this->visibility->visiblePartnerNames() ?? [])
            : $svc->partners();
        sort($options['partners']);
        $active = PsmEngagementFilters::get();

        return view('partner-success.business-plan', compact('plan', 'options', 'active', 'editablePartner', 'editableCommits', 'lobCommits', 'editableLobCommits'));
    }

    /**
     * PSM Engagement → Capability. Practice capability coverage — people by role,
     * certifications across the practice, and a partner × certification grid.
     * Scoped to the actor's partners and the pinned filters.
     */
    public function capability()
    {
        $svc = $this->metrics();
        $cap = $svc->capability();

        $options = $svc->filterOptions();
        $visible = $this->visibility->visiblePartnerNames();
        $options['partners'] = $visible ?? $svc->partners();
        sort($options['partners']);
        $active = PsmEngagementFilters::get();

        return view('partner-success.capability', compact('cap', 'options', 'active'));
    }

    /**
     * PSM Engagement → Enablement. Two sub-views: By individual (people roster
     * with certs / in-progress) and By product & role (coverage vs demand).
     * Scoped to the actor's partners and the pinned filters.
     */
    public function enablement()
    {
        $svc = $this->metrics();
        $en = $svc->enablement();

        $options = $svc->filterOptions();
        $visible = $this->visibility->visiblePartnerNames();
        $options['partners'] = $visible ?? $svc->partners();
        sort($options['partners']);
        $active = PsmEngagementFilters::get();

        return view('partner-success.enablement', compact('en', 'options', 'active'));
    }

    /**
     * PSM Engagement → Delivery. Three tabs: Use Cases (delivery-status matrix),
     * Implementation Health (editable project RAG grid), and Support (empty-state
     * until a support extract is linked). Scoped to the actor's partners.
     */
    public function delivery()
    {
        $svc = $this->metrics();
        $data = $svc->delivery();

        // Merge PSM-entered health onto the project rows.
        $health = \App\Models\ProjectHealth::whereIn('project_key', array_column($data['projects'], 'key'))
            ->get()->keyBy('project_key');
        $worst = function (?string ...$vals) {
            $rank = ['R' => 3, 'Y' => 2, 'G' => 1];
            $out = null;
            $max = 0;
            foreach ($vals as $v) {
                if ($v && ($rank[$v] ?? 0) > $max) {
                    $max = $rank[$v];
                    $out = $v;
                }
            }
            return $out;
        };
        $data['projects'] = array_map(function ($p) use ($health, $worst) {
            $h = $health->get($p['key']);
            $p['phase'] = $h->phase ?? null;
            $p['scope'] = $h->scope ?? null;
            $p['timeline'] = $h->timeline ?? null;
            $p['budget'] = $h->budget ?? null;
            $p['reason'] = $h->reason ?? null;
            $p['overall'] = $worst($p['scope'], $p['timeline'], $p['budget']);
            return $p;
        }, $data['projects']);

        $options = $svc->filterOptions();
        $visible = $this->visibility->visiblePartnerNames();
        $options['partners'] = $visible ?? $svc->partners();
        sort($options['partners']);
        $active = PsmEngagementFilters::get();

        return view('partner-success.delivery', compact('data', 'options', 'active'));
    }

    /** Persist a PSM-entered implementation-health signal for a project. */
    public function setProjectHealth(Request $request)
    {
        $data = $request->validate([
            'project_key' => 'required|string',
            'field' => 'required|string|in:phase,scope,timeline,budget,reason',
            'value' => 'nullable|string|max:2000',
        ]);

        // Only allow editing a project the actor can see.
        abort_unless($this->metrics()->deliveryProjectVisible($data['project_key']), 404);

        $value = ($data['value'] ?? '') === '' ? null : $data['value'];
        if (in_array($data['field'], ['scope', 'timeline', 'budget'], true)) {
            $value = in_array($value, ['G', 'Y', 'R'], true) ? $value : null;
        }

        \App\Models\ProjectHealth::updateOrCreate(
            ['project_key' => $data['project_key']],
            [$data['field'] => $value],
        );

        return $request->wantsJson() ? response()->json(['ok' => true]) : back();
    }

    /**
     * Set a Business Plan headcount commit (single-partner scope). An empty lob
     * is the practice-wide commit (role tiles); a populated lob is a per-line-of-
     * business commit (the People-committed-by-line-of-business matrix).
     */
    public function setCommit(Request $request)
    {
        $data = $request->validate([
            'partner_id' => 'required|integer',
            'lob' => 'nullable|string|max:32',
            'role' => 'required|string|in:GTM,PreSales,Delivery',
            'committed' => 'required|integer|min:0',
        ]);

        // Only allow committing for a partner the actor can see.
        $visibleIds = $this->visibility->visiblePartnerIds();
        abort_if($visibleIds !== null && ! in_array((int) $data['partner_id'], $visibleIds, true), 404);

        PartnerPlanCommit::updateOrCreate(
            ['partner_id' => $data['partner_id'], 'lob' => $data['lob'] ?? '', 'role' => $data['role']],
            ['committed' => $data['committed']],
        );

        return back();
    }

    /**
     * PSM Engagement → Pipeline Overview. Six distribution charts plus the
     * account × product matrix, scoped to the actor's partners and the pinned
     * filters.
     */
    public function pipeline()
    {
        $svc = $this->metrics();
        $pipe = $svc->pipeline();

        $options = $svc->filterOptions();
        $visible = $this->visibility->visiblePartnerNames();
        $options['partners'] = $visible ?? $svc->partners();
        sort($options['partners']);
        $active = PsmEngagementFilters::get();

        return view('partner-success.pipeline', compact('pipe', 'options', 'active'));
    }

    /**
     * PSM Engagement → Readiness Plan. Partner × path matrix with a
     * Baseline / Target / Gap lens and a role switch. Scoped to the actor's
     * partners and the pinned filters.
     */
    public function readiness()
    {
        $svc = $this->metrics();
        $plan = $svc->readinessPlan();

        $options = $svc->filterOptions();
        $visible = $this->visibility->visiblePartnerNames();
        $options['partners'] = $visible ?? $svc->partners();
        sort($options['partners']);
        $active = PsmEngagementFilters::get();

        return view('partner-success.readiness', compact('plan', 'options', 'active'));
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
