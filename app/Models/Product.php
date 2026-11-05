<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'salesforce_id',
        'name',
        'description',
        'slug',
        'product_line',
        'code',
        'sort_order',
        'status',
    ];

    /**
     * Salesforce Product Family values, which are loaded into product_line.
     */
    const PRODUCT_LINES = [
        'Finance - (non-FCR) Apps' => 'Finance - (non-FCR) Apps',
        'Consolidation (FCR) Apps' => 'Consolidation (FCR) Apps',
        'SCM Apps' => 'SCM Apps',
        'SPM Apps' => 'SPM Apps',
        'WFP Apps' => 'WFP Apps',
        'CIO Apps' => 'CIO Apps',
        'AXL' => 'AXL',
        'AI' => 'AI',
        'Other/Platform' => 'Other/Platform',
    ];

    public function opportunities(): HasMany
    {
        return $this->hasMany(Opportunity::class);
    }

    public function launchPrograms(): HasMany
    {
        return $this->hasMany(LaunchProgram::class);
    }

    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class, 'contact_product')
            ->withPivot('status', 'trained_at')
            ->withTimestamps();
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeByLine($query, string $line)
    {
        return $query->where('product_line', $line);
    }
}
