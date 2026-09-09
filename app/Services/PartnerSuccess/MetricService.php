<?php

namespace App\Services\PartnerSuccess;

use Illuminate\Support\Facades\DB;

/**
 * Reads the audited Partner Success warehouse (schema `wh`) and derives every
 * dashboard number from the predicate metric catalog (wh.metric_definition):
 * current value, weekly trend, drill-through rows, and chart breakdowns — with
 * the GEO / PARTNER filters applied uniformly. Ported from the Flask reference
 * app; the SQL and definitions are identical, so the numbers match.
 */
class MetricService
{
    private const CONN = 'warehouse';

    private const PI = '(partner IS NOT NULL OR implementer IS NOT NULL)';

    /**
     * Fixed product catalog for the Sales — opportunities by product matrix:
     * line of business => ordered [product name, column code]. This is the
     * single source of truth for the product columns (always shown, even with
     * no activity in scope), each product's LOB, and its short column label.
     * The warehouse only carries de-duplicated capability/group arrays per deal,
     * so a product's LOB can't be inferred from the data — this map defines it.
     * A few catalog products (ADO, MFP) are not yet in the warehouse feed and
     * simply render empty until they appear.
     */
    private const SALES_CATALOG = [
        'Platform' => [
            ['Core Platform', 'CORE'], ['Polaris', 'POLARIS'], ['Anaplan Data Orchestrator', 'ADO'],
        ],
        'SCM' => [
            ['Demand Planning', 'DP'], ['Statistical Forecasting', 'SF'], ['Supply Planning', 'SP'],
            ['Inventory Planning', 'IP'], ['Production Planning', 'PP'], ['Rough Cut Capacity', 'RCC'],
            ['Integrated Business Planning', 'IBP'], ['Commercial Supply (Biotech)', 'CSP'],
            ['Allocation & Replenishment', 'A&R'], ['Merchandise Financial Planning', 'MFP'],
        ],
        'FIN' => [
            ['Integrated Financial Planning', 'IFP'], ['Financial Consolidation (FCR)', 'FCR'],
            ['Profitability Analysis', 'PROF'], ['Project Cost Planning', 'PJC'],
            ['Subscription Revenue', 'SUBR'], ['Consensus Margin', 'CMGN'],
        ],
        'WFP' => [
            ['Workforce Planning', 'WFP'], ['Contact Center', 'CCP'], ['Project Resource', 'PJR'],
        ],
        'RPM' => [
            ['Territory & Quota', 'T&Q'], ['Sales Forecasting', 'SFC'],
            ['Segmentation', 'SEG'], ['Trade Promotion Mgmt', 'TPM'],
        ],
        'AI' => [
            ['Forecaster', 'FCAST'], ['CoModeler', 'COMOD'],
        ],
    ];

    /** base table config: current view, full table, geo/partner support, drill columns */
    private const BASES = [
        'opportunity' => [
            'cur' => 'wh.opportunity_current', 'all' => 'wh.opportunity', 'geo' => 'geo', 'partner' => 'opp',
            'cols' => ['source_id', 'account', 'use_case', 'partner', 'implementer', 'geo', 'fiscal_qtr', 'lob', 'stage_label', 'acv', 'snapshot_date', 'load_run_id', 'source_file'],
            'order' => 'acv DESC NULLS LAST',
        ],
        'project' => [
            'cur' => 'wh.project_current', 'all' => 'wh.project', 'geo' => 'geo', 'partner' => 'one',
            'cols' => ['source_id', 'name', 'account', 'partner', 'geo', 'health_partner', 'health_ps', 'health_cs', 'go_live', 'owner', 'snapshot_date', 'load_run_id', 'source_file'],
            'order' => 'go_live NULLS LAST',
        ],
        'person' => [
            'cur' => 'wh.person_current', 'all' => 'wh.person', 'geo' => null, 'partner' => 'one',
            'cols' => ['source_id', 'name', 'email', 'partner', 'country', 'roles', 'certified', 'in_progress', 'snapshot_date', 'load_run_id', 'source_file'],
            'order' => 'name',
        ],
        'course' => [
            'cur' => 'wh.course_current', 'all' => 'wh.course', 'geo' => null, 'partner' => null,
            'cols' => ['source_id', 'title', 'source', 'duration', 'roles', 'snapshot_date', 'load_run_id', 'source_file'],
            'order' => 'title',
        ],
        'learning_path' => [
            'cur' => 'wh.learning_path_current', 'all' => 'wh.learning_path', 'geo' => null, 'partner' => null,
            'cols' => ['source_id', 'outcome', 'n_courses', 'snapshot_date', 'load_run_id', 'source_file'],
            'order' => 'source_id',
        ],
    ];

    /** chart breakdowns (whitelisted) */
    private const BK = [
        'stage'   => ['base' => 'opportunity', 'grp' => 'stage_label', 'col' => 'stage_label', 'pi' => true,  'sum' => true,  'ord' => 'key'],
        'lob'     => ['base' => 'opportunity', 'grp' => 'lob',         'col' => 'lob',         'pi' => true,  'sum' => true,  'ord' => 'a DESC'],
        'geo'     => ['base' => 'opportunity', 'grp' => 'geo',         'col' => 'geo',         'pi' => true,  'sum' => true,  'ord' => 'a DESC'],
        'dhealth' => ['base' => 'project',     'grp' => 'health_ps',   'col' => 'health_ps',   'pi' => false, 'sum' => false, 'ord' => 'c DESC'],
        'dgeo'    => ['base' => 'project',     'grp' => 'geo',         'col' => 'geo',         'pi' => false, 'sum' => false, 'ord' => 'c DESC'],
        'capgrp'  => ['base' => 'opportunity', 'grp' => 'unnest(cap_groups)', 'arr' => 'cap_groups', 'pi' => true,  'sum' => true,  'ord' => 'a DESC'],
        'role'    => ['base' => 'person',      'grp' => 'unnest(roles)',      'arr' => 'roles',      'pi' => false, 'sum' => false, 'ord' => 'c DESC'],
        'csource' => ['base' => 'course',      'grp' => "split_part(source,' ',1)", 'col' => "split_part(source,' ',1)", 'pi' => false, 'sum' => false, 'ord' => 'c DESC'],
    ];

    private const GEOS = ['AMER', 'APAC', 'EMEA'];

    private function db()
    {
        return DB::connection(self::CONN);
    }

    private function geo(?string $g): ?string
    {
        return in_array($g, self::GEOS, true) ? $g : null;
    }

    /** filter clauses (+ bound params + human-readable shown clause) for a base */
    /**
     * Row-level visibility: when set to an array, every query is additionally
     * constrained to these partner names ("all" means "all MY partners"); null
     * means unrestricted (admins / bypass). Set from VisibilityContext.
     *
     * @var array<string>|null
     */
    private ?array $allowedPartners = null;

    public function restrictTo(?array $partnerNames): static
    {
        $this->allowedPartners = $partnerNames;

        return $this;
    }

