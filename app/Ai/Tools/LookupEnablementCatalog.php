<?php

namespace App\Ai\Tools;

use App\Models\EnablementEvent;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class LookupEnablementCatalog implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Search the existing enablement event catalog by product area or type. '
            .'Use this to discover what workshops, academies, and training content already '
            .'exist before designing new ones. Returns titles, types, categories, durations, '
            .'target audiences, and session counts.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $query = EnablementEvent::query()
            ->published()
            ->withCount('sessions');

        if ($productArea = $request['product_area'] ?? null) {
            $query->where('product_area', $productArea);
        }

        if ($type = $request['type'] ?? null) {
            $query->where('type', $type);
        }

        $events = $query->orderBy('product_area')->orderBy('title')->get();

        if ($events->isEmpty()) {
            return 'No published enablement events found matching the criteria.';
        }

        $lines = ["Found {$events->count()} enablement events:"];

        foreach ($events as $event) {
            $lines[] = '';
            $lines[] = "- {$event->title}";
            $lines[] = "  Type: {$event->type} | Category: {$event->category} | Product: {$event->product_area}";
            $lines[] = "  Duration: {$event->duration_hours}h | Max participants: ".($event->max_participants ?? 'unlimited');
            $lines[] = "  Target audience: {$event->target_audience}";
            $lines[] = "  Scheduled sessions: {$event->sessions_count}";

            if ($event->description) {
                $lines[] = "  Description: {$event->description}";
            }

            if ($event->objectives) {
                $lines[] = "  Objectives: {$event->objectives}";
            }
        }

        $lines[] = '';
        $lines[] = 'Available product areas: '.collect(EnablementEvent::PRODUCT_AREAS)->flatten()->keys()->implode(', ');
        $lines[] = 'Available types: '.collect(EnablementEvent::TYPES)->keys()->implode(', ');

        return implode("\n", $lines);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'product_area' => $schema->string()
                ->description('Filter by Anaplan product area: IFP, SCM, SPM, OWP, Polaris, ADO, AI, Forecaster, or Other'),
            'type' => $schema->string()
                ->description('Filter by event type: workshop, academy, certification_exam, webinar, bootcamp, onboarding, hackathon, connect, other'),
        ];
    }
}
