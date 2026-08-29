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
        'updated_by',
    ];

    protected $casts = [
        'questions_json' => 'array',
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
     * Score a set of submitted responses against this template's questions.
     * Only questions with a defined "correct" answer contribute to scoring -
     * open-text questions are recorded but not scored.
     *
     * @param  array<string, mixed>  $responses  Keyed by question id.
     * @return array{score: int, passed: bool}
     */
    public function score(array $responses): array
    {
        $scorable = collect($this->questions_json)->filter(fn (array $q) => ! empty($q['correct']));

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
