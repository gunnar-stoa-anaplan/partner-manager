<?php

namespace App\Services;

use App\Models\Journey;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use PDO;
use PDOException;

class JourneyDatabase
{
    /**
     * Stage definitions — keyed by stage number (1-based).
     *
     * @var array<int, array{name: string, tasks: int}>
     */
    public const STAGES = [
        1 => ['name' => 'Registered', 'tasks' => 3],
        2 => ['name' => 'Trained', 'tasks' => 3],
        3 => ['name' => 'Credentialized', 'tasks' => 3],
        4 => ['name' => 'Delivering', 'tasks' => 3],
        5 => ['name' => 'Expert', 'tasks' => 3],
    ];

    private ?PDO $pdo = null;

    public function __construct(private readonly Journey $journey) {}

    /**
     * Return all participants from the journey SQLite database.
     *
     * @return Collection<int, object>
     */
    public function participants(): Collection
    {
        return $this->query('SELECT * FROM participants ORDER BY name ASC');
    }

    /**
     * Return all pending actions from the journey SQLite database.
     *
     * @return Collection<int, object>
     */
    public function pendingActions(): Collection
    {
        return $this->query('SELECT * FROM pending_actions ORDER BY created_at DESC');
    }

    /**
     * Return the most recent agent log entries.
     *
     * @return Collection<int, object>
     */
    public function agentLog(int $limit = 30): Collection
    {
        return $this->query(
            'SELECT * FROM agent_log ORDER BY created_at DESC LIMIT :limit',
            [':limit' => $limit]
        );
    }

    /**
     * Return stage history entries.
     *
     * @return Collection<int, object>
     */
    public function stageHistory(): Collection
    {
        return $this->query('SELECT * FROM stage_history ORDER BY changed_at DESC');
    }

    /**
     * Execute a raw SELECT query and return results as a Collection of objects.
     * Handles missing/unreadable DB gracefully by returning an empty Collection.
     *
     * @param  array<string, mixed>  $bindings
     * @return Collection<int, object>
     */
    private function query(string $sql, array $bindings = []): Collection
    {
        $pdo = $this->connection();

        if ($pdo === null) {
            return collect();
        }

        try {
            $statement = $pdo->prepare($sql);

            foreach ($bindings as $key => $value) {
                $statement->bindValue($key, $value);
            }

            $statement->execute();

            return collect($statement->fetchAll(PDO::FETCH_OBJ));
        } catch (PDOException $e) {
            Log::warning('JourneyDatabase query failed', [
                'journey_key' => $this->journey->journey_key,
                'sql' => $sql,
                'error' => $e->getMessage(),
            ]);

            return collect();
        }
    }

    /**
     * Open a read-only PDO connection to the journey SQLite file.
     * Returns null and logs a warning if the file is missing or unreadable.
     */
    private function connection(): ?PDO
    {
        if ($this->pdo !== null) {
            return $this->pdo;
        }

        $path = $this->journey->db_path;

        if (! $path || ! file_exists($path) || ! is_readable($path)) {
            Log::warning('JourneyDatabase: SQLite file missing or unreadable', [
                'journey_key' => $this->journey->journey_key,
                'db_path' => $path,
            ]);

            return null;
        }

        try {
            $this->pdo = new PDO(
                dsn: "sqlite:{$path}",
                options: [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                ]
            );

            // Read-only via PRAGMA — prevents accidental writes
            $this->pdo->exec('PRAGMA query_only = ON;');

            return $this->pdo;
        } catch (PDOException $e) {
            Log::warning('JourneyDatabase: failed to open SQLite connection', [
                'journey_key' => $this->journey->journey_key,
                'db_path' => $path,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