    /**
     * Multi-dimension scope from the pinned filter panel. Keys (all optional):
     * geos[], partners[], lobs[], stages[], quarters[], capGroups[]. Each is
     * applied only to bases whose table has that column (geo/partner broadly;
     * lob/stage/quarter/capGroups on the opportunity base only).
     *
     * @param  array<string, array<string>>  $scope
     */
    public function withScope(array $scope): static
    {
        $this->scope = $scope;

        return $this;
    }

    /** @var array<string, array<string>> */
    private array $scope = [];

    private function filt(string $base, ?string $geo, ?string $partner): array
    {
        $clauses = [];
        $shown = [];
        $params = [];
        $b = self::BASES[$base];
        if ($geo && $b['geo']) {
            $clauses[] = "{$b['geo']} = ?";
            $params[] = $geo;
            $shown[] = "{$b['geo']} = '{$geo}'";
        }
        if ($partner && $b['partner']) {
            $pe = str_replace("'", "''", $partner);
            if ($b['partner'] === 'opp') {
                $clauses[] = '(partner = ? OR implementer = ?)';
                $params[] = $partner;
                $params[] = $partner;
                $shown[] = "(partner = '{$pe}' OR implementer = '{$pe}')";
            } else {
                $clauses[] = 'partner = ?';
                $params[] = $partner;
                $shown[] = "partner = '{$pe}'";
            }
        }

        // Row-level visibility: constrain to the actor's allowed partners.
        if ($this->allowedPartners !== null && $b['partner']) {
            if ($this->allowedPartners === []) {
                $clauses[] = '1 = 0';
                $shown[] = 'visibility: none';
            } else {
                $ph = implode(',', array_fill(0, count($this->allowedPartners), '?'));
                if ($b['partner'] === 'opp') {
                    $clauses[] = "(partner IN ($ph) OR implementer IN ($ph))";
                    $params = array_merge($params, $this->allowedPartners, $this->allowedPartners);
                } else {
                    $clauses[] = "partner IN ($ph)";
                    $params = array_merge($params, $this->allowedPartners);
                }
                $shown[] = 'visibility: '.count($this->allowedPartners).' partner(s)';
            }
        }

        // Multi-dimension scope from the pinned filter panel.
        $addIn = function (string $col, array $vals) use (&$clauses, &$params, &$shown) {
            $vals = array_values($vals);
            $ph = implode(',', array_fill(0, count($vals), '?'));
            $clauses[] = "{$col} IN ({$ph})";
            $params = array_merge($params, $vals);
            $shown[] = "{$col} IN (".count($vals).' selected)';
        };

        if (! empty($this->scope['geos']) && $b['geo']) {
            $addIn($b['geo'], $this->scope['geos']);
        }
        if (! empty($this->scope['partners']) && $b['partner']) {
            $vals = array_values($this->scope['partners']);
            $ph = implode(',', array_fill(0, count($vals), '?'));
            if ($b['partner'] === 'opp') {
                $clauses[] = "(partner IN ({$ph}) OR implementer IN ({$ph}))";
                $params = array_merge($params, $vals, $vals);
            } else {
                $clauses[] = "partner IN ({$ph})";
                $params = array_merge($params, $vals);
            }
            $shown[] = 'partner IN ('.count($vals).' selected)';
        }
        if ($base === 'opportunity') {
            if (! empty($this->scope['lobs'])) {
                $addIn('lob', $this->scope['lobs']);
            }
            if (! empty($this->scope['stages'])) {
                $addIn('stage_label', $this->scope['stages']);
            }
            if (! empty($this->scope['quarters'])) {
                $addIn('fiscal_qtr', $this->scope['quarters']);
            }
            if (! empty($this->scope['capGroups'])) {
                $vals = array_values($this->scope['capGroups']);
                $ph = implode(',', array_fill(0, count($vals), '?'));
                $clauses[] = "cap_groups && ARRAY[{$ph}]::text[]";
                $params = array_merge($params, $vals);
                $shown[] = 'capability in ('.count($vals).' selected)';
            }
        }

        return [$clauses, $params, $shown];
    }

    private function where(string $pred, string $base, ?string $geo, ?string $partner): array
    {
        [$c, $p, $s] = $this->filt($base, $geo, $partner);
        $sql = implode(' AND ', array_merge(["($pred)"], $c));
        $shown = implode(' AND ', array_merge(["($pred)"], $s));

        return [$sql, $p, $shown];
    }

    public function metrics(?string $geo = null, ?string $partner = null): array
    {
        $geo = $this->geo($geo);
        $defs = $this->db()->select('SELECT * FROM wh.metric_definition ORDER BY sort');
        $out = [];
        foreach ($defs as $m) {
            $base = $m->base_table;
            [$w, $p] = $this->where($m->predicate, $base, $geo, $partner);
            $sumExpr = $m->sum_col ? "sum({$m->sum_col})" : 'NULL::bigint';
            $row = $this->db()->selectOne(
                "SELECT count(*)::int n, {$sumExpr} acv, max(snapshot_date) d FROM ".self::BASES[$base]['cur']." WHERE {$w}", $p
            );
            $out[] = [
                'name' => $m->name, 'title' => $m->title, 'description' => $m->description,
                'unit' => $m->unit, 'section' => $m->section, 'sort' => $m->sort,
                'count' => (int) $row->n, 'acv' => $row->acv !== null ? (int) $row->acv : null,
                'has_trend' => true,
                'geo_applies' => (bool) self::BASES[$base]['geo'],
                'partner_applies' => (bool) self::BASES[$base]['partner'],
                'snapshot_date' => $row->d,
            ];
        }

        return $out;
    }

    public function drill(string $name, ?string $geo = null, ?string $partner = null): array
    {
        $geo = $this->geo($geo);
        $m = $this->db()->selectOne('SELECT * FROM wh.metric_definition WHERE name = ?', [$name]);
        abort_if(! $m, 404);
        $base = $m->base_table;
        $b = self::BASES[$base];
        [$w, $p, $shown] = $this->where($m->predicate, $base, $geo, $partner);
        $cols = implode(', ', $b['cols']);
        $rows = $this->db()->select("SELECT {$cols} FROM {$b['cur']} WHERE {$w} ORDER BY {$b['order']}", $p);
        $sql = "SELECT {$cols}\nFROM {$b['cur']}\nWHERE {$shown}\nORDER BY {$b['order']}";

        return $this->payload($m->title, $m->description, $sql, $rows);
    }

    public function trend(string $name, ?string $geo = null, ?string $partner = null): array
    {
        $geo = $this->geo($geo);
        $m = $this->db()->selectOne('SELECT * FROM wh.metric_definition WHERE name = ?', [$name]);
        if (! $m) {
            return ['unit' => null, 'points' => []];
        }
        $base = $m->base_table;
        [$w, $p] = $this->where($m->predicate, $base, $geo, $partner);
        $sumExpr = $m->sum_col ? "sum({$m->sum_col})" : 'NULL::bigint';
        $rows = $this->db()->select(
            "SELECT snapshot_date d, count(*)::int n, {$sumExpr} acv FROM ".self::BASES[$base]['all']." WHERE {$w} GROUP BY snapshot_date ORDER BY snapshot_date", $p
        );

        return ['unit' => $m->unit, 'points' => array_map(fn ($r) => [
            'd' => $r->d, 'count' => (int) $r->n, 'acv' => $r->acv !== null ? (int) $r->acv : null,
        ], $rows)];
    }

