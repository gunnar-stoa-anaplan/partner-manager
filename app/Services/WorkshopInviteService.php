<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\User;
use App\Models\WorkshopSession;
use Illuminate\Support\Carbon;

class WorkshopInviteService
{
    /**
     * Pull email addresses out of a free-text block, one per line (or
     * comma/semicolon-separated) - accepts a plain address, "Name
     * <user@domain>", or '"Name" <user@domain>". Only the address itself is
     * extracted, so surrounding names/quotes/brackets are simply ignored.
     *
     * @return array<int, string>
     */
    public function parseEmailList(string $raw): array
    {
        preg_match_all('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $raw, $matches);

        $emails = array_map('strtolower', $matches[0]);

        return array_values(array_unique($emails));
    }

    /**
     * Invite a list of emails to a workshop session. Only matches existing
     * Contacts by email - unlike the prototype this ports from, this app has
     * real Partner/Contact records, so an unmatched email is reported back
     * rather than guessing a partner from the email domain and fabricating
     * a new Contact.
     *
     * When $restrictToPartnerId is set (portal self-invite), only Contacts
     * belonging to that partner can be matched - a practice_lead must not be
     * able to enroll a contact from a different partner by guessing their email.
     *
     * @param  array<int, string>  $emails
     * @return array{invited: array<int, Contact>, already_enrolled: array<int, Contact>, unmatched: array<int, string>, capacity_warning: ?string}
     */
    public function inviteToSession(
        WorkshopSession $session,
        array $emails,
        ?User $invitedByUser = null,
        ?Contact $invitedByContact = null,
        ?int $restrictToPartnerId = null,
    ): array {
        $contacts = Contact::query()
            ->whereNotNull('email')
            ->whereIn('email', $emails)
            ->when($restrictToPartnerId, fn ($query) => $query->where('partner_id', $restrictToPartnerId))
            ->get()
            ->unique(fn (Contact $contact) => strtolower($contact->email));

        $matchedByEmail = $contacts->keyBy(fn (Contact $contact) => strtolower($contact->email));

        $invited = [];
        $alreadyEnrolled = [];
        $unmatched = [];

        foreach ($emails as $email) {
            $contact = $matchedByEmail->get($email);

            if (! $contact) {
                $unmatched[] = $email;

                continue;
            }

            $existing = $session->participants()->where('contact_id', $contact->id)->first();

            if ($existing) {
                $alreadyEnrolled[] = $contact;

                continue;
            }

            $session->participants()->create([
                'contact_id' => $contact->id,
                'status' => 'invited',
                'enrolled_at' => Carbon::now(),
                'enrolled_by_user_id' => $invitedByUser?->id,
                'enrolled_by_contact_id' => $invitedByContact?->id,
            ]);

            $invited[] = $contact;
        }

        return [
            'invited' => $invited,
            'already_enrolled' => $alreadyEnrolled,
            'unmatched' => $unmatched,
            'capacity_warning' => $this->capacityWarning($session, count($invited)),
        ];
    }

    /**
     * Tenant assignment is a separate manual step from invitation (see
     * WorkshopCredentialController::assign), so this doesn't block the
     * invite outright - it just flags when a bulk invite would enroll more
     * people than the workshop's tenant pool can currently seat, so the
     * admin isn't surprised later when some participants have no sandbox
     * to work in.
     */
    private function capacityWarning(WorkshopSession $session, int $newlyInvited): ?string
    {
        if ($newlyInvited === 0) {
            return null;
        }

        $available = $session->workshop->credentials->filter(fn ($credential) => $credential->isAvailable())->count();

        if ($newlyInvited > $available) {
            return "Only {$available} tenant(s) available in the pool for {$newlyInvited} newly invited participant(s) - add more credentials before the session starts.";
        }

        return null;
    }
}
