<?php

namespace App\Http\Controllers\IntegrationHub;

use App\Http\Controllers\Controller;
use App\Http\Requests\IntegrationHub\StoreFieldMappingRequest;
use App\Http\Requests\IntegrationHub\UpdateFieldMappingRequest;
use App\Models\FieldMapping;
use App\Models\IntegrationConnection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FieldMappingController extends Controller
{
    public function index(): View
    {
        $mappings = FieldMapping::query()
            ->with('connection')
            ->latest()
            ->get()
            ->groupBy('connection_id');

        $connections = IntegrationConnection::all();

        return view('integration-hub.mappings.index', compact('mappings', 'connections'));
    }

    public function create(): View
    {
        $connections = IntegrationConnection::where('status', 'active')->get();
        $targetModels = FieldMapping::availableTargetModels();

        return view('integration-hub.mappings.create', compact('connections', 'targetModels'));
    }

    public function store(StoreFieldMappingRequest $request): RedirectResponse
    {
        FieldMapping::create($request->validated());

        return redirect()
            ->route('integration_hub.mappings.index')
            ->with('success', 'Field mapping created.');
    }

    public function show(FieldMapping $mapping): View
    {
        $mapping->load('connection');

        return view('integration-hub.mappings.show', compact('mapping'));
    }

    public function edit(FieldMapping $mapping): View
    {
        $connections = IntegrationConnection::where('status', 'active')->get();
        $targetModels = FieldMapping::availableTargetModels();

        return view('integration-hub.mappings.edit', compact('mapping', 'connections', 'targetModels'));
    }

    public function update(UpdateFieldMappingRequest $request, FieldMapping $mapping): RedirectResponse
    {
        $mapping->update($request->validated());

        return redirect()
            ->route('integration_hub.mappings.index')
            ->with('success', 'Field mapping updated.');
    }

    public function destroy(FieldMapping $mapping): RedirectResponse
    {
        $mapping->delete();

        return redirect()
            ->route('integration_hub.mappings.index')
            ->with('success', 'Field mapping deleted.');
    }

    public function preview(FieldMapping $mapping): JsonResponse
    {
        return response()->json([
            'source_field' => $mapping->source_field,
            'target' => $mapping->target_model.'.'.$mapping->target_field,
            'transform' => $mapping->transform,
            'sample' => $mapping->previewTransform(),
        ]);
    }
}
