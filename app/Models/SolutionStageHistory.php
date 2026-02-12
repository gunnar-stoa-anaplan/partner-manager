<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolutionStageHistory extends Model
{
    protected $table = 'solution_stage_history';

    protected $fillable = [
        'partner_solution_id', 'from_stage', 'to_stage',
        'transitioned_at', 'transitioned_by', 'notes',
    ];

    protected $casts = [
        'transitioned_at' => 'datetime',
    ];

    public function solution()
    {
        return $this->belongsTo(PartnerSolution::class, 'partner_solution_id');
    }

    public function transitionedByUser()
    {
        return $this->belongsTo(User::class, 'transitioned_by');
    }
}
