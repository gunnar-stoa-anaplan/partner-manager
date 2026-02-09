<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;

class FieldEnablementController extends Controller
{
    public function keyInfluencers()
    {
        $partnerId = session('selected_partner_id');
    
        if (!$partnerId) {
            return redirect()->route('partners.index')->with('error', 'Please select a partner first.');
        }
    
        $contacts = Contact::where('partner_id', $partnerId)->get();
        return view('field-enablement.key-influencers', compact('contacts'));
    }

    public function breadth()
    {
        $contacts = Contact::withCount('projects')->orderByDesc('projects_count')->get();
        return view('field-enablement.breadth', compact('contacts'));
    }

    public function depth()
    {
        $contacts = Contact::with('projects')->get()->filter(function ($contact) {
            return $contact->projects->count() >= 3; // Example: "depth" means involvement in ≥3 projects
        });

        return view('field-enablement.depth', ['contacts' => $contacts]);
    }
}