    public function partners(): array
    {
        $rows = $this->db()->select("
            SELECT DISTINCT p FROM (
              SELECT partner p FROM wh.opportunity_current
              UNION SELECT implementer FROM wh.opportunity_current
              UNION SELECT partner FROM wh.project_current
              UNION SELECT partner FROM wh.person_current
            ) t WHERE p IS NOT NULL ORDER BY p");

        return array_map(fn ($r) => $r->p, $rows);
    }

    /**
     * Distinct option lists for the pinned filter panel, constrained to the
     * actor's visible partners (allowedPartners) but NOT to the current scope,
     * so choosing one value never hides the others.
     *
     * @return array{geos: array<string>, lobs: array<string>, stages: array<string>, quarters: array<string>, capGroups: array<string>}
     */
    public function filterOptions(): array
    {
        $where = '1=1';
        $params = [];
        if ($this->allowedPartners !== null) {
            if ($this->allowedPartners === []) {
                $where = '1=0';
            } else {
                $ph = implode(',', array_fill(0, count($this->allowedPartners), '?'));
                $where = "(partner IN ({$ph}) OR implementer IN ({$ph}))";
                $params = array_merge($this->allowedPartners, $this->allowedPartners);
            }
        }

        $db = $this->db();
        $distinct = fn (string $col) => array_map(
            fn ($r) => $r->v,
            $db->select("SELECT DISTINCT {$col} v FROM wh.opportunity_current WHERE {$where} AND {$col} IS NOT NULL ORDER BY 1", $params)
        );

        return [
            'geos' => $distinct('geo'),
            'lobs' => $distinct('lob'),
            'stages' => $distinct('stage_label'),
            'quarters' => $distinct('fiscal_qtr'),
            'capGroups' => array_map(
                fn ($r) => $r->v,
                $db->select("SELECT DISTINCT unnest(cap_groups) v FROM wh.opportunity_current WHERE {$where} ORDER BY 1", $params)
            ),
        ];
    }

    /**
     * Business Plan aggregation — revenue attribution, delivery, revenue-by-LOB,
     * and certified headcount by role. Scoped by the same filt() as everything
     * else (row-level visibility + the pinned scope filters).
     *
     * @return array<string, mixed>
     */
    public function businessPlan(): array
    {
        $db = $this->db();

        [$oc, $op] = $this->filt('opportunity', null, null);
        $ow = 'WHERE '.implode(' AND ', array_merge([self::PI], $oc));
        $o = $db->selectOne("
            SELECT
              count(*) FILTER (WHERE stage < 5 AND partner IS NOT NULL AND partner <> 'nan')::int AS src_n,
              coalesce(sum(acv) FILTER (WHERE stage < 5 AND partner IS NOT NULL AND partner <> 'nan'),0)::bigint AS src_v,
              count(*) FILTER (WHERE stage < 5 AND (partner IS NULL OR partner = 'nan') AND implementer IS NOT NULL AND implementer <> 'nan')::int AS inf_n,
              coalesce(sum(acv) FILTER (WHERE stage < 5 AND (partner IS NULL OR partner = 'nan') AND implementer IS NOT NULL AND implementer <> 'nan'),0)::bigint AS inf_v,
              count(*) FILTER (WHERE stage = 5)::int AS won_n,
              coalesce(sum(acv) FILTER (WHERE stage = 5),0)::bigint AS won_v
            FROM wh.opportunity_current {$ow}", $op);

        [$pc, $pp] = $this->filt('project', null, null);
        $pw = $pc ? ('WHERE '.implode(' AND ', $pc)) : '';
        $p = $db->selectOne("
            SELECT
              count(*) FILTER (WHERE go_live IS NOT NULL AND go_live <= now())::int AS delivered,
              count(*) FILTER (WHERE go_live IS NULL OR go_live > now())::int AS in_progress
            FROM wh.project_current {$pw}", $pp);

        $lob = $db->select("
            SELECT coalesce(lob,'(unspecified)') AS lob,
              count(*) FILTER (WHERE stage < 5)::int AS open_n,
              coalesce(sum(acv) FILTER (WHERE stage < 5),0)::bigint AS open_v,
              count(*) FILTER (WHERE stage = 5)::int AS won_n,
              coalesce(sum(acv) FILTER (WHERE stage = 5),0)::bigint AS won_v
            FROM wh.opportunity_current {$ow}
            GROUP BY 1 ORDER BY open_v DESC", $op);

        // Pipeline demand per line of business (capability group) — the rows of
        // the "People committed by line of business" matrix.
        $groups = $db->select("
            SELECT grp,
              count(*) FILTER (WHERE stage < 5)::int AS open_n,
              coalesce(sum(acv) FILTER (WHERE stage < 5),0)::bigint AS open_v,
              count(*) FILTER (WHERE stage = 5)::int AS won_n,
              coalesce(sum(acv) FILTER (WHERE stage = 5),0)::bigint AS won_v
            FROM wh.opportunity_current o, unnest(o.cap_groups) grp
            {$ow}
            GROUP BY grp ORDER BY open_v DESC", $op);

        // Product (capability) coverage — the SALES capability matrix, each
        // product tagged with the group it most often sells under.
        $caps = $db->select("
            SELECT cap,
              count(*) FILTER (WHERE stage < 5)::int AS open_n,
              coalesce(sum(acv) FILTER (WHERE stage < 5),0)::bigint AS open_v,
              count(*) FILTER (WHERE stage = 5)::int AS won_n,
              coalesce(sum(acv) FILTER (WHERE stage = 5),0)::bigint AS won_v
            FROM wh.opportunity_current o, unnest(o.capabilities) cap
            {$ow}
            GROUP BY cap ORDER BY open_v DESC", $op);
        // Product => line of business / column code, from the fixed catalog.
        $capLob = [];
        $capAbbr = [];
        foreach (self::SALES_CATALOG as $lobKey => $prods) {
            foreach ($prods as [$name, $code]) {
                $capLob[$name] = $lobKey;
                $capAbbr[$name] = $code;
            }
        }

        // Sales — opportunities by product: a sales-stage × product matrix. Row
        // totals ($ sold / opps / accounts) count each deal once; product cells
        // count a deal in every product it spans, so columns sum higher.
        $stageAgg = $db->select("
            SELECT stage, coalesce(max(stage_label),'')::text AS label,
              coalesce(sum(acv),0)::bigint AS v, count(*)::int AS n, count(DISTINCT account)::int AS a
            FROM wh.opportunity_current {$ow}
            GROUP BY stage ORDER BY stage", $op);
        $cellAgg = $db->select("
            SELECT stage, cap, coalesce(sum(acv),0)::bigint AS v, count(*)::int AS n
            FROM wh.opportunity_current o, unnest(o.capabilities) cap
            {$ow}
            GROUP BY stage, cap", $op);
        $grandAccounts = (int) ($db->selectOne(
            "SELECT count(DISTINCT account)::int AS a FROM wh.opportunity_current {$ow}", $op)->a ?? 0);

        // Cell values + column totals, keyed by product name.
        $colTotal = [];
        $cellMap = [];
        foreach ($cellAgg as $c) {
            $cellMap[(int) $c->stage][$c->cap] = ['v' => (int) $c->v, 'n' => (int) $c->n];
            $colTotal[$c->cap] = ($colTotal[$c->cap] ?? ['v' => 0, 'n' => 0]);
            $colTotal[$c->cap]['v'] += (int) $c->v;
            $colTotal[$c->cap]['n'] += (int) $c->n;
        }
        // Columns come from the fixed catalog — every product in every LOB is
        // always shown (empty cells render as "—"), in catalog order.
        $matrixGroups = [];
        foreach (self::SALES_CATALOG as $lobKey => $prods) {
            $matrixGroups[] = [
                'lob' => $lobKey,
                'products' => array_map(fn ($p) => ['cap' => $p[0], 'abbr' => $p[1]], $prods),
            ];
        }
        $matrixStages = [];
        $grandSold = 0;
        $grandOpps = 0;
        foreach ($stageAgg as $s) {
            $grandSold += (int) $s->v;
            $grandOpps += (int) $s->n;
            $matrixStages[] = [
                'label' => $s->label ?: (string) $s->stage,
                'sold' => (int) $s->v, 'opps' => (int) $s->n, 'accounts' => (int) $s->a,
                'cells' => $cellMap[(int) $s->stage] ?? [],
            ];
        }

        [$rc, $rp] = $this->filt('person', null, null);
        $rw = $rc ? ('WHERE '.implode(' AND ', $rc)) : '';
        $roles = [];
        foreach (['GTM', 'PreSales', 'Delivery'] as $role) {
            $r = $db->selectOne("
                SELECT
                  count(*) FILTER (WHERE ? = ANY(roles))::int AS total,
                  count(*) FILTER (WHERE ? = ANY(roles) AND coalesce(array_length(certified,1),0) > 0)::int AS certified
                FROM wh.person_current {$rw}", array_merge([$role, $role], $rp));
            $roles[$role] = ['total' => (int) $r->total, 'certified' => (int) $r->certified];
        }

        return [
            'sourced' => ['n' => (int) $o->src_n, 'v' => (int) $o->src_v],
            'influenced' => ['n' => (int) $o->inf_n, 'v' => (int) $o->inf_v],
            'pipeline' => ['n' => (int) $o->src_n + (int) $o->inf_n, 'v' => (int) $o->src_v + (int) $o->inf_v],
            'won' => ['n' => (int) $o->won_n, 'v' => (int) $o->won_v],
            'delivered' => (int) $p->delivered,
            'in_progress' => (int) $p->in_progress,
            'lob' => array_map(fn ($x) => [
                'lob' => $x->lob, 'open_n' => (int) $x->open_n, 'open_v' => (int) $x->open_v,
                'won_n' => (int) $x->won_n, 'won_v' => (int) $x->won_v,
            ], $lob),
            'roles' => $roles,
            'groups' => array_map(fn ($x) => [
                'lob' => $x->grp, 'open_n' => (int) $x->open_n, 'open_v' => (int) $x->open_v,
                'won_n' => (int) $x->won_n, 'won_v' => (int) $x->won_v,
            ], $groups),
            'caps' => array_map(fn ($x) => [
                'cap' => $x->cap, 'group' => $capLob[$x->cap] ?? '—',
                'open_n' => (int) $x->open_n, 'open_v' => (int) $x->open_v,
                'won_n' => (int) $x->won_n, 'won_v' => (int) $x->won_v,
            ], $caps),
            'matrix' => [
                'groups' => $matrixGroups,
                'stages' => $matrixStages,
                'total' => [
                    'sold' => $grandSold, 'opps' => $grandOpps, 'accounts' => $grandAccounts,
                    'cells' => $colTotal,
                ],
            ],
        ];
    }

    /**
     * Pipeline Overview — six distribution charts plus the account × product
     * matrix. All scoped by the same filt() (row-level visibility + pinned
     * filters). Money charts sum $ACV; Top products counts opportunities.
     *
     * @return array<string, mixed>
     */
    public function pipeline(): array
    {
        $db = $this->db();
        [$oc, $op] = $this->filt('opportunity', null, null);
        $ow = $oc ? ('WHERE '.implode(' AND ', $oc)) : '';

        $capLob = [];
        foreach (self::SALES_CATALOG as $lobKey => $prods) {
            foreach ($prods as [$name, $code]) {
                $capLob[$name] = $lobKey;
            }
        }
        $platform = array_map(fn ($p) => $p[0], self::SALES_CATALOG['Platform']);
        $platformArr = "ARRAY['".implode("','", array_map(fn ($n) => str_replace("'", "''", $n), $platform))."']::text[]";

        // Distribution by sales stage ($ACV), ordered by stage.
        $byStage = $db->select("
            SELECT stage_label AS k, coalesce(sum(acv),0)::bigint AS v
            FROM wh.opportunity_current {$ow}
            GROUP BY stage, stage_label ORDER BY stage", $op);

        // By line of business + by region ($ACV).
        $byLob = $db->select("
            SELECT coalesce(lob,'(unspecified)') AS k, coalesce(sum(acv),0)::bigint AS v
            FROM wh.opportunity_current {$ow} GROUP BY 1 ORDER BY v DESC", $op);
        $byGeo = $db->select("
            SELECT coalesce(geo,'(unspecified)') AS k, coalesce(sum(acv),0)::bigint AS v
            FROM wh.opportunity_current {$ow} GROUP BY 1 ORDER BY v DESC", $op);

        // Top products by opportunity count, plus deals with no product tagged.
        $topProducts = $db->select("
            SELECT cap AS k, count(*)::int AS v
            FROM wh.opportunity_current o, unnest(o.capabilities) cap
            {$ow}
            GROUP BY cap ORDER BY v DESC LIMIT 10", $op);
        $noProduct = (int) ($db->selectOne("
            SELECT count(*)::int AS n FROM wh.opportunity_current
            ".($ow ? $ow.' AND' : 'WHERE')." coalesce(array_length(capabilities,1),0) = 0", $op)->n ?? 0);

        // AI engagement + Apps-vs-Core ($ACV, each deal counted once).
        $ai = $db->selectOne("
            SELECT
              coalesce(sum(acv) FILTER (WHERE ncaps = 0),0)::bigint AS unassigned,
              coalesce(sum(acv) FILTER (WHERE ncaps > 0 AND is_ai),0)::bigint AS engaged,
              coalesce(sum(acv) FILTER (WHERE ncaps > 0 AND NOT coalesce(is_ai,false)),0)::bigint AS no_ai
            FROM (SELECT acv, is_ai, coalesce(array_length(capabilities,1),0) AS ncaps
                  FROM wh.opportunity_current {$ow}) s", $op);
        $appsCore = $db->selectOne("
            SELECT
              coalesce(sum(acv) FILTER (WHERE ncaps = 0),0)::bigint AS unassigned,
              coalesce(sum(acv) FILTER (WHERE ncaps > 0 AND napps > 0),0)::bigint AS apps,
              coalesce(sum(acv) FILTER (WHERE ncaps > 0 AND napps = 0),0)::bigint AS core
            FROM (
              SELECT acv, coalesce(array_length(capabilities,1),0) AS ncaps,
                coalesce(array_length(ARRAY(SELECT unnest(capabilities) EXCEPT SELECT unnest({$platformArr})),1),0) AS napps
              FROM wh.opportunity_current {$ow}) s", $op);

        // Account × product matrix — top deals by $ACV in scope.
        $rows = $db->select("
            SELECT account, use_case, partner, implementer, stage_label, coalesce(acv,0)::bigint AS acv,
              coalesce(capabilities, ARRAY[]::text[]) AS capabilities
            FROM wh.opportunity_current {$ow}
            ORDER BY acv DESC NULLS LAST LIMIT 300", $op);

        $matrixGroups = [];
        foreach (self::SALES_CATALOG as $lobKey => $prods) {
            $matrixGroups[] = [
                'lob' => $lobKey,
                'products' => array_map(fn ($p) => ['cap' => $p[0], 'abbr' => $p[1]], $prods),
            ];
        }
        $matrixRows = array_map(function ($r) {
            $caps = is_array($r->capabilities) ? $r->capabilities
                : array_filter(explode(',', trim((string) $r->capabilities, '{}')));
            return [
                'account' => $r->account, 'opportunity' => $r->use_case,
                'partner' => $r->partner, 'implementer' => $r->implementer,
                'stage' => $r->stage_label, 'acv' => (int) $r->acv,
                'caps' => array_fill_keys(array_map(fn ($c) => trim($c, '"'), $caps), true),
            ];
        }, $rows);

        // Products present anywhere in the scoped pipeline (drives "hide empty
        // columns" across all deals, not just the 300 shown in the matrix).
        $activeCaps = array_map(fn ($r) => $r->cap, $db->select("
            SELECT DISTINCT cap FROM wh.opportunity_current o, unnest(o.capabilities) cap {$ow}", $op));

        return [
            'activeCaps' => $activeCaps,
            'byStage' => array_map(fn ($r) => ['k' => $r->k, 'v' => (int) $r->v], $byStage),
            'byLob' => array_map(fn ($r) => ['k' => $r->k, 'v' => (int) $r->v], $byLob),
            'byGeo' => array_map(fn ($r) => ['k' => $r->k, 'v' => (int) $r->v], $byGeo),
            'topProducts' => array_merge(
                array_map(fn ($r) => ['k' => $r->k, 'v' => (int) $r->v, 'lob' => $capLob[$r->k] ?? '—'], $topProducts),
                $noProduct > 0 ? [['k' => 'No product', 'v' => $noProduct, 'lob' => '—']] : []
            ),
            'ai' => ['engaged' => (int) $ai->engaged, 'no_ai' => (int) $ai->no_ai, 'unassigned' => (int) $ai->unassigned],
            'appsCore' => ['apps' => (int) $appsCore->apps, 'core' => (int) $appsCore->core, 'unassigned' => (int) $appsCore->unassigned],
            'matrix' => ['groups' => $matrixGroups, 'rows' => $matrixRows],
        ];
    }

    /**
     * Readiness plan — Partner × path. For every partner and every app/foundation
     * path: Baseline (people certified now), Target (people the plan requires =
     * coverage-per-deal × demand deals) and Gap (Target − Baseline). Computed for
     * all three roles so the Role/Show toggles are instant client-side.
     *
     * topic = the certification name in person_current.certified[]; products = the
     * capabilities that create demand for the path.  planNeed = certified people
     * required per demanding deal, by role (GTM, PreSales, Delivery).
     */
    private const PLAN_NEED = [1, 1, 2];

    private const PLAN_PATHS = [
        ['abbr' => 'ADO', 'label' => 'ADO', 'foundational' => true, 'topic' => 'ADO', 'products' => ['Anaplan Data Orchestrator']],
        ['abbr' => 'Polaris', 'label' => 'L2 Polaris MB', 'foundational' => true, 'topic' => 'Polaris', 'products' => ['Polaris']],
        ['abbr' => 'TAWA', 'label' => 'Anaplan Way for Applications', 'foundational' => true, 'topic' => 'The Anaplan Way for Applications', 'products' => ['Core Platform']],
        ['abbr' => 'IFP', 'label' => 'IFP', 'foundational' => false, 'topic' => 'IFP', 'products' => ['Integrated Financial Planning']],
        ['abbr' => 'FCR', 'label' => 'FCR', 'foundational' => false, 'topic' => 'FCR', 'products' => ['Financial Consolidation (FCR)']],
        ['abbr' => 'IP', 'label' => 'Inventory Planning', 'foundational' => false, 'topic' => 'I&D', 'products' => ['Inventory Planning']],
        ['abbr' => 'DP', 'label' => 'Demand Planning', 'foundational' => false, 'topic' => 'I&D', 'products' => ['Demand Planning']],
        ['abbr' => 'OWP', 'label' => 'OWP', 'foundational' => false, 'topic' => 'OWP', 'products' => ['Workforce Planning']],
        ['abbr' => 'MFP', 'label' => 'MFP', 'foundational' => false, 'topic' => 'MFP', 'products' => ['Merchandise Financial Planning']],
        ['abbr' => 'PF', 'label' => 'Platform Foundations', 'foundational' => false, 'topic' => 'The Anaplan Way for Applications', 'products' => ['Core Platform']],
        ['abbr' => 'AI', 'label' => 'AI Foundations', 'foundational' => false, 'topic' => 'AI (other)', 'products' => ['CoModeler', 'Forecaster', 'Statistical Forecasting']],
        ['abbr' => 'T&Q', 'label' => 'Territory & Quota', 'foundational' => false, 'topic' => 'SPM', 'products' => ['Territory & Quota']],
        ['abbr' => 'Seg', 'label' => 'Segmentation and Scoring', 'foundational' => false, 'topic' => 'SPM', 'products' => ['Segmentation']],
        ['abbr' => 'GTM CP', 'label' => 'GTM Capacity Planning', 'foundational' => false, 'topic' => 'SPM', 'products' => []],
    ];

    /** @return array<string, mixed> */
    public function readinessPlan(): array
    {
        $db = $this->db();
        [$rc, $rp] = $this->filt('person', null, null);
        $rw = $rc ? ('WHERE '.implode(' AND ', $rc)) : '';
        $people = $db->select("SELECT partner, roles, certified FROM wh.person_current {$rw}", $rp);

        [$oc, $op] = $this->filt('opportunity', null, null);
        $ow = $oc ? ('WHERE '.implode(' AND ', $oc)) : '';
        $deals = $db->select("
            SELECT partner, implementer, stage, coalesce(capabilities, ARRAY[]::text[]) AS capabilities
            FROM wh.opportunity_current {$ow}", $op);

        $toArr = fn ($v) => is_array($v) ? $v
            : array_map(fn ($x) => trim($x, '"'), array_filter(explode(',', trim((string) $v, '{}'))));
        $roleIdx = ['GTM' => 0, 'PreSales' => 1, 'Delivery' => 2];

        // Baseline: people certified now, per partner / topic / role.
        $base = []; // [partner][topic][r]
        foreach ($people as $p) {
            $partner = $p->partner;
            if (! $partner) {
                continue;
            }
            $roles = $toArr($p->roles);
            $certs = $toArr($p->certified);
            foreach ($roles as $role) {
                if (! isset($roleIdx[$role])) {
                    continue;
                }
                $ri = $roleIdx[$role];
                foreach ($certs as $c) {
                    $base[$partner][$c][$ri] = ($base[$partner][$c][$ri] ?? 0) + 1;
                }
            }
        }

        // Demand: distinct deals per partner / role that touch a path's products.
        $demand = []; // [partner][r] = list of capability arrays
        foreach ($deals as $d) {
            $caps = $toArr($d->capabilities);
            $stage = (int) $d->stage;
            // GTM / PreSales — open deals attributed to the selling partner.
            if ($stage < 5 && $d->partner) {
                $demand[$d->partner][0][] = $caps;
                $demand[$d->partner][1][] = $caps;
            }
            // Delivery — all stages, attributed to the implementer (else seller).
            $owner = $d->implementer ?: $d->partner;
            if ($owner) {
                $demand[$owner][2][] = $caps;
            }
        }

        $partners = array_values(array_unique(array_merge(array_keys($base), array_keys($demand))));

        // Rows are limited to the partners the actor may see and, when set, the
        // pinned partner filter — a deal implemented by one of the actor's
        // partners must not pull its outside selling partner in as a row.
        $scopeP = $this->scope['partners'] ?? [];
        if ($scopeP) {
            $eff = $this->allowedPartners !== null ? array_intersect($scopeP, $this->allowedPartners) : $scopeP;
        } else {
            $eff = $this->allowedPartners; // null = all (admin / CoE)
        }
        if ($eff !== null) {
            $allow = array_flip(array_values($eff));
            $partners = array_values(array_filter($partners, fn ($p) => isset($allow[$p])));
        }

        $paths = self::PLAN_PATHS;

        $rows = [];
        foreach ($partners as $partner) {
            $cells = [];
            foreach ($paths as $path) {
                $b = [0, 0, 0];
                $req = [0, 0, 0];
                for ($ri = 0; $ri < 3; $ri++) {
                    $b[$ri] = $base[$partner][$path['topic']][$ri] ?? 0;
                    $prod = $path['products'];
                    if ($prod) {
                        $n = 0;
                        foreach (($demand[$partner][$ri] ?? []) as $caps) {
                            if (array_intersect($prod, $caps)) {
                                $n++;
                            }
                        }
                        $req[$ri] = self::PLAN_NEED[$ri] * $n;
                    }
                }
                $cells[] = ['base' => $b, 'req' => $req];
            }
            $rows[] = ['partner' => $partner, 'cells' => $cells];
        }

        // Sort by Delivery target (req[2]) desc, like the source.
        usort($rows, function ($a, $b) {
            $sa = array_sum(array_map(fn ($c) => $c['req'][2], $a['cells']));
            $sb = array_sum(array_map(fn ($c) => $c['req'][2], $b['cells']));
            return $sb <=> $sa ?: strcmp($a['partner'], $b['partner']);
        });

        return [
            'paths' => array_map(fn ($p) => ['abbr' => $p['abbr'], 'label' => $p['label'], 'foundational' => $p['foundational']], $paths),
            'rows' => $rows,
            'roles' => ['GTM', 'PreSales', 'Delivery'],
            'planNeed' => self::PLAN_NEED,
        ];
    }

    /**
     * Delivery — Use Cases status matrix + the project rows for Implementation
     * Health. Delivery readiness is READY when, for every product on the deal,
     * the delivering partner has at least one Delivery-certified person in that
     * product's topic. Columns fed by extracts we don't hold (Support, Polaris,
     * IHS, live use-case status) are returned empty for an honest empty-state.
     *
     * @return array<string, mixed>
     */
    public function delivery(): array
    {
        $db = $this->db();
        $toArr = fn ($v) => is_array($v) ? $v
            : array_map(fn ($x) => trim($x, '"'), array_filter(explode(',', trim((string) $v, '{}'))));

        // Delivery-certified people per partner per topic.
        [$rc, $rp] = $this->filt('person', null, null);
        $rw = $rc ? ('WHERE '.implode(' AND ', $rc)) : '';
        $certMap = [];
        foreach ($db->select("SELECT partner, roles, certified FROM wh.person_current {$rw}", $rp) as $p) {
            if (! $p->partner || ! in_array('Delivery', $toArr($p->roles), true)) {
                continue;
            }
            foreach ($toArr($p->certified) as $c) {
                $certMap[$p->partner][$c] = ($certMap[$p->partner][$c] ?? 0) + 1;
            }
        }
        // product => topic (first path that lists the product).
        $productTopic = [];
        foreach (self::PLAN_PATHS as $path) {
            foreach ($path['products'] as $prod) {
                $productTopic[$prod] = $productTopic[$prod] ?? $path['topic'];
            }
        }

        // Projects (delivery), for status/go-live lookup by account and the
        // Implementation Health grid.
        [$pc, $pp] = $this->filt('project', null, null);
        $pw = $pc ? ('WHERE '.implode(' AND ', $pc)) : '';
        $projRows = $db->select("
            SELECT source_id, name, account, partner, owner, go_live, health_partner, health_ps, health_cs
            FROM wh.project_current {$pw} ORDER BY go_live NULLS LAST", $pp);
        $projByAccount = [];
        foreach ($projRows as $pr) {
            if ($pr->account && ! isset($projByAccount[$pr->account])) {
                $projByAccount[$pr->account] = $pr->go_live;
            }
        }

        // Opportunities → Use Cases matrix rows.
        [$oc, $op] = $this->filt('opportunity', null, null);
        $ow = $oc ? ('WHERE '.implode(' AND ', $oc)) : '';
        $deals = $db->select("
            SELECT account, use_case, partner, implementer, stage, coalesce(acv,0)::bigint AS acv,
              coalesce(capabilities, ARRAY[]::text[]) AS capabilities
            FROM wh.opportunity_current {$ow} ORDER BY acv DESC NULLS LAST LIMIT 300", $op);

        $readyOf = function (string $owner, array $caps) use ($certMap, $productTopic) {
            $topics = [];
            foreach ($caps as $c) {
                if (isset($productTopic[$c])) {
                    $topics[$productTopic[$c]] = true;
                }
            }
            foreach (array_keys($topics) as $t) {
                if (($certMap[$owner][$t] ?? 0) < 1) {
                    return false;
                }
            }
            return true;
        };

        $matrixGroups = [];
        foreach (self::SALES_CATALOG as $lobKey => $prods) {
            $matrixGroups[] = ['lob' => $lobKey, 'products' => array_map(fn ($p) => ['cap' => $p[0], 'abbr' => $p[1]], $prods)];
        }

        $inDelivery = $notStarted = $ledByPartner = $ready = $notReady = 0;
        $activeCaps = [];
        $rows = array_map(function ($d) use ($projByAccount, $readyOf, $toArr, &$inDelivery, &$notStarted, &$ledByPartner, &$ready, &$notReady, &$activeCaps) {
            $caps = $toArr($d->capabilities);
            foreach ($caps as $c) {
                $activeCaps[$c] = true;
            }
            $owner = $d->implementer ?: $d->partner;
            $hasProject = isset($projByAccount[$d->account]);
            $status = $hasProject ? 'In delivery' : 'Not started';
            $hasProject ? $inDelivery++ : $notStarted++;
            $led = ! empty($d->implementer);
            $led && $ledByPartner++;
            $isReady = $readyOf($owner ?: '', $caps);
            $isReady ? $ready++ : $notReady++;

            return [
                'account' => $d->account, 'opportunity' => $d->use_case,
                'implementer' => $d->implementer ?: $d->partner, 'sales_only' => empty($d->implementer) && ! empty($d->partner),
                'status' => $status, 'led_by' => $led ? 'Partner led' : '—',
                'go_live' => $projByAccount[$d->account] ?? null,
                'ready' => $isReady, 'caps' => array_fill_keys($caps, true),
            ];
        }, $deals);

        return [
            'stats' => [
                'deals' => count($deals), 'live' => 0, 'in_delivery' => $inDelivery, 'not_started' => $notStarted,
                'ready' => $ready, 'not_ready' => $notReady, 'led_by_partner' => $ledByPartner,
                'apps_in_play' => count($activeCaps),
            ],
            'matrix' => ['groups' => $matrixGroups, 'rows' => $rows],
            'activeCaps' => array_keys($activeCaps),
            'projects' => array_map(fn ($p) => [
                'key' => $p->source_id, 'name' => $p->name, 'account' => $p->account,
                'partner' => $p->partner, 'owner' => $p->owner, 'go_live' => $p->go_live,
                'health_ps' => $p->health_ps, 'health_cs' => $p->health_cs,
            ], $projRows),
        ];
    }

    /**
     * Capability — practice capability coverage. People by role (total vs
     * certified), certifications across the practice (certified + in-progress),
     * and a partner × certification grid. Fully scoped by filt('person'), which
     * applies row-level visibility and the pinned partner filter.
     *
     * @return array<string, mixed>
     */
    public function capability(): array
    {
        $db = $this->db();
        [$rc, $rp] = $this->filt('person', null, null);
        $rw = $rc ? ('WHERE '.implode(' AND ', $rc)) : '';

        $tot = $db->selectOne("
            SELECT count(*)::int AS people,
              count(*) FILTER (WHERE coalesce(array_length(certified,1),0) > 0)::int AS certified_any,
              count(*) FILTER (WHERE coalesce(array_length(in_progress,1),0) > 0)::int AS inprogress_any
            FROM wh.person_current {$rw}", $rp);

        $roles = [];
        foreach (['GTM', 'PreSales', 'Delivery'] as $role) {
            $r = $db->selectOne("
                SELECT count(*) FILTER (WHERE ? = ANY(roles))::int AS total,
                  count(*) FILTER (WHERE ? = ANY(roles) AND coalesce(array_length(certified,1),0) > 0)::int AS certified
                FROM wh.person_current {$rw}", array_merge([$role, $role], $rp));
            $roles[$role] = ['total' => (int) $r->total, 'certified' => (int) $r->certified];
        }

        $cert = [];
        foreach ($db->select("
            SELECT c AS k, count(*)::int AS n FROM wh.person_current p, unnest(p.certified) c
            {$rw} GROUP BY c", $rp) as $row) {
            $cert[$row->k] = ['certified' => (int) $row->n, 'in_progress' => 0];
        }
        foreach ($db->select("
            SELECT ip AS k, count(*)::int AS n FROM wh.person_current p, unnest(p.in_progress) ip
            {$rw} GROUP BY ip", $rp) as $row) {
            $cert[$row->k] = $cert[$row->k] ?? ['certified' => 0, 'in_progress' => 0];
            $cert[$row->k]['in_progress'] = (int) $row->n;
        }
        $certs = [];
        foreach ($cert as $k => $v) {
            $certs[] = ['name' => $k, 'certified' => $v['certified'], 'in_progress' => $v['in_progress']];
        }
        usort($certs, fn ($a, $b) => $b['certified'] <=> $a['certified'] ?: strcmp($a['name'], $b['name']));

        // Partner × certification grid (certified counts).
        $grid = [];
        foreach ($db->select("
            SELECT partner, c AS cert, count(*)::int AS n FROM wh.person_current p, unnest(p.certified) c
            {$rw} GROUP BY partner, c", $rp) as $row) {
            if ($row->partner) {
                $grid[$row->partner][$row->cert] = (int) $row->n;
            }
        }
        $partners = array_keys($grid);
        sort($partners);
        $certNames = array_map(fn ($c) => $c['name'], $certs);

        return [
            'totals' => [
                'people' => (int) $tot->people, 'certified_any' => (int) $tot->certified_any,
                'inprogress_any' => (int) $tot->inprogress_any, 'distinct_certs' => count($certs),
            ],
            'roles' => $roles,
            'certs' => $certs,
            'grid' => ['partners' => $partners, 'certs' => $certNames, 'cells' => $grid],
        ];
    }

    /**
     * Enablement — two sub-views, both scoped. "By individual" is the people
     * roster with certifications and in-progress paths; "By product & role" is
     * practice coverage vs demand per path (certified / in-progress / target /
     * gap by role).
     *
     * @return array<string, mixed>
     */
    public function enablement(): array
    {
        $db = $this->db();
        $toArr = fn ($v) => is_array($v) ? $v
            : array_map(fn ($x) => trim($x, '"'), array_filter(explode(',', trim((string) $v, '{}'))));

        [$rc, $rp] = $this->filt('person', null, null);
        $rw = $rc ? ('WHERE '.implode(' AND ', $rc)) : '';
        $ppl = $db->select("
            SELECT name, email, partner, roles, certified, in_progress
            FROM wh.person_current {$rw} ORDER BY partner NULLS LAST, name", $rp);

        $people = array_map(fn ($p) => [
            'name' => $p->name, 'partner' => $p->partner,
            'roles' => $toArr($p->roles), 'certified' => $toArr($p->certified), 'in_progress' => $toArr($p->in_progress),
        ], $ppl);

        // Practice coverage per path/role.
        $roleIdx = ['GTM' => 0, 'PreSales' => 1, 'Delivery' => 2];
        $certRT = [];  // [topic][r]
        $inprog = [];  // [topic]
        foreach ($people as $p) {
            foreach ($p['certified'] as $c) {
                foreach ($p['roles'] as $role) {
                    if (isset($roleIdx[$role])) {
                        $certRT[$c][$roleIdx[$role]] = ($certRT[$c][$roleIdx[$role]] ?? 0) + 1;
                    }
                }
            }
            foreach ($p['in_progress'] as $ip) {
                $inprog[$ip] = ($inprog[$ip] ?? 0) + 1;
            }
        }

        [$oc, $op] = $this->filt('opportunity', null, null);
        $ow = $oc ? ('WHERE '.implode(' AND ', $oc)) : '';
        $deals = $db->select("
            SELECT stage, coalesce(capabilities, ARRAY[]::text[]) AS capabilities
            FROM wh.opportunity_current {$ow}", $op);
        $dealCaps = array_map(fn ($d) => ['stage' => (int) $d->stage, 'caps' => $toArr($d->capabilities)], $deals);

        $coverage = [];
        foreach (self::PLAN_PATHS as $path) {
            $topic = $path['topic'];
            $prod = $path['products'];
            $demand = [0, 0, 0];
            if ($prod) {
                foreach ($dealCaps as $d) {
                    if (! array_intersect($prod, $d['caps'])) {
                        continue;
                    }
                    if ($d['stage'] < 5) {
                        $demand[0]++;
                        $demand[1]++;
                    }
                    $demand[2]++; // delivery counts all stages
                }
            }
            $certified = [$certRT[$topic][0] ?? 0, $certRT[$topic][1] ?? 0, $certRT[$topic][2] ?? 0];
            $target = [self::PLAN_NEED[0] * $demand[0], self::PLAN_NEED[1] * $demand[1], self::PLAN_NEED[2] * $demand[2]];
            $gap = [max(0, $target[0] - $certified[0]), max(0, $target[1] - $certified[1]), max(0, $target[2] - $certified[2])];
            $coverage[] = [
                'abbr' => $path['abbr'], 'label' => $path['label'], 'foundational' => $path['foundational'],
                'certified' => $certified, 'in_progress' => $inprog[$topic] ?? 0,
                'target' => $target, 'gap' => $gap,
            ];
        }

        return [
            'people' => $people,
            'coverage' => $coverage,
            'roles' => ['GTM', 'PreSales', 'Delivery'],
            'planNeed' => self::PLAN_NEED,
        ];
    }

    /** Whether the given project (by warehouse source_id) is visible to the actor. */
    public function deliveryProjectVisible(string $projectKey): bool
    {
        [$pc, $pp] = $this->filt('project', null, null);
        $clauses = array_merge(['source_id = ?'], $pc);
        $params = array_merge([$projectKey], $pp);

        return (bool) $this->db()->selectOne(
            'SELECT 1 FROM wh.project_current WHERE '.implode(' AND ', $clauses).' LIMIT 1', $params
        );
    }

    public function breakdown(string $key, ?string $geo = null, ?string $partner = null): array
    {
        $cfg = self::BK[$key] ?? abort(404);
        $geo = $this->geo($geo);
        $base = $cfg['base'];
        $b = self::BASES[$base];
        $clauses = [];
        $params = [];
        if ($cfg['pi']) {
            $clauses[] = self::PI;
        }
        [$fc, $fp] = $this->filt($base, $geo, $partner);
        $clauses = array_merge($clauses, $fc);
        $params = array_merge($params, $fp);
        $wsql = $clauses ? ('WHERE '.implode(' AND ', $clauses)) : '';
        $acvSel = $base === 'opportunity' ? 'acv' : 'NULL::bigint';
        $inner = "SELECT {$cfg['grp']} AS key, {$acvSel} AS acv FROM {$b['cur']} {$wsql}";
        $rows = $this->db()->select(
            "SELECT coalesce(key::text,'(unspecified)') key, count(*)::int c, sum(coalesce(acv,0))::bigint a FROM ({$inner}) t GROUP BY key ORDER BY {$cfg['ord']}", $params
        );

        return array_map(fn ($r) => ['key' => $r->key, 'count' => (int) $r->c, 'acv' => (int) $r->a], $rows);
    }

    public function drillBreakdown(string $key, string $value, ?string $geo = null, ?string $partner = null): array
    {
        $cfg = self::BK[$key] ?? abort(404);
        $geo = $this->geo($geo);
        $base = $cfg['base'];
        $b = self::BASES[$base];
        $clauses = [];
        $shown = [];
        $params = [];
        if ($cfg['pi']) {
            $clauses[] = self::PI;
            $shown[] = self::PI;
        }
        [$fc, $fp, $fs] = $this->filt($base, $geo, $partner);
        $clauses = array_merge($clauses, $fc);
        $params = array_merge($params, $fp);
        $shown = array_merge($shown, $fs);
        $ve = str_replace("'", "''", $value);
        if (! empty($cfg['arr'])) {
            $clauses[] = "? = ANY({$cfg['arr']})";
            $shown[] = "'{$ve}' = ANY({$cfg['arr']})";
            $params[] = $value;
        } elseif ($value === '(unspecified)') {
            $clauses[] = "{$cfg['col']} IS NULL";
            $shown[] = "{$cfg['col']} IS NULL";
        } else {
            $clauses[] = "{$cfg['col']} = ?";
            $shown[] = "{$cfg['col']} = '{$ve}'";
            $params[] = $value;
        }
        $cols = implode(', ', $b['cols']);
        $rows = $this->db()->select("SELECT {$cols} FROM {$b['cur']} WHERE ".implode(' AND ', $clauses)." ORDER BY {$b['order']}", $params);
        $sql = "SELECT {$cols}\nFROM {$b['cur']}\nWHERE ".implode(' AND ', $shown)."\nORDER BY {$b['order']}";

        return $this->payload("{$key} = {$value}", 'Rows where '.implode(' AND ', $shown), $sql, $rows);
    }

    private function payload(string $title, string $desc, string $sql, array $rows): array
    {
        $prov = [];
        foreach ($rows as $r) {
            $a = $r->load_run_id ?? null;
            $bb = $r->source_file ?? null;
            $c = $r->snapshot_date ?? null;
            $prov[$a.'|'.$bb.'|'.$c] = ['load_run_id' => $a, 'source_file' => $bb, 'snapshot_date' => $c];
        }
        ksort($prov);
        $cols = $rows ? array_keys((array) $rows[0]) : [];

        return [
            'title' => $title, 'description' => $desc, 'definition_sql' => $sql,
            'row_count' => count($rows),
            'provenance' => array_values($prov),
            'columns' => $cols,
            'rows' => array_map(fn ($r) => (array) $r, $rows),
        ];
    }
}
