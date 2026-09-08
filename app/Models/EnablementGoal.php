<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnablementGoal extends Model
{
    /** @use HasFactory<\Database\Factories\EnablementGoalFactory> */
    use HasFactory;

    protected $fillable = [
        'partner_id',
        'user_id',
        'category',
        'target',
        'baseline',
    ];

    protected function casts(): array
    {
        return [
            'target' => 'integer',
            'baseline' => 'integer',
        ];
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * All valid category keys (product line keys from config).
     *
     * @return array<string>
     */
    public static function categoryKeys(): array
    {
        $keys = [];
        foreach (config('enablement_goals.groups', []) as $groupKey => $group) {
            if (! empty($group['aggregate'])) {
                $keys[] = $groupKey;
            } else {
                foreach (array_keys($group['lines'] ?? []) as $lineKey) {
                    $keys[] = $lineKey;
                }
            }
        }

        return $keys;
    }

    /**
     * @return array<string, array{label: string, role: string, lines: array}>
     */
    public static function groups(): array
    {
        return config('enablement_goals.groups', []);
    }
}
