<?php

namespace App\Http\Controllers;

use App\Models\ContentAsset;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssetCatalogController extends Controller
{
    public function index(Request $request): View
    {
        $query = ContentAsset::query();

        if ($request->filled('lob')) {
            $query->forLob((string) $request->string('lob'));
        }
        if ($request->filled('persona')) {
            $query->forPersona((string) $request->string('persona'));
        }
        if ($request->filled('status')) {
            $query->where('status', (string) $request->string('status'));
        }
        if ($request->filled('type')) {
            $query->where('type', (string) $request->string('type'));
        }
        if ($request->filled('application')) {
            $query->where('application', 'like', '%'.$request->string('application').'%');
        }

        $assets = $query
            ->orderBy('lob')
            ->orderBy('application')
            ->orderBy('persona')
            ->orderBy('type')
            ->paginate(50)
            ->withQueryString();

        return view('assets.index', [
            'assets' => $assets,
            'filters' => $request->only(['lob', 'persona', 'status', 'type', 'application']),
            'lobs' => ContentAsset::LOBS,
            'personas' => ContentAsset::PERSONAS,
            'statuses' => ContentAsset::STATUSES,
            'types' => ContentAsset::TYPES,
        ]);
    }
}
