<?php

namespace App\Http\Controllers;

use App\Models\WorkshopUpload;
use Illuminate\Support\Facades\Storage;

class WorkshopUploadController extends Controller
{
    public function download(WorkshopUpload $upload): mixed
    {
        return Storage::disk('local')->download($upload->stored_path, $upload->filename);
    }
}
