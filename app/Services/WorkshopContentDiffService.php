<?php

namespace App\Services;

class WorkshopContentDiffService
{
    /**
     * Word/tag-level diff between two HTML strings, using a classic LCS
     * (longest common subsequence) alignment. Tokenizes on whitespace and
     * HTML tags so a tag never gets split mid-token.
     *
     * @return array<int, array{type: 'equal'|'insert'|'delete', text: string}>
     */
    public function diffText(?string $old, ?string $new): array
    {
        $oldTokens = $this->tokenize($old ?? '');
        $newTokens = $this->tokenize($new ?? '');

        $oldCount = count($oldTokens);
        $newCount = count($newTokens);

        // Standard LCS length table.
        $lengths = array_fill(0, $oldCount + 1, array_fill(0, $newCount + 1, 0));

        for ($i = $oldCount - 1; $i >= 0; $i--) {
            for ($j = $newCount - 1; $j >= 0; $j--) {
                $lengths[$i][$j] = $oldTokens[$i] === $newTokens[$j]
                    ? $lengths[$i + 1][$j + 1] + 1
                    : max($lengths[$i + 1][$j], $lengths[$i][$j + 1]);
            }
        }

        // Walk the table to build the ordered diff.
        $result = [];
        $i = 0;
        $j = 0;

        while ($i < $oldCount && $j < $newCount) {
            if ($oldTokens[$i] === $newTokens[$j]) {
                $result[] = ['type' => 'equal', 'text' => $oldTokens[$i]];
                $i++;
                $j++;
            } elseif ($lengths[$i + 1][$j] >= $lengths[$i][$j + 1]) {
                $result[] = ['type' => 'delete', 'text' => $oldTokens[$i]];
                $i++;
            } else {
                $result[] = ['type' => 'insert', 'text' => $newTokens[$j]];
                $j++;
            }
        }

        while ($i < $oldCount) {
            $result[] = ['type' => 'delete', 'text' => $oldTokens[$i]];
            $i++;
        }

        while ($j < $newCount) {
            $result[] = ['type' => 'insert', 'text' => $newTokens[$j]];
            $j++;
        }

        return $this->mergeAdjacent($result);
    }

    /**
     * Semantic diff between two arrays of question/check objects keyed by
     * "id" - far more useful here than a raw JSON text diff, since it shows
     * exactly which question/check was added, removed, or changed and what
     * changed about it.
     *
     * @param  array<int, array<string, mixed>>  $old
     * @param  array<int, array<string, mixed>>  $new
     * @return array{added: array<int, array<string, mixed>>, removed: array<int, array<string, mixed>>, changed: array<int, array{id: mixed, before: array<string, mixed>, after: array<string, mixed>, fields: array<int, string>}>}
     */
    public function diffKeyedArray(array $old, array $new): array
    {
        $oldById = collect($old)->keyBy('id');
        $newById = collect($new)->keyBy('id');

        $added = $newById->reject(fn ($item, $id) => $oldById->has($id))->values()->all();
        $removed = $oldById->reject(fn ($item, $id) => $newById->has($id))->values()->all();

        $changed = $oldById->intersectByKeys($newById->all())
            ->map(function ($before, $id) use ($newById) {
                $after = $newById->get($id);
                $fields = collect(array_unique(array_merge(array_keys($before), array_keys($after))))
                    ->filter(fn ($field) => ($before[$field] ?? null) !== ($after[$field] ?? null))
                    ->values()
                    ->all();

                return empty($fields) ? null : ['id' => $id, 'before' => $before, 'after' => $after, 'fields' => $fields];
            })
            ->filter()
            ->values()
            ->all();

        return ['added' => $added, 'removed' => $removed, 'changed' => $changed];
    }

    /**
     * @return array<int, string>
     */
    private function tokenize(string $text): array
    {
        $tokens = preg_split('/(\s+|<[^>]+>)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        return $tokens ?: [];
    }

    /**
     * Collapse consecutive tokens of the same diff type into one entry, so
     * the rendered view doesn't wrap every single word in its own span.
     *
     * @param  array<int, array{type: string, text: string}>  $tokens
     * @return array<int, array{type: string, text: string}>
     */
    private function mergeAdjacent(array $tokens): array
    {
        $merged = [];

        foreach ($tokens as $token) {
            $last = count($merged) - 1;

            if ($last >= 0 && $merged[$last]['type'] === $token['type']) {
                $merged[$last]['text'] .= $token['text'];
            } else {
                $merged[] = $token;
            }
        }

        return $merged;
    }
}
