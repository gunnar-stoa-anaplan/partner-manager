<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\OpportunityResource;
use App\Http\Resources\V1\PartnerResource;
use App\Http\Resources\V1\ProjectResource;
use App\Models\Partner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Partner::withCount(['opportunities', 'projects', 'contacts', 'leads'])
            ->with('user');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('geography')) {
            $query->where('geography', $request->geography);
        }
        if ($request->filled('region')) {
            $query->where('region', $request->region);
        }
        if ($request->filled('psm')) {
            $query->whereHas('user', fn ($q) => $q->where('name', 'like', '%' . $request->psm . '%'));
        }

        $perPage = min((int) ($request->per_page ?? 25), 100);
        $partners = $query->orderBy('name')->paginate($perPage);

        return PartnerResource::collection($partners)->response();
    }

    public function show(int $id): JsonResponse
    {
        $partner = Partner::withCount(['opportunities', 'projects', 'contacts', 'leads'])
            ->with('user')
            ->findOrFail($id);

        return response()->json([
            'data' => new PartnerResource($partner),
        ]);
    }

    public function pipeline(Request $request, int $id): JsonResponse
    {
        $partner = Partner::findOrFail($id);
        $query = $partner->opportunities();

        if ($request->filled('stage')) {
            $query->where('stage', $request->stage);
        }
        if ($request->filled('fiscal_year')) {
            $query->where('fiscal_year', $request->fiscal_year);
        }
        if ($request->filled('revenue_type')) {
            $query->where('revenue_type', $request->revenue_type);
        }

        $perPage = min((int) ($request->per_page ?? 25), 100);
        $opportunities = $query->orderByDesc('amount')->paginate($perPage);

        return OpportunityResource::collection($opportunities)->response();
    }

    public function projects(Request $request, int $id): JsonResponse
    {
        $partner = Partner::findOrFail($id);
        $query = $partner->projects();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = min((int) ($request->per_page ?? 25), 100);
        $projects = $query->orderByDesc('created_at')->paginate($perPage);

        return ProjectResource::collection($projects)->response();
    }
}
