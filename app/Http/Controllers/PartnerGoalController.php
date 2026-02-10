<?php

namespace App\Http\Controllers;

use App\Helpers\FiscalYearHelper;
use App\Models\Partner;
use App\Models\PartnerGoal;
use Illuminate\Http\Request;

class PartnerGoalController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $currentFY = FiscalYearHelper::getCurrentFiscalPeriod()['fiscal_year'];
        $selectedFY = (int) $request->query('fy', $currentFY);

        if ($user->role === 'admin') {
            $partners = Partner::withOnly([])
                ->orderBy('name')
                ->get();
        } else {
            $partners = $user->partners()
                ->withOnly([])
                ->orderBy('name')
                ->get();
        }

        // Load goals for the selected fiscal year, keyed by partner_id
        $goals = PartnerGoal::with('updatedByUser')
            ->where('fiscal_year', $selectedFY)
            ->whereIn('partner_id', $partners->pluck('id'))
            ->get()
            ->keyBy('partner_id');

        // Build fiscal year options: current FY -1 through current FY +1
        $fyOptions = [];
        for ($fy = $currentFY - 1; $fy <= $currentFY + 1; $fy++) {
            $fyOptions[] = $fy;
        }

        return view('partner-goals.index', compact('partners', 'goals', 'selectedFY', 'currentFY', 'fyOptions'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        $fiscalYear = (int) $request->input('fiscal_year');
        $goalsData = $request->input('goals', []);

        // Determine which partner IDs this user can edit
        if ($user->role === 'admin') {
            $allowedPartnerIds = Partner::pluck('id')->toArray();
        } else {
            $allowedPartnerIds = $user->partners()->pluck('partners.id')->toArray();
        }

        foreach ($goalsData as $partnerId => $data) {
            $partnerId = (int) $partnerId;

            if (!in_array($partnerId, $allowedPartnerIds)) {
                continue;
            }

            $sourced = $data['sourced_nnacv_target'] ?? 0;
            $influenced = $data['influenced_nnacv_target'] ?? 0;
            $notes = $data['notes'] ?? null;

            // Only create/update if at least one value is non-zero or notes exist
            if ($sourced > 0 || $influenced > 0 || !empty($notes)) {
                PartnerGoal::updateOrCreate(
                    ['partner_id' => $partnerId, 'fiscal_year' => $fiscalYear],
                    [
                        'sourced_nnacv_target' => $sourced,
                        'influenced_nnacv_target' => $influenced,
                        'notes' => $notes,
                        'updated_by' => $user->id,
                    ]
                );
            } else {
                // If all values are cleared, remove the record
                PartnerGoal::where('partner_id', $partnerId)
                    ->where('fiscal_year', $fiscalYear)
                    ->delete();
            }
        }

        return redirect()
            ->route('partner-goals.index', ['fy' => $fiscalYear])
            ->with('success', 'Partner goals updated successfully.');
    }
}
