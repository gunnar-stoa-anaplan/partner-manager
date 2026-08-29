<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\User;
use App\Models\WorkshopSession;
use Illuminate\Support\Carbon;

class WorkshopInviteService
{
    /**
     * Split a free-text block of emails (one per line, or comma-separated) into
     * a de-duplicated list of lowercase email addresses.
     *
     * @return array<int, string>
     */
    public function parseEmailList(string $raw): array
    {
        $emails = preg_split('/[\s,;]+/', trim($raw)) ?: [];

        $emails = array_filter(array_map(
            fn (string $email) => strtolower(trim($email)),
            $emails,
        ));

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
     * @return array{invited: array<int, Contact>, already_enrolled: array<int, Contact>, unmatched: array<int, string>}
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
        ];
    }
}
