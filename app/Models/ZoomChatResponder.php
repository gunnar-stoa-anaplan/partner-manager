<?php

namespace App\Models;

use App\Ai\Agents\ProductLine\AdoExpert;
use App\Ai\Agents\ProductLine\FinanceExpert;
use App\Ai\Agents\ProductLine\PolarisExpert;
use App\Ai\Agents\ProductLine\SalesExpert;
use App\Ai\Agents\ProductLine\SupplyChainExpert;
use App\Ai\Agents\ProductLine\WorkforceExpert;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Ai\Contracts\Agent;

class ZoomChatResponder extends Model
{
    use HasFactory;

    protected $fillable = [
        'enablement_session_id',
        'zoom_meeting_id',
        'agent_class',
        'display_name',
        'trigger_prefix',
        'is_active',
        'max_response_tokens',
        'rate_limit_per_minute',
        'config',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'max_response_tokens' => 'integer',
            'rate_limit_per_minute' => 'integer',
            'config' => 'array',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function allowedAgents(): array
    {
        return [
            SupplyChainExpert::class => 'Supply Chain Expert (SCM)',
            FinanceExpert::class => 'Finance Expert (IFP)',
            WorkforceExpert::class => 'Workforce Expert (OWP)',
            SalesExpert::class => 'Sales Expert (SPM)',
            PolarisExpert::class => 'Polaris Expert',
            AdoExpert::class => 'ADO Expert',
        ];
    }

    /**
     * Instantiate the configured AI agent.
     */
    public function resolveAgent(): Agent
    {
        $class = $this->agent_class;

        return $class::make();
    }

    /**
     * Check if the given message text matches the trigger prefix.
     */
    public function matchesTrigger(string $messageText): bool
    {
        return str_starts_with(
            mb_strtolower(trim($messageText)),
            mb_strtolower($this->trigger_prefix)
        );
    }

    /**
     * Strip the trigger prefix from the message text.
     */
    public function stripTrigger(string $messageText): string
    {
        $text = trim($messageText);
        $prefix = $this->trigger_prefix;

        if (str_starts_with(mb_strtolower($text), mb_strtolower($prefix))) {
            $text = trim(mb_substr($text, mb_strlen($prefix)));
        }

        return $text;
    }

    public function enablementSession(): BelongsTo
    {
        return $this->belongsTo(EnablementSession::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ZoomChatMessage::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
