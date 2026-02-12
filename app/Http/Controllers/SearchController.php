<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Partner;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $q = trim($request->input('q', ''));

        if (strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $user = auth()->user();
        $partnerIds = $user->role === 'admin'
            ? null
            : Partner::where('user_id', $user->id)->pluck('id')->toArray();

        $results = [];

        // Search Partners
        $partnerQuery = Partner::where('name', 'like', "%{$q}%");
        if ($partnerIds !== null) {
            $partnerQuery->whereIn('id', $partnerIds);
        }
        $partners = $partnerQuery->limit(5)->get();
        foreach ($partners as $p) {
            $results[] = [
                'type' => 'partner',
                'icon' => 'fa-building',
                'color' => 'primary',
                'label' => $p->name,
                'sub' => $p->geography ? "{$p->geography} / {$p->region}" : 'Partner',
                'url' => route('partners.show', $p->id),
            ];
        }

        // Search Contacts (influencers, resources, general)
        $contactQuery = Contact::where(function ($query) use ($q) {
            $query->where('first_name', 'like', "%{$q}%")
                  ->orWhere('last_name', 'like', "%{$q}%")
                  ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$q}%"])
                  ->orWhere('email', 'like', "%{$q}%")
                  ->orWhere('organization', 'like', "%{$q}%");
        })->with('partner:id,name');

        if ($partnerIds !== null) {
            $contactQuery->whereIn('partner_id', $partnerIds);
        }

        $contacts = $contactQuery->limit(8)->get();
        foreach ($contacts as $c) {
            $typeLabel = match ($c->contact_type) {
                'influencer' => 'Influencer',
                'resource'   => 'Resource',
                default      => 'Contact',
            };
            $icon = match ($c->contact_type) {
                'influencer' => 'fa-user-tie',
                'resource'   => 'fa-user-hard-hat',
                default      => 'fa-user',
            };
            $color = match ($c->contact_type) {
                'influencer' => 'info',
                'resource'   => 'success',
                default      => 'secondary',
            };

            $results[] = [
                'type' => 'contact',
                'icon' => $icon,
                'color' => $color,
                'label' => "{$c->first_name} {$c->last_name}",
                'sub' => "{$typeLabel} at " . ($c->partner->name ?? 'Unknown'),
                'url' => $c->contact_type === 'influencer'
                    ? route('influencers.show', $c->id)
                    : '#',
            ];
        }

        return response()->json(['results' => $results]);
    }
}
