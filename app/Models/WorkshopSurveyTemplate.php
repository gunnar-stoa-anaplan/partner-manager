<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class WorkshopSurveyTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'workshop_id',
        'kind',
        'version',
        'parent_survey_template_id',
        'superseded_at',
        'published_at',
        'published_by',
        'rejected_at',
        'rejected_by',
        'rejection_reason',
        'intro',
        'questions_json',
        'passing_score',
        'max_attempts',
        'questions_per_attempt',
        'shuffle_options',
        'updated_by',
    ];

    protected $casts = [
        'questions_json' => 'array',
        'shuffle_options' => 'boolean',
        'superseded_at' => 'datetime',
        'published_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    const KINDS = [
        'pre' => 'Pre-Workshop',
        'post' => 'Post-Workshop',
        'knowledge' => 'Knowledge Check',
    ];

    /**
     * Fixed 5-point comfort scale for "likert" questions - value 5 is the
     * most comfortable/confident end, so a post-workshop average higher
     * than the pre-workshop one reads as a positive delta.
     */
    const LIKERT_SCALE = [
        5 => 'Very comfortable',
        4 => 'Somewhat comfortable',
        3 => 'Neither comfortable nor uncomfortable',
        2 => 'Somewhat uncomfortable',
        1 => 'Very uncomfortable',
    ];

    const QUESTION_TYPES = [
        'single_select' => 'Single-select',
        'multi_select' => 'Multi-select',
        'likert' => 'Likert (5-point)',
        'text' => 'Free text',
    ];

    public function workshop(): BelongsTo
    {
        return $this->belongsTo(Workshop::class);
    }

    public function updatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(WorkshopSurveyResponse::class, 'workshop_survey_template_id');
    }

    public function parentTemplate(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_survey_template_id');
    }

    public function childVersions(): HasMany
    {
        return $this->hasMany(self::class, 'parent_survey_template_id');
    }

    public function publishedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function rejectedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function reviewComments(): HasMany
    {
        return $this->hasMany(WorkshopContentReviewComment::class, 'workshop_survey_template_id');
    }

    /**
     * Whether this is the live version sessions attach to, as opposed to a
     * frozen historical version kept only so past responses still resolve
     * to the exact questions they were asked.
     */
    public function isCurrent(): bool
    {
        return $this->superseded_at === null;
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null;
    }

    /**
     * A draft is a proposed revision (or a brand-new template) that hasn't
     * been published live yet and hasn't been rejected either - it's still
     * "open," in PR terms.
     */
    public function isDraft(): bool
    {
        return $this->published_at === null && $this->rejected_at === null;
    }

    public function isRejected(): bool
    {
        return $this->rejected_at !== null;
    }

    /**
     * Whether this template has at least one question with a defined
     * "correct" answer - i.e. it's a scored (pass/fail) survey rather than
     * a plain feedback form. passing_score always has a DB default and
     * can't be used for this check on its own.
     */
    public function isScored(): bool
    {
        return collect($this->questions_json)->contains(fn (array $q) => ! empty($q['correct']));
    }

    /**
     * IDs of every 1-5 likert-type question on this template.
     */
    public function likertQuestionIds(): Collection
    {
        return collect($this->questions_json)->where('type', 'likert')->pluck('id');
    }

    /**
     * Overall comfort/confidence average across every likert question and
     * every response - a template can ask about several topics (e.g. one
     * likert per workshop module), so this isn't just one question's
     * average. Returns null when the template has no likert questions or
     * none of the responses answered any of them.
     *
     * @param  Collection<int, WorkshopSurveyResponse>  $responses
     */
    public function averageRating(Collection $responses): ?float
    {
        $questionIds = $this->likertQuestionIds();

        if ($questionIds->isEmpty()) {
            return null;
        }

        $values = $responses
            ->flatMap(fn (WorkshopSurveyResponse $response) => $questionIds->map(fn ($id) => $response->responses_json[$id] ?? null))
            ->filter(fn ($value) => is_numeric($value))
            ->map(fn ($value) => (float) $value);

        return $values->isNotEmpty() ? round($values->avg(), 2) : null;
    }

    /**
     * Select the questions to present for one attempt: a random subset when
     * questions_per_attempt is set (otherwise all questions), with option
     * order shuffled per question when shuffle_options is enabled.
     *
     * @return array<int, array<string, mixed>>
     */
    public function questionsForAttempt(): array
    {
        $questions = collect($this->questions_json);

        if ($this->questions_per_attempt && $this->questions_per_attempt < $questions->count()) {
            $questions = $questions->shuffle()->take($this->questions_per_attempt);
        }

        if ($this->shuffle_options) {
            $questions = $questions->map(function (array $question) {
                if (! empty($question['options'])) {
                    $question['options'] = collect($question['options'])->shuffle()->values()->all();
                }

                return $question;
            });
        }

        return $questions->values()->all();
    }

    /**
     * Score a set of submitted responses against this template's questions.
     * Only questions with a defined "correct" answer contribute to scoring -
     * open-text questions are recorded but not scored. Pass $presentedIds
     * (the question ids actually shown for this attempt, e.g. when
     * questions_per_attempt narrows the pool) so questions the participant
     * never saw aren't counted against them.
     *
     * @param  array<string, mixed>  $responses  Keyed by question id.
     * @param  array<int, string>|null  $presentedIds
     * @return array{score: int, passed: bool}
     */
    public function score(array $responses, ?array $presentedIds = null): array
    {
        $pool = $presentedIds
            ? collect($this->questions_json)->filter(fn (array $q) => in_array($q['id'], $presentedIds, true))
            : collect($this->questions_json);

        $scorable = $pool->filter(fn (array $q) => ! empty($q['correct']));

        if ($scorable->isEmpty()) {
            return ['score' => 0, 'passed' => false];
        }

        $correctCount = $scorable->filter(function (array $question) use ($responses) {
            $expected = collect($question['correct'])->sort()->values()->all();
            $given = collect((array) ($responses[$question['id']] ?? []))->sort()->values()->all();

            return $expected === $given;
        })->count();

        $score = (int) round(($correctCount / $scorable->count()) * 100);

        return ['score' => $score, 'passed' => $score >= $this->passing_score];
    }
}
