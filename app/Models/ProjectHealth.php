<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectHealth extends Model
{
    protected $table = 'project_health';

    protected $fillable = ['project_key', 'phase', 'scope', 'timeline', 'budget', 'reason'];
}
