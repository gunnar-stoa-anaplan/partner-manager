<?php

namespace App\Http\Controllers\KnowledgeBase;

use App\Http\Controllers\Controller;
use App\Jobs\IngestDocumentJob;
use App\Models\KnowledgeDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocumentController extends Controller
{
    public function index(Request $request): View
    {
        $documents = KnowledgeDocument::query()
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate(25);

        return view('knowledge-base.documents.index', compact('documents'));
    }

    public function show(KnowledgeDocument $document): View
    {
        $document->loadCount('chunks');

        $chunks = $document->chunks()
            ->orderBy('chunk_index')
            ->paginate(20);

        return view('knowledge-base.documents.show', compact('document', 'chunks'));
    }

    public function destroy(KnowledgeDocument $document): RedirectResponse
    {
        $document->chunks()->delete();
        $document->delete();

        return redirect()
            ->route('knowledge_base.documents.index')
            ->with('success', "Document \"{$document->title}\" deleted.");
    }

    public function reingest(KnowledgeDocument $document): RedirectResponse
    {
        IngestDocumentJob::dispatch($document);

        return redirect()
            ->route('knowledge_base.documents.show', $document)
            ->with('success', 'Re-ingestion queued.');
    }
}
