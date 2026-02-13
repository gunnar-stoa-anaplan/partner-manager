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
        'name',
        'slug',
        'product_line',
        'code',
        'sort_order',
        'status',
    ];

    const PRODUCT_LINES = [
        'Finance' => 'Finance',
        'Supply Chain' => 'Supply Chain',
        'Sales' => 'Sales',
        'Workforce' => 'Workforce',
        'Platform' => 'Platform',
    ];

    public function opportunities(): HasMany
    {
        return $this->hasMany(Opportunity::class);
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
