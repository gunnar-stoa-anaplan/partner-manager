<?php

namespace App\Services;

use App\Models\Import;
use App\Models\Opportunity;
use App\Models\Partner;
use App\Models\PartnerAlert;

class ChangeDetectionService
{
    public function detectChangesForImport(Import $import): array
    {
        $changes = [
            'new_opportunities' => 0,
            'stage_changes' => 0,
            'close_date_slips' => 0,
        ];

        $opportunities = Opportunity::where('import_id', $import->id)
            ->with('partner')
            ->get();

        foreach ($opportunities as $opp) {
            if (!$opp->partner) {
                continue;
            }

            // 1. New opportunity detection
            if ($opp->first_seen_at && $opp->first_seen_at->gte($import->started_at->copy()->subMinutes(5))) {
                $this->createNewOpportunityAlert($opp);
                $changes['new_opportunities']++;
            }

            // 2. Stage change detection
            if ($opp->previous_stage && $opp->previous_stage !== $opp->stage) {
                $this->createStageChangeAlert($opp);
                $changes['stage_changes']++;
            }

            // 3. Close date slip detection
            if ($opp->previous_close_date && $opp->close_date
                && $opp->close_date->gt($opp->previous_close_date)) {
                $this->createCloseDateSlipAlert($opp);
                $changes['close_date_slips']++;
            }
        }

        return $changes;
    }

    protected function createNewOpportunityAlert(Opportunity $opp): void
    {
        $amount = (float) $opp->amount;
        $severity = $amount > 100000 ? 'positive' : 'info';
        $fmtAmount = '$' . number_format($amount, 0);
        $productArea = $opp->product_area ? " — {$opp->product_area}" : '';
        $closeDate = $opp->close_date ? $opp->close_date->format('M j, Y') : 'TBD';

        $this->createAlert(
            $opp->partner,
            'new_opportunity',
            $severity,
            "New opportunity: {$opp->opportunity_name}",
            "{$opp->opportunity_name}{$productArea} — {$fmtAmount} at {$opp->stage}. Close date: {$closeDate}.",
            "Review the opportunity details and pipeline impact.",
            [
                'opportunity_id' => $opp->id,
                'opportunity_name' => $opp->opportunity_name,
                'amount' => $amount,
                'stage' => $opp->stage,
                'product_area' => $opp->product_area,
                'partner_name' => $opp->partner->name,
            ]
        );
    }

    protected function createStageChangeAlert(Opportunity $opp): void
    {
        $amount = (float) $opp->amount;
        $fmtAmount = '$' . number_format($amount, 0);
        $stageOrder = [
            'Prospecting', 'Qualification', 'Needs Analysis',
            'Value Proposition', 'Proposal/Price Quote',
            'Negotiation/Review', 'Closed Won', 'Closed Lost',
        ];

        $fromIdx = array_search($opp->previous_stage, $stageOrder);
        $toIdx = array_search($opp->stage, $stageOrder);
        $isForward = $toIdx !== false && $fromIdx !== false && $toIdx > $fromIdx;

        // Determine severity
        if ($opp->stage === 'Closed Won') {
            $severity = 'positive';
            $description = "Closed Won — {$fmtAmount}" . ($opp->revenue_type ? " ({$opp->revenue_type})" : '') . ".";
            $action = "Celebrate the win and update forecasts.";
        } elseif ($opp->stage === 'Closed Lost') {
            $severity = 'warning';
            $description = "Closed Lost — {$fmtAmount} was at {$opp->previous_stage}.";
            $action = "Review loss reasons and capture learnings.";
        } elseif ($isForward) {
            $severity = 'positive';
            $description = "Moved from {$opp->previous_stage} → {$opp->stage}. Amount: {$fmtAmount}.";
            $action = "Pipeline is progressing — review next steps.";
        } else {
            $severity = 'warning';
            $description = "Moved back from {$opp->previous_stage} → {$opp->stage}. Amount: {$fmtAmount}.";
            $action = "Investigate why this deal regressed and assess risk.";
        }

        $titleVerb = match (true) {
            $opp->stage === 'Closed Won' => 'Deal won',
            $opp->stage === 'Closed Lost' => 'Deal lost',
            $isForward => 'Stage advance',
            default => 'Stage regression',
        };

        $this->createAlert(
            $opp->partner,
            'stage_change',
            $severity,
            "{$titleVerb}: {$opp->opportunity_name}",
            $description,
            $action,
            [
                'opportunity_id' => $opp->id,
                'opportunity_name' => $opp->opportunity_name,
                'amount' => $amount,
                'from_stage' => $opp->previous_stage,
                'to_stage' => $opp->stage,
                'direction' => $isForward ? 'forward' : 'backward',
                'partner_name' => $opp->partner->name,
            ]
        );
    }

    protected function createCloseDateSlipAlert(Opportunity $opp): void
    {
        $amount = (float) $opp->amount;
        $fmtAmount = '$' . number_format($amount, 0);
        $slipDays = $opp->previous_close_date->diffInDays($opp->close_date);
        $fromDate = $opp->previous_close_date->format('M j, Y');
        $toDate = $opp->close_date->format('M j, Y');

        if ($slipDays > 90) {
            $severity = 'critical';
        } elseif ($slipDays >= 30) {
            $severity = 'warning';
        } else {
            $severity = 'info';
        }

        $this->createAlert(
            $opp->partner,
            'close_date_slip',
            $severity,
            "Close date slipped: {$opp->opportunity_name}",
            "Pushed {$slipDays} days from {$fromDate} to {$toDate}. Amount: {$fmtAmount}.",
            "Review deal timeline and assess impact on quarterly targets.",
            [
                'opportunity_id' => $opp->id,
                'opportunity_name' => $opp->opportunity_name,
                'amount' => $amount,
                'from_date' => $opp->previous_close_date->toDateString(),
                'to_date' => $opp->close_date->toDateString(),
                'slip_days' => $slipDays,
                'partner_name' => $opp->partner->name,
            ]
        );
    }

    protected function createAlert(Partner $partner, string $type, string $severity, string $title, string $description, string $action, array $data = []): void
    {
        // Don't create duplicate active alerts for same partner/type/title
        $exists = PartnerAlert::where('partner_id', $partner->id)
            ->where('type', $type)
            ->where('status', 'active')
            ->where('title', $title)
            ->exists();

        if ($exists) {
            return;
        }

        PartnerAlert::create([
            'partner_id' => $partner->id,
            'type' => $type,
            'severity' => $severity,
            'title' => $title,
            'description' => $description,
            'recommended_action' => $action,
            'data' => $data,
        ]);
    }
}
