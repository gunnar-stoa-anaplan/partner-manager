<?php
namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\Certification;
use App\Models\CertificationRequirement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompetencyMatrixController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $partnersQuery = $user->role === 'admin'
            ? Partner::query()
            : Partner::where('user_id', $user->id);

        $partners = $partnersQuery->select('id', 'name')->orderBy('name')->get();
        $partnerIds = $partners->pluck('id');

        // Product areas with certs
        $productAreas = Certification::whereIn('partner_id', $partnerIds)
            ->where('status', 'active')
            ->whereNotNull('product_area')
            ->distinct()
            ->pluck('product_area')
            ->sort()
            ->values();

        // Matrix: partner x product_area => cert count
        $matrix = Certification::whereIn('partner_id', $partnerIds)
            ->where('status', 'active')
            ->whereNotNull('product_area')
            ->selectRaw('partner_id, product_area, COUNT(*) as cert_count')
            ->groupBy('partner_id', 'product_area')
            ->get()
            ->groupBy('partner_id');

        // Requirements per current stage
        $requirements = CertificationRequirement::all();

        // Expiring soon
        $expiring = Certification::whereIn('partner_id', $partnerIds)
            ->where('status', 'active')
            ->whereBetween('expiry_date', [now(), now()->addDays(90)])
            ->with('contact', 'partner')
            ->orderBy('expiry_date')
            ->get();

        // Summary stats
        $stats = [
            'total_active' => Certification::whereIn('partner_id', $partnerIds)->where('status', 'active')->count(),
            'expiring_30' => Certification::whereIn('partner_id', $partnerIds)->where('status', 'active')
                ->whereBetween('expiry_date', [now(), now()->addDays(30)])->count(),
            'expiring_90' => $expiring->count(),
            'in_progress' => Certification::whereIn('partner_id', $partnerIds)->where('status', 'in_progress')->count(),
            'product_areas' => $productAreas->count(),
        ];

        return view('plan.competency-matrix.index', compact('partners', 'productAreas', 'matrix', 'requirements', 'expiring', 'stats'));
    }
}
