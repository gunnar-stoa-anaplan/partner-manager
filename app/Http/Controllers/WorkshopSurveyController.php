<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Workshop;
use App\Models\WorkshopSession;
use App\Models\WorkshopSurveyResponse;
use App\Models\WorkshopSurveyTemplate;
use App\Services\WorkshopContentDiffService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\IOFactory;

class WorkshopSurveyController extends Controller
{
    public function __construct(private WorkshopContentDiffService $diffService) {}

    /**
     * Every workshop gets exactly one template per kind, so all of its
     * sessions are measured the same way - auto-provision any kind that
     * doesn't exist yet rather than requiring a separate "create" step.
     */
    public function index(Workshop $workshop): View
    {
        foreach (array_keys(WorkshopSurveyTemplate::KINDS) as $kind) {
            $workshop->surveyTemplates()->firstOrCreate(
                ['kind' => $kind],
                ['questions_json' => [], 'passing_score' => 70, 'published_at' => now(), 'published_by' => auth()->id()],
            );
        }

        $templates = $workshop->surveyTemplates()
            ->withCount('responses')
            ->get()
            ->sortBy(fn (WorkshopSurveyTemplate $t) => array_search($t->kind, array_keys(WorkshopSurveyTemplate::KINDS)));

        $pendingCount = $workshop->pendingSurveyTemplates()->count();

        return view('workshops.surveys.index', compact('workshop', 'templates', 'pendingCount'));
    }

    public function edit(WorkshopSurveyTemplate $survey): View
    {
        return view('workshops.surveys.edit', ['template' => $survey]);
    }

    public function update(Request $request, WorkshopSurveyTemplate $survey): RedirectResponse
    {
        $validated = $this->validateTemplate($request);

        $attributes = [
            'intro' => $validated['intro'] ?? null,
            'questions_json' => $validated['questions_json'],
            'passing_score' => $validated['passing_score'],
            'max_attempts' => $validated['max_attempts'] ?? null,
            'questions_per_attempt' => $validated['questions_per_attempt'] ?? null,
            'shuffle_options' => $request->boolean('shuffle_options'),
            'updated_by' => $request->user()->id,
        ];

        $current = $this->applyContentEdit($survey, $attributes);

        if ($current->is($survey)) {
            return redirect()->route('workshops.surveys.edit', $current)->with('success', 'Survey template saved.');
        }

        return redirect()->route('workshops.surveys.diff', $current)
            ->with('success', "Saved as a draft (version {$current->version}) - review the diff and publish it to make it live. The current live version is untouched until then.");
    }

    /**
     * Editing content that's already live and published creates a new
     * DRAFT version instead of mutating the live one in place, so
     * historical scoring/comparisons never retroactively change underneath
     * completed sessions and the change goes through review before anyone
     * but staff sees it. A template that's never been published yet (still
     * being authored, or an earlier draft that was itself never published)
     * can be edited freely in place - there's nothing live to protect.
     *
     * @param  array<string, mixed>  $attributes
     */
    private function applyContentEdit(WorkshopSurveyTemplate $survey, array $attributes): WorkshopSurveyTemplate
    {
        if (! $survey->isPublished()) {
            $survey->update($attributes);

            return $survey;
        }

        $newVersion = $survey->replicate(['created_at', 'updated_at']);
        $newVersion->fill($attributes);
        $newVersion->version = $survey->version + 1;
        $newVersion->parent_survey_template_id = $survey->id;
        $newVersion->superseded_at = null;
        $newVersion->published_at = null;
        $newVersion->published_by = null;
        $newVersion->rejected_at = null;
        $newVersion->rejected_by = null;
        $newVersion->rejection_reason = null;
        $newVersion->save();

        return $newVersion;
    }

    /**
     * Makes a draft the live version: supersedes whatever was previously
     * live for the same kind (if anything), and marks this row published.
     */
    public function publish(WorkshopSurveyTemplate $survey): RedirectResponse
    {
        abort_if($survey->isPublished(), 422, 'This template is already live.');
        abort_if($survey->isRejected(), 422, 'A rejected draft cannot be published - create a new edit instead.');

        $liveSibling = $survey->workshop->allSurveyTemplateVersions()
            ->where('kind', $survey->kind)
            ->whereNotNull('published_at')
            ->whereNull('superseded_at')
            ->where('id', '!=', $survey->id)
            ->first();

        $liveSibling?->update(['superseded_at' => now()]);

        $survey->update(['published_at' => now(), 'published_by' => auth()->id()]);

        return redirect()->route('workshops.surveys.index', $survey->workshop)->with('success', 'Survey template is now live.');
    }

    public function reject(Request $request, WorkshopSurveyTemplate $survey): RedirectResponse
    {
        abort_if($survey->isPublished(), 422, 'This template is already live and cannot be rejected.');

        $validated = $request->validate(['rejection_reason' => ['nullable', 'string', 'max:2000']]);

        $survey->update([
            'rejected_at' => now(),
            'rejected_by' => auth()->id(),
            'rejection_reason' => $validated['rejection_reason'] ?? null,
        ]);

        return redirect()->route('workshops.review-queue', $survey->workshop)->with('success', 'Draft template rejected.');
    }

