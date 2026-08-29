<?php

namespace App\Http\Controllers;

use App\Models\Workshop;
use App\Models\WorkshopSurveyTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WorkshopSurveyController extends Controller
{
    public function index(): View
    {
        $templates = WorkshopSurveyTemplate::with('workshop:id,name')
            ->withCount('responses')
            ->orderBy('workshop_id')
            ->get();

        $workshops = Workshop::orderBy('name')->get();

        return view('workshops.surveys.index', compact('templates', 'workshops'));
    }

    public function create(): View
    {
        $workshops = Workshop::orderBy('name')->get();

        return view('workshops.surveys.create', compact('workshops'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateTemplate($request);

        WorkshopSurveyTemplate::updateOrCreate(
            ['workshop_id' => $validated['workshop_id'], 'kind' => $validated['kind']],
            [
                'intro' => $validated['intro'] ?? null,
                'questions_json' => $validated['questions_json'],
                'passing_score' => $validated['passing_score'],
                'max_attempts' => $validated['max_attempts'] ?? null,
                'updated_by' => $request->user()->id,
            ],
        );

        return redirect()->route('workshops.surveys.index')->with('success', 'Survey template saved.');
    }

    public function edit(WorkshopSurveyTemplate $survey): View
    {
        return view('workshops.surveys.edit', ['template' => $survey]);
    }

    public function update(Request $request, WorkshopSurveyTemplate $survey): RedirectResponse
    {
        $validated = $this->validateTemplate($request, updating: true);

        $survey->update([
            'intro' => $validated['intro'] ?? null,
            'questions_json' => $validated['questions_json'],
            'passing_score' => $validated['passing_score'],
            'max_attempts' => $validated['max_attempts'] ?? null,
            'updated_by' => $request->user()->id,
        ]);

        return redirect()->route('workshops.surveys.index')->with('success', 'Survey template updated.');
    }

    public function results(WorkshopSurveyTemplate $survey): View
    {
        $survey->load(['responses.contact:id,first_name,last_name,partner_id', 'responses.contact.partner:id,name', 'responses.session:id,name']);

        $total = $survey->responses->count();
        $passed = $survey->responses->where('passed', true)->count();
        $averageScore = $total > 0 ? round($survey->responses->avg('score')) : 0;

        return view('workshops.surveys.results', [
            'template' => $survey,
            'total' => $total,
            'passed' => $passed,
            'averageScore' => $averageScore,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateTemplate(Request $request, bool $updating = false): array
    {
        $rules = [
            'intro' => ['nullable', 'string', 'max:2000'],
            'questions_json' => ['required', 'json'],
            'passing_score' => ['required', 'integer', 'min:0', 'max:100'],
            'max_attempts' => ['nullable', 'integer', 'min:1'],
        ];

        if (! $updating) {
            $rules['workshop_id'] = ['required', 'exists:workshops,id'];
            $rules['kind'] = ['required', Rule::in(array_keys(WorkshopSurveyTemplate::KINDS))];
        }

        $validated = $request->validate($rules);
        $validated['questions_json'] = json_decode($validated['questions_json'], true);

        return $validated;
    }
}
