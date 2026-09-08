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