    /**
     * Side-by-side review of a draft against the template it would
     * replace - a text diff of the intro and a semantic diff of the
     * questions.
     */
    public function diff(WorkshopSurveyTemplate $survey): View
    {
        $workshop = $survey->workshop;
        $before = $survey->parentTemplate;

        $introDiff = $this->diffService->diffText($before?->intro, $survey->intro);
        $questionsDiff = $this->diffService->diffKeyedArray($before?->questions_json ?? [], $survey->questions_json ?? []);

        $comments = $survey->reviewComments()->with('author:id,name')->latest()->get();

        return view('workshops.surveys.diff', compact('workshop', 'survey', 'before', 'introDiff', 'questionsDiff', 'comments'));
    }

    public function comment(Request $request, WorkshopSurveyTemplate $survey): RedirectResponse
    {
        $validated = $request->validate(['body' => ['required', 'string', 'max:2000']]);

        $survey->reviewComments()->create([
            'body' => $validated['body'],
            'author_user_id' => $request->user()->id,
        ]);

        return redirect()->route('workshops.surveys.diff', $survey)->with('success', 'Comment added.');
    }

    /**
     * Every version of this template's kind, newest first, for an admin
     * reviewing what changed and when.
     */
    public function history(WorkshopSurveyTemplate $survey): View
    {
        $versions = $survey->workshop->allSurveyTemplateVersions()
            ->where('kind', $survey->kind)
            ->withCount('responses')
            ->with('updatedByUser:id,name')
            ->orderByDesc('version')
            ->get();

        return view('workshops.surveys.history', ['template' => $survey, 'versions' => $versions]);
    }

    /**
     * Bulk-append questions from a pasted JSON array to the existing pool -
     * a separate small form from the main builder, so it never risks
     * clobbering in-progress edits to the questions above it.
     */
    public function importQuestions(Request $request, WorkshopSurveyTemplate $survey): RedirectResponse
    {
        $validated = $request->validate(['questions_json' => ['required', 'json']]);
        $incoming = json_decode($validated['questions_json'], true);

        if (! is_array($incoming) || empty($incoming)) {
            return back()->with('error', 'Paste a non-empty JSON array of question objects.');
        }

        $existing = $survey->questions_json ?? [];
        $usedIds = collect($existing)->pluck('id')->filter()->all();

        foreach ($incoming as $question) {
            if (empty($question['id'])) {
                $question['id'] = 'q'.(count($usedIds) + 1).'-'.Str::random(4);
            }
            $usedIds[] = $question['id'];
            $existing[] = $question;
        }

        $current = $this->applyContentEdit($survey, ['questions_json' => $existing]);

        if ($current->is($survey)) {
            return redirect()->route('workshops.surveys.edit', $current)
                ->with('success', count($incoming).' question(s) appended - '.count($existing).' total in the pool.');
        }

        return redirect()->route('workshops.surveys.diff', $current)
            ->with('success', count($incoming).' question(s) appended as a draft (version '.$current->version.') - review and publish to go live.');
    }

    public function preview(WorkshopSurveyTemplate $survey): View
    {
        return view('workshops.surveys.preview', ['template' => $survey, 'questions' => $survey->questionsForAttempt()]);
    }

    public function destroy(WorkshopSurveyTemplate $survey): RedirectResponse
    {
        $workshop = $survey->workshop;
        $survey->delete();

        return redirect()->route('workshops.surveys.index', $workshop)->with('success', 'Survey template deleted.');
    }

    public function results(WorkshopSurveyTemplate $survey): View
    {
        $survey->load(['responses.contact:id,first_name,last_name,partner_id', 'responses.contact.partner:id,name', 'responses.session:id,name']);

        $total = $survey->responses->count();
        $passed = $survey->responses->where('passed', true)->count();
        $averageScore = $total > 0 ? round($survey->responses->avg('score')) : 0;

        $importBatches = $survey->responses->where('source', 'import')->pluck('import_batch')->unique()->filter()->values();

        $comparison = $this->prePostComparison($survey);

        return view('workshops.surveys.results', [
            'template' => $survey,
            'total' => $total,
            'passed' => $passed,
            'averageScore' => $averageScore,
            'importBatches' => $importBatches,
            'comparison' => $comparison,
        ]);
    }

