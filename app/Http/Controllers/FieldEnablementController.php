<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Partner;

class FieldEnablementController extends Controller
{
    /**
     * Key Influencers: contacts for the currently selected partner.
     */
    public function keyInfluencers()
    {
        $partnerId = session('selected_partner_id');

        if (! $partnerId) {
            return redirect()->route('partners.my')->with('error', 'Please select a partner first.');
        }

        $partner = Partner::findOrFail($partnerId);

        // Authorization
        $user = auth()->user();
        if (! $user->isAdmin() && $partner->user_id !== $user->id) {
            abort(403, 'Unauthorized access to this partner.');
        }

        $contacts = Contact::where('partner_id', $partnerId)
            ->withCount('projects')
            ->orderByDesc('projects_count')
            ->get();

        // Metrics
        $totalContacts = $contacts->count();
        $avgProjects = $totalContacts > 0 ? round($contacts->avg('projects_count'), 1) : 0;
        $withProjects = $contacts->where('projects_count', '>', 0)->count();
        $topInfluencers = $contacts->where('projects_count', '>=', 3)->count();

        // Chart: project involvement distribution
        $involvementBuckets = [
            '0' => $contacts->where('projects_count', 0)->count(),
            '1' => $contacts->where('projects_count', 1)->count(),
            '2' => $contacts->where('projects_count', 2)->count(),
            '3+' => $contacts->where('projects_count', '>=', 3)->count(),
        ];

        // Chart: contacts by title (top roles)
        $byTitle = $contacts->groupBy(fn ($c) => $c->title ?: 'Unknown')
            ->map->count()
            ->sortDesc()
            ->take(8);

        return view('field-enablement.key-influencers', compact(
            'contacts', 'partner', 'totalContacts', 'avgProjects',
            'withProjects', 'topInfluencers', 'involvementBuckets', 'byTitle'
        ));
    }

    /**
     * Breadth: contacts ordered by project count across the user's portfolio.
     */
    public function breadth()
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            $partnerIds = Partner::pluck('id');
        } else {
            $partnerIds = Partner::where('user_id', $user->id)->pluck('id');
        }

        $contacts = Contact::with('partner')
            ->withCount('projects')
            ->whereIn('partner_id', $partnerIds)
            ->orderByDesc('projects_count')
            ->get();

        // Metrics
        $totalContacts = $contacts->count();
        $avgProjects = $totalContacts > 0 ? round($contacts->avg('projects_count'), 1) : 0;
        $withMultiple = $contacts->where('projects_count', '>=', 2)->count();
        $partnerCount = $contacts->pluck('partner_id')->unique()->count();

        // Chart: contacts per partner (top 10)
        $contactsByPartner = $contacts->groupBy(fn ($c) => $c->partner->name ?? 'Unknown')
            ->map->count()
            ->sortDesc()
            ->take(10);

        // Chart: project count distribution
        $projectDistribution = [
            '0 projects' => $contacts->where('projects_count', 0)->count(),
            '1 project' => $contacts->where('projects_count', 1)->count(),
            '2 projects' => $contacts->where('projects_count', 2)->count(),
            '3 projects' => $contacts->where('projects_count', 3)->count(),
            '4+ projects' => $contacts->where('projects_count', '>=', 4)->count(),
        ];

        return view('field-enablement.breadth', compact(
            'contacts', 'totalContacts', 'avgProjects', 'withMultiple',
            'partnerCount', 'contactsByPartner', 'projectDistribution'
        ));
    }

    /**
     * Depth: contacts with involvement in 3+ projects.
     */
    public function depth()
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            $partnerIds = Partner::pluck('id');
        } else {
            $partnerIds = Partner::where('user_id', $user->id)->pluck('id');
        }

        $contacts = Contact::with(['partner', 'projects'])
            ->withCount('projects')
            ->whereIn('partner_id', $partnerIds)
            ->whereHas('projects', null, '>=', 3)
            ->orderByDesc('projects_count')
            ->get();

        // All contacts for ratio calculation
        $totalPortfolioContacts = Contact::whereIn('partner_id', $partnerIds)->count();

        // Metrics
        $deepCount = $contacts->count();
        $deepRatio = $totalPortfolioContacts > 0 ? round(($deepCount / $totalPortfolioContacts) * 100) : 0;
        $avgProjectCount = $deepCount > 0 ? round($contacts->avg('projects_count'), 1) : 0;
        $maxProjectCount = $deepCount > 0 ? $contacts->max('projects_count') : 0;

        // Chart: deep contacts by partner
        $deepByPartner = $contacts->groupBy(fn ($c) => $c->partner->name ?? 'Unknown')
            ->map->count()
            ->sortDesc()
            ->take(10);

        // Chart: project count spread for deep contacts
        $depthSpread = $contacts->groupBy('projects_count')
            ->map->count()
            ->sortKeys();

        return view('field-enablement.depth', compact(
            'contacts', 'deepCount', 'deepRatio', 'avgProjectCount',
            'maxProjectCount', 'deepByPartner', 'depthSpread', 'totalPortfolioContacts'
        ));
    }

    /**
     * Influence Map: portfolio-wide influence dashboard.
     */
    public function influenceMap()
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            $partnerIds = Partner::pluck('id');
        } else {
            $partnerIds = Partner::where('user_id', $user->id)->pluck('id');
        }

        $contacts = Contact::with('partner')
            ->withCount('projects')
            ->whereIn('partner_id', $partnerIds)
            ->get();

        $partners = Partner::with(['contacts', 'projects', 'leads'])
            ->whereIn('id', $partnerIds)
            ->get();

        // Top-level metrics
        $totalInfluencers = $contacts->count();
        $activeInfluencers = $contacts->where('projects_count', '>', 0)->count();
        $totalProjects = $partners->sum(fn ($p) => $p->projects->count());
        $totalLeads = $partners->sum(fn ($p) => $p->leads->count());

        // Contacts per partner (radar-style comparison)
        $contactsByPartner = $partners->mapWithKeys(fn ($p) => [$p->name => $p->contacts->count()])
            ->sortDesc()
            ->take(8);

        // Projects per partner (bar)
        $projectsByPartner = $partners->mapWithKeys(fn ($p) => [$p->name => $p->projects->count()])
            ->sortDesc()
            ->take(10);

        // Engagement rate: % of contacts with at least 1 project
        $engagementRate = $totalInfluencers > 0 ? round(($activeInfluencers / $totalInfluencers) * 100) : 0;
        $deepEngagement = $totalInfluencers > 0 ? round(($contacts->where('projects_count', '>=', 3)->count() / $totalInfluencers) * 100) : 0;

        // Industry distribution (from partners)
        $industryDistribution = $partners->groupBy(fn ($p) => $p->industry ?: 'Unknown')
            ->map(fn ($group) => $group->sum(fn ($p) => $p->contacts->count()))
            ->sortDesc()
            ->take(8);

        // Geographic distribution
        $geoDistribution = $partners->groupBy(fn ($p) => $p->geography ?: 'Unknown')
            ->map(fn ($group) => $group->sum(fn ($p) => $p->contacts->count()))
            ->sortDesc();

        // Contact title distribution
        $titleDistribution = $contacts->groupBy(fn ($c) => $c->title ?: 'Unknown')
            ->map->count()
            ->sortDesc()
            ->take(8);

        return view('field-enablement.influence-map', compact(
            'totalInfluencers', 'activeInfluencers', 'totalProjects', 'totalLeads',
            'contactsByPartner', 'projectsByPartner', 'engagementRate', 'deepEngagement',
            'industryDistribution', 'geoDistribution', 'titleDistribution'
        ));
    }
}
