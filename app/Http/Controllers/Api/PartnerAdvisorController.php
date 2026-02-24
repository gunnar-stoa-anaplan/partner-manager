<?php

namespace App\Http\Controllers\Api;

use App\Ai\Agents\PartnerAdvisor;
use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\JsonResponse;

class PartnerAdvisorController extends Controller
{
    public function __invoke(Partner $partner): JsonResponse
    {
        $response = PartnerAdvisor::make(partner: $partner)
            ->prompt('Analyze this partner and provide your health assessment with recommendations.');

        return response()->json([
            'partner_id' => $partner->id,
            'partner_name' => $partner->name,
            'analysis' => [
                'health_score' => $response['health_score'],
                'risk_level' => $response['risk_level'],
                'summary' => $response['summary'],
                'recommendations' => [
                    $response['top_recommendation'],
                    $response['second_recommendation'],
                    $response['third_recommendation'],
                ],
                'key_strength' => $response['key_strength'],
                'key_risk' => $response['key_risk'],
            ],
        ]);
    }
}
