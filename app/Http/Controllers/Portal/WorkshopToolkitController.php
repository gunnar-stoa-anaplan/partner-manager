<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\WorkshopSession;
use App\Models\WorkshopToolkitAsset;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class WorkshopToolkitController extends Controller
{
    public function download(WorkshopSession $session, WorkshopToolkitAsset $asset): mixed
    {
        $contact = Auth::guard('portal')->user();

        abort_unless(
            $session->participants()->where('contact_id', $contact->id)->where('status', '!=', 'removed')->exists(),
            403,
        );
        abort_unless($asset->workshop_id === $session->workshop_id, 404);

        return Storage::disk('local')->download($asset->file_path, $asset->file_name);
    }
}
