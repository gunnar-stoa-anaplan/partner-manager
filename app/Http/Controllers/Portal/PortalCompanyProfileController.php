<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\PartnerMarketplaceEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PortalCompanyProfileController extends Controller
{
    public function edit(): View
    {
        $contact = Auth::guard('portal')->user();
        $partner = $contact->partner;

        $entries = $partner->marketplaceEntries()
            ->orderBy('category')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('category');

        $categories = PartnerMarketplaceEntry::CATEGORIES;

        return view('portal.company-profile.edit', compact('contact', 'partner', 'entries', 'categories'));
    }

    public function update(Request $request): RedirectResponse
    {
        $contact = Auth::guard('portal')->user();
        $partner = $contact->partner;

        $validated = $request->validate([
            'logo' => 'nullable|image|mimes:jpeg,jpg,png,gif,svg|max:2048',
            'remove_logo' => 'sometimes|boolean',
            'phone' => 'nullable|string|max:255',
            'sales_territory' => 'nullable|string|max:255',
            'mailing_country' => 'nullable|string|max:255',
            'mailing_state' => 'nullable|string|max:255',
            'mailing_street' => 'nullable|string|max:255',
            'mailing_suite' => 'nullable|string|max:255',
            'mailing_city' => 'nullable|string|max:255',
            'mailing_postal_code' => 'nullable|string|max:255',
            'partner_locator_emails' => 'nullable|string|max:5000',
            'headline' => 'nullable|string|max:255',
            'company_description' => 'nullable|string|max:10000',
        ]);

        // Handle logo upload / removal
        if ($request->hasFile('logo')) {
            $this->deleteOldLogo($partner->logo_path);
            $validated['logo_path'] = $request->file('logo')->store('partner-logos', 'public');
        } elseif ($request->boolean('remove_logo')) {
            $this->deleteOldLogo($partner->logo_path);
            $validated['logo_path'] = null;
        }

        unset($validated['logo'], $validated['remove_logo']);

        $partner->update($validated);

        // Auto-submit for review when meaningful profile data is present
        $partner->refresh();
        if (($partner->headline || $partner->company_description) && in_array($partner->marketplace_status, ['draft', 'rejected'])) {
            $partner->update([
                'marketplace_status' => 'submitted',
                'marketplace_rejection_reason' => null,
            ]);
        }

        return back()->with('success', 'Company profile updated successfully.');
    }

    public function storeEntry(Request $request): RedirectResponse
    {
        $contact = Auth::guard('portal')->user();

        $validated = $request->validate([
            'category' => ['required', 'string', Rule::in(array_keys(PartnerMarketplaceEntry::CATEGORIES))],
            'title' => 'required|string|max:255',
            'link' => 'required|url|max:512',
            'description' => 'required|string|max:5000',
            'sort_order' => 'nullable|integer|min:0|max:999',
        ]);

        $validated['partner_id'] = $contact->partner_id;
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        PartnerMarketplaceEntry::create($validated);

        return back()->with('success', 'Marketplace entry added successfully.');
    }

    public function updateEntry(Request $request, PartnerMarketplaceEntry $entry): RedirectResponse
    {
        $contact = Auth::guard('portal')->user();
        $this->ensureOwnsEntry($contact, $entry);

        $validated = $request->validate([
            'category' => ['required', 'string', Rule::in(array_keys(PartnerMarketplaceEntry::CATEGORIES))],
            'title' => 'required|string|max:255',
            'link' => 'required|url|max:512',
            'description' => 'required|string|max:5000',
            'sort_order' => 'nullable|integer|min:0|max:999',
        ]);

        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $entry->update($validated);

        return back()->with('success', 'Marketplace entry updated successfully.');
    }

    public function destroyEntry(PartnerMarketplaceEntry $entry): RedirectResponse
    {
        $contact = Auth::guard('portal')->user();
        $this->ensureOwnsEntry($contact, $entry);

        $entry->delete();

        return back()->with('success', 'Marketplace entry removed.');
    }

    private function ensureOwnsEntry($contact, PartnerMarketplaceEntry $entry): void
    {
        if ($entry->partner_id !== $contact->partner_id) {
            abort(403);
        }
    }

    private function deleteOldLogo(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
