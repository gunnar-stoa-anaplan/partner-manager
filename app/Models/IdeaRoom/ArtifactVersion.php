<?php

namespace App\Models\IdeaRoom;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArtifactVersion extends Model
{
    use HasFactory;

    protected $table = 'idea_artifact_versions';

    public const UPDATED_AT = null;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'artifact_id',
        'version',
        'content',
        'file_path',
        'changed_by_type',
        'changed_by_id',
    ];

    public function artifact(): BelongsTo
    {
        return $this->belongsTo(Artifact::class);
    }
}
