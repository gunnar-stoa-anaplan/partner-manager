<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\ContactInteraction;
use App\Models\Partner;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $partnerIds = $user->isAdmin()
            ? Partner::pluck('id')
            : Partner::where('user_id', $user->id)->pluck('id');

        $influencers = Contact::where('contact_type', 'influencer')
            ->whereIn('partner_id', $partnerIds)
            ->with('partner')
            ->withCount('interactions')
            ->orderByDesc('interactions_count')
            ->get();

        $partners = Partner::whereIn('id', $partnerIds)->orderBy('name')->get();

        $stats = [
            'total' => $influencers->count(),
            'by_role' => $influencers->groupBy('influencer_role')->map->count(),
            'interactions_this_month' => ContactInteraction::whereIn('partner_id', $partnerIds)
                ->where('interaction_date', '>=', now()->startOfMonth())
                ->count(),
        ];

        return view('influencers.index', compact('influencers', 'partners', 'stats'));
    }

    public function directory()
    {
        $user = auth()->user();
        $partnerIds = $user->isAdmin()
            ? Partner::pluck('id')
            : Partner::where('user_id', $user->id)->pluck('id');

        $contacts = Contact::whereIn('partner_id', $partnerIds)
            ->with('partner')
            ->withCount(['certifications', 'projects', 'interactions'])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $partners = Partner::whereIn('id', $partnerIds)->orderBy('name')->get();

        $stats = [
            'total' => $contacts->count(),
            'general' => $contacts->where('contact_type', 'general')->count(),
            'influencer' => $contacts->where('contact_type', 'influencer')->count(),
            'resource' => $contacts->where('contact_type', 'resource')->count(),
        ];

        return view('contacts.index', compact('contacts', 'partners', 'stats'));
    }

    public function create()
    {
        $user = auth()->user();
        $partners = $user->isAdmin()
            ? Partner::orderBy('name')->get()
            : Partner::where('user_id', $user->id)->orderBy('name')->get();

        return view('influencers.create', compact('partners'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'partner_id' => 'required|exists:partners,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'title' => 'nullable|string|max:255',
            'influencer_role' => 'required|in:'.implode(',', array_keys(Contact::INFLUENCER_ROLES)),
            'organization' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $validated['contact_type'] = 'influencer';
        Contact::create($validated);

        return redirect()->route('influencers.index')
            ->with('success', 'Influencer added successfully.');
    }

    public function show(string $id)
    {
        $contact = Contact::with(['partner', 'events', 'projects'])
            ->withCount('interactions')
            ->findOrFail($id);

        $interactions = ContactInteraction::where('contact_id', $contact->id)
            ->with('loggedBy')
            ->orderByDesc('interaction_date')
            ->paginate(20);

        return view('influencers.show', compact('contact', 'interactions'));
    }

    public function edit(string $id)
    {
        $contact = Contact::findOrFail($id);
        $user = auth()->user();
        $partners = $user->isAdmin()
            ? Partner::orderBy('name')->get()
            : Partner::where('user_id', $user->id)->orderBy('name')->get();

        return view('influencers.edit', compact('contact', 'partners'));
    }

    public function update(Request $request, string $id)
    {
        $contact = Contact::findOrFail($id);

        $rules = [
            'partner_id' => 'required|exists:partners,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'title' => 'nullable|string|max:255',
            'organization' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'linkedin_url' => 'nullable|url|max:500',
            'linkedin_headline' => 'nullable|string|max:255',
            'linkedin_image_url' => 'nullable|url|max:500',
        ];

        if ($contact->contact_type === 'influencer') {
            $rules['influencer_role'] = 'required|in:'.implode(',', array_keys(Contact::INFLUENCER_ROLES));
        } elseif ($contact->contact_type === 'resource') {
            $rules['resource_role'] = 'required|in:'.implode(',', array_keys(Contact::RESOURCE_ROLES));
        }

        $validated = $request->validate($rules);
        $contact->update($validated);

        return redirect()->route('influencers.show', $contact)
            ->with('success', 'Contact updated successfully.');
    }

    public function destroy(string $id)
    {
        $contact = Contact::findOrFail($id);
        $contact->interactions()->delete();
        $contact->delete();

        return redirect()->route('influencers.index')
            ->with('success', 'Influencer removed.');
    }

    public function storeInteraction(Request $request, Contact $influencer)
    {
        $validated = $request->validate([
            'interaction_type' => 'required|in:'.implode(',', array_keys(ContactInteraction::TYPES)),
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'interaction_date' => 'required|date',
            'location' => 'nullable|string|max:255',
            'outcome' => 'nullable|string',
            'next_steps' => 'nullable|string',
        ]);

        $validated['contact_id'] = $influencer->id;
        $validated['partner_id'] = $influencer->partner_id;
        $validated['logged_by'] = auth()->id();

        ContactInteraction::create($validated);

        return redirect()->back()->with('success', 'Interaction logged successfully.');
    }

    public function destroyInteraction(ContactInteraction $interaction)
    {
        $interaction->delete();

        return redirect()->back()->with('success', 'Interaction removed.');
    }

    /**
     * Store LinkedIn profile URL for an influencer.
     */
    public function storeLinkedIn(Request $request, Contact $influencer)
    {
        $validated = $request->validate([
            'linkedin_url' => 'required|url|max:500',
        ]);

        // Extract profile ID from URL (e.g., linkedin.com/in/john-doe -> john-doe)
        $profileId = null;
        if (preg_match('#linkedin\.com/in/([^/?]+)#i', $validated['linkedin_url'], $matches)) {
            $profileId = $matches[1];
        }

        $influencer->update([
            'linkedin_url' => $validated['linkedin_url'],
            'linkedin_profile_id' => $profileId,
            'linkedin_synced_at' => now(),
        ]);

        return redirect()->route('influencers.show', $influencer)
            ->with('success', 'LinkedIn profile linked successfully.');
    }

    /**
     * Remove LinkedIn profile from an influencer.
     */
    public function removeLinkedIn(Contact $influencer)
    {
        $influencer->update([
            'linkedin_url' => null,
            'linkedin_profile_id' => null,
            'linkedin_headline' => null,
            'linkedin_image_url' => null,
            'linkedin_synced_at' => null,
        ]);

        return redirect()->route('influencers.show', $influencer)
            ->with('success', 'LinkedIn profile unlinked.');
    }
}
