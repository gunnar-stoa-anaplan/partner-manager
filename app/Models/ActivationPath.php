<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ActivationPath extends Model
{
    /** @use HasFactory<\Database\Factories\ActivationPathFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'feature_slug',
        'name',
        'description',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function steps(): HasMany
    {
        return $this->hasMany(ActivationStep::class)->orderBy('order');
    }
}
