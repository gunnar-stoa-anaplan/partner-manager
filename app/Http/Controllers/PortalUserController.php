<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Partner;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PortalUserController extends Controller
{
    public function toggleAccess(Partner $partner, Contact $contact): \Illuminate\Http\RedirectResponse
    {
        $this->authorizePartnerAccess($partner);
        $this->ensureContactBelongsToPartner($partner, $contact);

        $contact->update(['portal_access' => ! $contact->portal_access]);
        $status = $contact->portal_access ? 'enabled' : 'disabled';

        return redirect(route('partners.show', $partner).'#portal-users')
            ->with('success', "Portal access {$status} for {$contact->full_name}.");
    }

    public function updateRole(Request $request, Partner $partner, Contact $contact): \Illuminate\Http\RedirectResponse
    {
        $this->authorizePartnerAccess($partner);
        $this->ensureContactBelongsToPartner($partner, $contact);

        $validated = $request->validate([
            'portal_role' => ['nullable', 'string', 'in:'.implode(',', array_keys(Contact::PORTAL_ROLES))],
        ]);

        $contact->update(['portal_role' => $validated['portal_role']]);

        return redirect(route('partners.show', $partner).'#portal-users')
            ->with('success', "Portal role updated for {$contact->full_name}.");
    }

    public function setPassword(Request $request, Partner $partner, Contact $contact): \Illuminate\Http\RedirectResponse
    {
        $this->authorizePartnerAccess($partner);
        $this->ensureContactBelongsToPartner($partner, $contact);

        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $contact->update(['password' => $request->password]);

        return redirect(route('partners.show', $partner).'#portal-users')
            ->with('success', "Password set for {$contact->full_name}.");
    }

    public function import(Request $request, Partner $partner): \Illuminate\Http\RedirectResponse
    {
        $this->authorizePartnerAccess($partner);

        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        $readerType = match ($extension) {
            'csv' => 'Csv',
            'xls' => 'Xls',
            default => 'Xlsx',
        };
        $reader = IOFactory::createReader($readerType);
        $spreadsheet = $reader->load($file->getPathname());
        $rows = $spreadsheet->getActiveSheet()->toArray();

        if (count($rows) < 2) {
            return redirect(route('partners.show', $partner).'#portal-users')
                ->with('error', 'The file is empty or contains only headers.');
        }

        $headers = array_map(fn ($h) => strtolower(trim($h ?? '')), array_shift($rows));
        $colMap = array_flip($headers);

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $validRoles = array_keys(Contact::PORTAL_ROLES);

        foreach ($rows as $row) {
            $email = trim($row[$colMap['email'] ?? -1] ?? '');
            if (empty($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $skipped++;

                continue;
            }

            $firstName = trim($row[$colMap['first name'] ?? $colMap['first_name'] ?? -1] ?? '');
            $lastName = trim($row[$colMap['last name'] ?? $colMap['last_name'] ?? -1] ?? '');
            $phone = trim($row[$colMap['phone'] ?? -1] ?? '');
            $title = trim($row[$colMap['title'] ?? -1] ?? '');
            $portalRole = strtolower(trim($row[$colMap['portal role'] ?? $colMap['portal_role'] ?? -1] ?? ''));
            $portalRole = str_replace([' ', '-'], '_', $portalRole);

            $data = array_filter([
                'first_name' => $firstName ?: null,
                'last_name' => $lastName ?: null,
                'phone' => $phone ?: null,
                'title' => $title ?: null,
                'portal_role' => in_array($portalRole, $validRoles, true) ? $portalRole : null,
            ]);

            $contact = Contact::where('email', $email)
                ->where('partner_id', $partner->id)
                ->first();

            if ($contact) {
                $contact->update($data);
                $updated++;
            } else {
                Contact::create(array_merge($data, [
                    'partner_id' => $partner->id,
                    'email' => $email,
                    'contact_type' => 'general',
                ]));
                $created++;
            }
        }

        return redirect(route('partners.show', $partner).'#portal-users')
            ->with('success', "Import complete: {$created} created, {$updated} updated, {$skipped} skipped.");
    }

    public function downloadTemplate(): StreamedResponse
    {
        $headers = ['First Name', 'Last Name', 'Email', 'Phone', 'Title', 'Portal Role'];
        $roleKeys = implode(' / ', array_keys(Contact::PORTAL_ROLES));

        return response()->streamDownload(function () use ($headers, $roleKeys) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);
            fputcsv($out, ['Jane', 'Doe', 'jane.doe@partner.com', '+1-555-0100', 'Delivery Manager', 'delivery_lead']);
            fputcsv($out, ['', '', '', '', '', "Valid roles: {$roleKeys}"]);
            fclose($out);
        }, 'contact_import_template.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function authorizePartnerAccess(Partner $partner): void
    {
        if ($partner->user_id !== auth()->id() && auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access to this partner.');
        }
    }

    private function ensureContactBelongsToPartner(Partner $partner, Contact $contact): void
    {
        if ($contact->partner_id !== $partner->id) {
            abort(404);
        }
    }
}
