<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActionPlanItem extends Model
{
    protected $fillable = [
        'action_plan_id', 'description', 'status', 'due_date',
        'assigned_to', 'completed_at', 'notes', 'sort_order',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function actionPlan()
    {
        return $this->belongsTo(ActionPlan::class);
    }
}
