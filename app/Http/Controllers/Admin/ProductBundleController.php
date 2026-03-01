<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductBundleController extends Controller
{
    public function index(): View
    {
        $products = Module::query()
            ->orderBy('sort_order')
            ->get()
            ->map(function (Module $module) {
                $sections = $module->definition['sections'] ?? [];
                $itemCount = collect($sections)->sum(fn (array $s) => count($s['items'] ?? []));

                return [
                    'module' => $module,
                    'section_count' => count($sections),
                    'item_count' => $itemCount,
                    'extends_count' => count($module->definition['extends'] ?? []),
                ];
            });

        $groups = $products->groupBy(fn (array $p) => $p['module']->group);

        return view('admin.product-bundles.index', compact('products', 'groups'));
    }

    public function show(Module $module): View
    {
        $sections = $module->definition['sections'] ?? [];
        $extends = $module->definition['extends'] ?? [];

        return view('admin.product-bundles.show', compact('module', 'sections', 'extends'));
    }

    public function updateSection(Request $request, Module $module, string $sectionKey): RedirectResponse
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'icon' => 'required|string|max:50',
        ]);

        $definition = $module->definition;

        if (! isset($definition['sections'][$sectionKey])) {
            abort(404, 'Section not found.');
        }

        $definition['sections'][$sectionKey]['label'] = $validated['label'];
        $definition['sections'][$sectionKey]['icon'] = $validated['icon'];

        $module->update(['definition' => $definition]);

        return back()->with('success', "Section \"{$validated['label']}\" updated.");
    }

    public function addItem(Request $request, Module $module, string $sectionKey): RedirectResponse
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'route' => 'required|string|max:255',
            'icon' => 'required|string|max:50',
        ]);

        $definition = $module->definition;

        if (! isset($definition['sections'][$sectionKey])) {
            abort(404, 'Section not found.');
        }

        $definition['sections'][$sectionKey]['items'][] = [
            'label' => $validated['label'],
            'route' => $validated['route'],
            'icon' => $validated['icon'],
        ];

        $module->update(['definition' => $definition]);

        return back()->with('success', "Item \"{$validated['label']}\" added.");
    }

    public function updateItem(Request $request, Module $module, string $sectionKey, int $itemIndex): RedirectResponse
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'route' => 'required|string|max:255',
            'icon' => 'required|string|max:50',
        ]);

        $definition = $module->definition;

        if (! isset($definition['sections'][$sectionKey]['items'][$itemIndex])) {
            abort(404, 'Item not found.');
        }

        $definition['sections'][$sectionKey]['items'][$itemIndex] = array_merge(
            $definition['sections'][$sectionKey]['items'][$itemIndex],
            $validated,
        );

        $module->update(['definition' => $definition]);

        return back()->with('success', "Item \"{$validated['label']}\" updated.");
    }

    public function removeItem(Module $module, string $sectionKey, int $itemIndex): RedirectResponse
    {
        $definition = $module->definition;

        if (! isset($definition['sections'][$sectionKey]['items'][$itemIndex])) {
            abort(404, 'Item not found.');
        }

        $label = $definition['sections'][$sectionKey]['items'][$itemIndex]['label'];

        array_splice($definition['sections'][$sectionKey]['items'], $itemIndex, 1);

        $module->update(['definition' => $definition]);

        return back()->with('success', "Item \"{$label}\" removed.");
    }

    public function addSection(Request $request, Module $module): RedirectResponse
    {
        $validated = $request->validate([
            'key' => ['required', 'string', 'max:50', 'regex:/^[a-z_]+$/'],
            'label' => 'required|string|max:255',
            'icon' => 'required|string|max:50',
        ]);

        $definition = $module->definition;

        if (isset($definition['sections'][$validated['key']])) {
            return back()->withErrors(['key' => 'A section with this key already exists.']);
        }

        $definition['sections'][$validated['key']] = [
            'label' => $validated['label'],
            'icon' => $validated['icon'],
            'items' => [],
        ];

        $module->update(['definition' => $definition]);

        return back()->with('success', "Section \"{$validated['label']}\" added.");
    }

    public function removeSection(Module $module, string $sectionKey): RedirectResponse
    {
        $definition = $module->definition;

        if (! isset($definition['sections'][$sectionKey])) {
            abort(404, 'Section not found.');
        }

        $label = $definition['sections'][$sectionKey]['label'];

        unset($definition['sections'][$sectionKey]);

        $module->update(['definition' => $definition]);

        return back()->with('success', "Section \"{$label}\" removed.");
    }
}
