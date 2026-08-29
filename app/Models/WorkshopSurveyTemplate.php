<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkshopSurveyTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'workshop_id',
        'kind',
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
    ];

    const KINDS = [
        'pre' => 'Pre-Workshop',
        'post' => 'Post-Workshop',
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
