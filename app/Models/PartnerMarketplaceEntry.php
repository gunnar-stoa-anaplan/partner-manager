<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerMarketplaceEntry extends Model
{
    /** @use HasFactory<\Database\Factories\PartnerMarketplaceEntryFactory> */
    use HasFactory;

    public const CATEGORIES = [
        'consumer_customer_stories' => 'Consumer Customer Stories',
        'consumer_solutions' => 'Consumer Solutions',
        'finance_customer_stories' => 'Finance Customer Stories',
        'finance_solutions' => 'Finance Solutions',
        'financial_business_services_customer_stories' => 'Financial & Business Services Customer Stories',
        'financial_business_services_solutions' => 'Financial & Business Services Solutions',
        'hr_workforce_customer_stories' => 'HR & Workforce Customer Stories',
        'hr_workforce_solutions' => 'HR & Workforce Solutions',
        'manufacturing_customer_stories' => 'Manufacturing Customer Stories',
        'manufacturing_solutions' => 'Manufacturing Solutions',
        'sales_marketing_customer_stories' => 'Sales & Marketing Customer Stories',
        'sales_marketing_solutions' => 'Sales & Marketing Solutions',
        'supply_chain_customer_stories' => 'Supply Chain Customer Stories',
        'supply_chain_solutions' => 'Supply Chain Solutions',
        'tech_media_telecom_customer_stories' => 'Tech, Media & Telecom Customer Stories',
        'tech_media_telecom_solutions' => 'Tech, Media & Telecom Solutions',
    ];

    protected $fillable = [
        'partner_id',
        'category',
        'title',
        'link',
        'description',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }
}
