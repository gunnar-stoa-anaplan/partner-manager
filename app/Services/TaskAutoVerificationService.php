<?php

namespace App\Services;

use App\Models\PartnerTask;
use App\Models\Partner;
use Illuminate\Support\Facades\DB;

class TaskAutoVerificationService
{
    public function verifyAll(): int
    {
        $tasks = PartnerTask::whereIn('status', ['not_started', 'in_progress'])
            ->whereHas('template', function ($q) {
                $q->whereNotNull('auto_verify_rule');
            })
            ->with(['template', 'partner'])
            ->get();
        
        $verified = 0;
        foreach ($tasks as $task) {
            if ($this->evaluate($task->partner, $task->template->auto_verify_rule)) {
                $task->update([
                    'status' => 'completed',
                    'auto_verified' => true,
                    'auto_verified_at' => now(),
                    'completed_at' => now(),
                    'completion_evidence' => 'Automatically verified by system rule: ' . $task->template->auto_verify_rule,
                ]);
                $verified++;
            }
        }
        
        return $verified;
    }
    
    protected function evaluate(Partner $partner, string $rule): bool
    {
        try {
            // Parse rules like "partner.salesforce_id IS NOT NULL"
            if (str_contains($rule, 'IS NOT NULL')) {
                $field = trim(str_replace(['partner.', ' IS NOT NULL'], '', $rule));
                return !empty($partner->$field);
            }
            
            // Parse rules like "certifications.active.count >= 2"
            if (preg_match('/^(\w+)\.(\w+)?\.?(\w+)?\s*(>=|<=|>|<|=)\s*(\d+\.?\d*)$/', $rule, $m)) {
                $table = $m[1];
                $modifier = $m[2] ?? null;
                $aggregate = $m[3] ?? 'count';
                $operator = $m[4];
                $threshold = (float) $m[5];
                
                $value = $this->getMetricValue($partner, $table, $modifier, $aggregate);
                
                return match ($operator) {
                    '>=' => $value >= $threshold,
                    '<=' => $value <= $threshold,
                    '>' => $value > $threshold,
                    '<' => $value < $threshold,
                    '=' => $value == $threshold,
                    default => false,
                };
            }
            
            return false;
        } catch (\Throwable $e) {
            return false;
        }
    }
    
    protected function getMetricValue(Partner $partner, string $table, ?string $modifier, string $aggregate): float
    {
        return match ($table) {
            'certifications' => DB::table('certifications')
                ->where('partner_id', $partner->id)
                ->when($modifier === 'active', fn($q) => $q->where('status', 'active'))
                ->when($aggregate === 'count', fn($q) => $q->count(), fn($q) => $q->sum($aggregate)),
            'opportunities' => DB::table('opportunities')
                ->where('partner_id', $partner->id)
                ->when($modifier === 'open', fn($q) => $q->whereNotIn('stage', ['Closed Won', 'Closed Lost']))
                ->when($aggregate === 'count', fn($q) => $q->count())
                ->when(str_starts_with($aggregate, 'sum('), function ($q) use ($aggregate) {
                    $col = str_replace(['sum(', ')'], '', $aggregate);
                    return $q->sum($col);
                }, fn($q) => $aggregate === 'count' ? $q : $q->count()),
            'projects' => DB::table('projects')
                ->where('partner_id', $partner->id)
                ->when($modifier === 'completed', fn($q) => $q->where('status', 'completed'))
                ->count(),
            'contacts' => DB::table('contacts')->where('partner_id', $partner->id)->count(),
            'partner_events' => DB::table('partner_events')
                ->where('partner_id', $partner->id)
                ->when($modifier === 'qbr', fn($q) => $q->where('type', 'qbr'))
                ->when(str_contains($aggregate, 'trailing_12m'), fn($q) => $q->where('start_at', '>=', now()->subMonths(12)))
                ->count(),
            'blueprints' => DB::table('partner_blueprint_assignments')
                ->where('partner_id', $partner->id)
                ->when($modifier === 'completed', fn($q) => $q->where('status', 'completed'))
                ->count(),
            'health_score' => DB::table('partner_scorecards')
                ->where('partner_id', $partner->id)
                ->orderByDesc('scored_at')
                ->value($modifier ?? 'overall_score') ?? 0,
            default => 0,
        };
    }
}
