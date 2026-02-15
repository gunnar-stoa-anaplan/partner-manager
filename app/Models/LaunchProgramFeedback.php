<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaunchProgramFeedback extends Model
{
    use HasFactory;

    protected $table = 'launch_program_feedback';

    protected $fillable = [
        'launch_program_partner_id',
        'contact_id',
        'type',
        'rating',
        'body',
    ];

    const TYPES = [
        'product' => 'Product',
        'enablement' => 'Enablement',
        'general' => 'General',
    ];

    const TYPE_COLORS = [
        'product' => '#007bff',
        'enablement' => '#7B1FA2',
        'general' => '#868e96',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'rating' => 'integer',
        ];
    }

    public function programPartner(): BelongsTo
    {
        return $this->belongsTo(LaunchProgramPartner::class, 'launch_program_partner_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }
}
