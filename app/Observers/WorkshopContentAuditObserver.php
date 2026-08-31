<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

/**
 * Shared observer for every workshop-content model that needs an audit
 * trail (pages, survey templates, the credential pool) - one class,
 * attached to each model in AppServiceProvider, rather than a near-
 * identical observer per model.
 */
class WorkshopContentAuditObserver
{
    public function created(Model $model): void
    {
        $this->record($model, 'created', null, $this->auditable($model, $model->getAttributes()));
    }

    public function updated(Model $model): void
    {
        $changes = $model->getChanges();
        unset($changes['updated_at']);

        if (empty($changes)) {
            return;
        }

        $before = collect($model->getOriginal())->only(array_keys($changes))->all();

        $this->record($model, 'updated', $this->auditable($model, $before), $this->auditable($model, $changes));
    }

    public function deleted(Model $model): void
    {
        $this->record($model, 'deleted', $this->auditable($model, $model->getAttributes()), null);
    }

    /**
     * @param  array<string, mixed>|null  $before
     * @param  array<string, mixed>|null  $after
     */
    private function record(Model $model, string $action, ?array $before, ?array $after): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'auditable_type' => $model::class,
            'auditable_id' => $model->getKey(),
            'before' => $before,
            'after' => $after,
        ]);
    }

    /**
     * Strips fields a model marks as never-auditable (e.g. an encrypted
     * credential secret) so they never land in the log's before/after JSON,
     * even in an unreadable/encrypted form.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function auditable(Model $model, array $attributes): array
    {
        $excluded = defined($model::class.'::AUDIT_EXCLUDED') ? $model::AUDIT_EXCLUDED : [];

        return collect($attributes)->except($excluded)->all();
    }
}