    public function upload(Request $request, WorkshopSurveyTemplate $survey): RedirectResponse
    {
        $validated = $request->validate([
            'workshop_session_id' => ['required', 'exists:workshop_sessions,id'],
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:10240'],
            'is_anonymous' => ['nullable', 'boolean'],
        ]);

        $session = WorkshopSession::findOrFail($validated['workshop_session_id']);
        abort_unless($session->workshop_id === $survey->workshop_id, 422, 'That session does not belong to this workshop.');

        $spreadsheet = IOFactory::load($request->file('file')->getRealPath());
        $rows = $spreadsheet->getActiveSheet()->toArray();

        if (count($rows) < 2) {
            return back()->with('error', 'The file has no data rows.');
        }

        $headers = array_map(fn ($h) => trim((string) $h), array_shift($rows));
        $questionIds = collect($survey->questions_json)->pluck('id')->all();
        $isAnonymous = $request->boolean('is_anonymous');
        $batchId = 'import-'.now()->format('Ymd-His').'-'.Str::random(6);

        $imported = 0;
        $unmatched = 0;

        foreach ($rows as $row) {
            $rowData = array_combine($headers, array_pad($row, count($headers), null));
            $email = trim((string) ($rowData['email'] ?? ''));

            $responses = [];
            foreach ($questionIds as $questionId) {
                if (array_key_exists($questionId, $rowData) && $rowData[$questionId] !== null && $rowData[$questionId] !== '') {
                    $responses[$questionId] = $rowData[$questionId];
                }
            }

            if (empty($responses)) {
                continue;
            }

            $contact = $isAnonymous ? null : Contact::whereRaw('LOWER(email) = ?', [strtolower($email)])->first();

            if (! $isAnonymous && ! $contact) {
                $unmatched++;
            }

            $result = $survey->score($responses);

            WorkshopSurveyResponse::create([
                'workshop_survey_template_id' => $survey->id,
                'workshop_session_id' => $session->id,
                'contact_id' => $contact?->id,
                'is_anonymous' => $isAnonymous || ! $contact,
                'raw_email' => $email ?: null,
                'source' => 'import',
                'import_batch' => $batchId,
                'responses_json' => $responses,
                'score' => $result['score'],
                'passed' => $result['passed'],
                'attempt_number' => 1,
                'submitted_at' => Carbon::now(),
            ]);
            $imported++;
        }

        return redirect()->route('workshops.surveys.results', $survey)
            ->with('success', "Imported {$imported} response(s)".($unmatched > 0 ? " ({$unmatched} without a matching contact)." : '.'));
    }

    public function destroyBatch(Request $request, WorkshopSurveyTemplate $survey): RedirectResponse
    {
        $validated = $request->validate(['import_batch' => ['required', 'string']]);

        $deleted = $survey->responses()->where('import_batch', $validated['import_batch'])->delete();

        return back()->with('success', "Removed {$deleted} imported response(s).");
    }

    /**
     * Compare average scores question-by-question between this workshop's
     * pre and post survey templates, matched by question id, for questions
     * present in both.
     *
     * @return array<int, array{text: string, pre_avg: float, post_avg: float}>
     */
    private function prePostComparison(WorkshopSurveyTemplate $survey): array
    {
        if (! in_array($survey->kind, ['pre', 'post'], true)) {
            return [];
        }

        $other = WorkshopSurveyTemplate::where('workshop_id', $survey->workshop_id)
            ->where('kind', $survey->kind === 'pre' ? 'post' : 'pre')
            ->whereNull('superseded_at')
            ->whereNotNull('published_at')
            ->first();

        if (! $other) {
            return [];
        }

        [$pre, $post] = $survey->kind === 'pre' ? [$survey, $other] : [$other, $survey];

        $preQuestions = collect($pre->questions_json)->keyBy('id');
        $postQuestions = collect($post->questions_json)->keyBy('id');
        $sharedIds = $preQuestions->keys()->intersect($postQuestions->keys());

        if ($sharedIds->isEmpty()) {
            return [];
        }

        $preResponses = $pre->responses()->get();
        $postResponses = $post->responses()->get();

        $correctRate = function ($responses, array $question) {
            if (empty($question['correct'])) {
                return null;
            }

            $expected = collect($question['correct'])->sort()->values()->all();

            $answered = $responses->filter(fn (WorkshopSurveyResponse $r) => array_key_exists($question['id'], $r->responses_json));

            if ($answered->isEmpty()) {
                return null;
            }

            $correct = $answered->filter(function (WorkshopSurveyResponse $r) use ($question, $expected) {
                $given = collect((array) $r->responses_json[$question['id']])->sort()->values()->all();

                return $given === $expected;
            });

            return round(($correct->count() / $answered->count()) * 100);
        };

        return $sharedIds->map(function (string $questionId) use ($preQuestions, $postQuestions, $preResponses, $postResponses, $correctRate) {
            return [
                'text' => $preQuestions[$questionId]['text'],
                'pre_avg' => $correctRate($preResponses, $preQuestions[$questionId]),
                'post_avg' => $correctRate($postResponses, $postQuestions[$questionId]),
            ];
        })->filter(fn (array $row) => $row['pre_avg'] !== null || $row['post_avg'] !== null)->values()->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function validateTemplate(Request $request): array
    {
        $validated = $request->validate([
            'intro' => ['nullable', 'string', 'max:2000'],
            'questions_json' => ['required', 'json'],
            'passing_score' => ['required', 'integer', 'min:0', 'max:100'],
            'max_attempts' => ['nullable', 'integer', 'min:1'],
            'questions_per_attempt' => ['nullable', 'integer', 'min:1'],
        ]);

        $validated['questions_json'] = json_decode($validated['questions_json'], true);

        return $validated;
    }
}
