<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\WorkshopSession;
use App\Models\WorkshopUpload;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class WorkshopUploadController extends Controller
{
    public function download(WorkshopSession $session, WorkshopUpload $upload): mixed
    {
        $contact = Auth::guard('portal')->user();

        abort_unless(
            $session->participants()->where('contact_id', $contact->id)->where('status', '!=', 'removed')->exists(),
            403,
        );

        $threadSessionId = $upload->thread?->workshop_session_id ?? $upload->reply?->thread?->workshop_session_id;
        abort_unless($threadSessionId === $session->id, 404);

        return Storage::disk('local')->download($upload->stored_path, $upload->filename);
    }
}
