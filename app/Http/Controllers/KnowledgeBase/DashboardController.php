<?php

namespace App\Http\Controllers\KnowledgeBase;

use App\Http\Controllers\Controller;
use App\Models\KnowledgeChunk;
use App\Models\KnowledgeDocument;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $stats = [
            'total_documents' => KnowledgeDocument::count(),
            'completed_documents' => KnowledgeDocument::completed()->count(),
            'pending_documents' => KnowledgeDocument::pending()->count(),
            'failed_documents' => KnowledgeDocument::failed()->count(),
            'total_chunks' => KnowledgeChunk::count(),
        ];

        $recentDocuments = KnowledgeDocument::query()
            ->latest()
            ->limit(10)
            ->get();

        return view('knowledge-base.dashboard', compact('stats', 'recentDocuments'));
    }
}
