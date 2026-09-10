<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * Smoke-test every GET screen as a super-admin and report the ones that error.
 *
 * Runs the requests in-process through the HTTP kernel, so it needs neither a
 * running web server nor a temporary login route — just the database. Point it
 * at CI or run it after any change instead of clicking through screens.
 *
 *   php artisan screens:crawl                # all parameterless screens
 *   php artisan screens:crawl --details      # also sample one {id} per resource
 *   php artisan screens:crawl --json         # machine-readable output
 *
 * Exit code = number of 5xx failures (0 = clean), so it can gate a pipeline.
 */
class CrawlScreens extends Command
{
    protected $signature = 'screens:crawl
        {--user= : User id to act as (defaults to a super_admin)}
        {--details : also crawl single-parameter {id} routes, sampling one real record}
        {--json : output JSON instead of a table}';

    protected $description = 'Crawl every GET screen as super-admin and report render errors';

    /** URI prefixes that are not user-facing screens. */
    private const SKIP = ['_', 'api/', 'sanctum', 'storage', 'livewire', 'telescope', 'up', 'build/', 'mcp/'];

    public function handle(HttpKernel $kernel): int
    {
        $userId = $this->option('user')
            ?: optional(User::whereHas('role', fn ($q) => $q->where('slug', 'super_admin'))->first())->id;
        $user = $userId ? User::find($userId) : null;
        if (! $user) {
            $this->error('No super_admin user found. Pass --user=<id>.');

            return self::FAILURE;
        }

        $targets = $this->targets((bool) $this->option('details'));
        $results = [];
        foreach ($targets as $t) {
            Auth::loginUsingId($user->id); // guard is a singleton; keeps us authed across sub-requests
            $req = Request::create($t['url'], 'GET', server: ['HTTP_ACCEPT' => 'application/json']);
            try {
                $res = $kernel->handle($req);
                $code = $res->getStatusCode();
                $err = $code >= 500 ? $this->extract($res->getContent()) : '';
            } catch (\Throwable $e) {
                $code = 500;
                $err = class_basename($e).': '.$e->getMessage().'  '.basename($e->getFile()).':'.$e->getLine();
            }
            $results[] = ['name' => $t['name'], 'url' => $t['url'], 'code' => $code, 'error' => $err];
        }

        $fails = array_values(array_filter($results, fn ($r) => $r['code'] >= 500));
        $warn = array_values(array_filter($results, fn ($r) => in_array($r['code'], [403, 405, 419], true)));

        if ($this->option('json')) {
            $this->line(json_encode(['crawled' => count($results), 'failures' => $fails, 'warnings' => $warn], JSON_PRETTY_PRINT));

            return count($fails);
        }

        $this->line('');
        $this->line("Crawled <info>".count($results)."</info> screens as <comment>{$user->name}</comment>: "
            .'<error>'.count($fails).' failed</error>, '.count($warn).' to review.');
        foreach ($fails as $f) {
            $this->line("\n<error>[{$f['code']}]</error> {$f['url']}  <fg=gray>({$f['name']})</>");
            $this->line('      '.$f['error']);
        }
        if ($warn) {
            $this->line("\n<comment>Review (non-5xx, expected for some non-screens like MCP/Pulse):</comment>");
            foreach ($warn as $w) {
                $this->line("  [{$w['code']}] {$w['url']}");
            }
        }
        $this->line('');

        return count($fails);
    }

    /** @return array<int, array{name: string, url: string}> */
    private function targets(bool $details): array
    {
        $out = [];
        $seen = [];
        foreach (Route::getRoutes() as $route) {
            if (! in_array('GET', $route->methods(), true)) {
                continue;
            }
            $uri = $route->uri();
            if (Str::startsWith($uri, self::SKIP)) {
                continue;
            }
            $params = $route->parameterNames();

            if (count($params) === 0) {
                if (isset($seen[$uri])) {
                    continue;
                }
                $seen[$uri] = true;
                $out[] = ['name' => $route->getName() ?: $uri, 'url' => '/'.ltrim($uri, '/')];
            } elseif ($details && count($params) === 1) {
                $id = $this->sampleId($params[0]);
                if ($id !== null) {
                    $url = '/'.ltrim(str_replace(['{'.$params[0].'}', '{'.$params[0].'?}'], (string) $id, $uri), '/');
                    if (! isset($seen[$url])) {
                        $seen[$url] = true;
                        $out[] = ['name' => $route->getName() ?: $uri, 'url' => $url];
                    }
                }
            }
        }

        return $out;
    }

    /** Best-effort: first id of the table named after the route parameter. */
    private function sampleId(string $param): int|string|null
    {
        $table = Str::plural(Str::snake($param));
        try {
            if (! DB::getSchemaBuilder()->hasTable($table)) {
                return null;
            }

            return DB::table($table)->value('id');
        } catch (\Throwable) {
            return null;
        }
    }

    /** Pull the exception summary out of a JSON (APP_DEBUG) error response. */
    private function extract(string $body): string
    {
        $j = json_decode($body, true);
        if (is_array($j) && isset($j['message'])) {
            $loc = isset($j['file']) ? '  '.basename($j['file']).':'.($j['line'] ?? '') : '';

            return Str::limit((string) $j['message'], 200).$loc;
        }

        return 'HTTP 500 (enable APP_DEBUG for detail)';
    }
}
