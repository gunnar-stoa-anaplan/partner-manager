<?php

namespace App\Services;

use App\Contracts\SignalProcessor;
use App\Models\Partner;
use App\Models\Signal;
use App\Models\SignalEngineRun;
use App\Services\SignalProcessors\AlertBridgeProcessor;
use App\Services\SignalProcessors\BenchmarkProcessor;
use App\Services\SignalProcessors\CohortBenchmarkProcessor;
use App\Services\SignalProcessors\CrossDimensionCorrelationProcessor;
use App\Services\SignalProcessors\DeliveryCapabilityProcessor;
use App\Services\SignalProcessors\EnablementMomentumProcessor;
use App\Services\SignalProcessors\EngagementAnomalyProcessor;
use App\Services\SignalProcessors\HealthTrendProcessor;
use App\Services\SignalProcessors\PipelineVelocityProcessor;

class SignalEngineService
{
    /** @var array<string, SignalProcessor> */
    protected array $processors = [];

    public function __construct(
        protected AlertGenerationService $alertService,
        protected PartnerHealthService $healthService,
        protected DeliveryCapabilityRiskService $deliveryService,
    ) {
        $this->registerDefaultProcessors();
    }

    protected function registerDefaultProcessors(): void
    {
        $this->registerProcessor(new AlertBridgeProcessor($this->alertService));
        $this->registerProcessor(new HealthTrendProcessor($this->healthService));
        $this->registerProcessor(new DeliveryCapabilityProcessor($this->deliveryService));
        $this->registerProcessor(new PipelineVelocityProcessor);
        $this->registerProcessor(new EnablementMomentumProcessor);
        $this->registerProcessor(new EngagementAnomalyProcessor);
        $this->registerProcessor(new CrossDimensionCorrelationProcessor);
        $this->registerProcessor(new BenchmarkProcessor);
        $this->registerProcessor(new CohortBenchmarkProcessor);
    }

    public function registerProcessor(SignalProcessor $processor): void
    {
        $this->processors[$processor->name()] = $processor;
    }

    public function runAll(?string $cadence = null): SignalEngineRun
    {
        $startTime = microtime(true);
        $signalsCreated = 0;
        $partnersProcessed = 0;
        $processors = $this->getProcessorsForCadence($cadence);

        try {
            Partner::query()->chunk(50, function ($partners) use ($processors, &$signalsCreated, &$partnersProcessed) {
                foreach ($partners as $partner) {
                    $signalsCreated += $this->runForPartner($partner, null, $processors);
                    $partnersProcessed++;
                }
            });

            $signalsExpired = $this->expireStale();
            $signalsCorrelated = $this->runCorrelation();
            $durationMs = (int) ((microtime(true) - $startTime) * 1000);

            return SignalEngineRun::create([
                'processor' => null,
                'signals_created' => $signalsCreated,
                'signals_expired' => $signalsExpired,
                'signals_correlated' => $signalsCorrelated,
                'partners_processed' => $partnersProcessed,
                'duration_ms' => $durationMs,
                'status' => 'completed',
            ]);
        } catch (\Throwable $e) {
            $durationMs = (int) ((microtime(true) - $startTime) * 1000);

            return SignalEngineRun::create([
                'processor' => null,
                'signals_created' => $signalsCreated,
                'signals_expired' => 0,
                'signals_correlated' => 0,
                'partners_processed' => $partnersProcessed,
                'duration_ms' => $durationMs,
                'status' => 'failed',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function runForPartner(Partner $partner, ?string $cadence = null, ?array $processors = null): int
    {
        $processors = $processors ?? $this->getProcessorsForCadence($cadence);
        $signalsCreated = 0;

        foreach ($processors as $processor) {
            if ($processor instanceof CrossDimensionCorrelationProcessor) {
                continue;
            }

            $signalDataCollection = $processor->process($partner);

            foreach ($signalDataCollection as $signalData) {
                $signal = $this->createSignal($partner, $signalData);
                if ($signal) {
                    $signalsCreated++;
                }
            }
        }

        return $signalsCreated;
    }

    public function createSignal(Partner $partner, array $data): ?Signal
    {
        $exists = Signal::where('partner_id', $partner->id)
            ->where('type', $data['type'])
            ->where('source_processor', $data['source_processor'])
            ->where('status', 'active')
            ->where('created_at', '>=', now()->subDay())
            ->exists();

        if ($exists) {
            return null;
        }

        return Signal::create(array_merge($data, [
            'partner_id' => $partner->id,
            'status' => 'active',
        ]));
    }

    public function expireStale(): int
    {
        return Signal::where('status', 'active')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->update(['status' => 'expired']);
    }

    protected function runCorrelation(): int
    {
        $correlationProcessor = $this->processors['cross_dimension_correlation'] ?? null;

        if (! $correlationProcessor) {
            return 0;
        }

        $correlated = 0;

        $partnerIds = Signal::active()
            ->where('created_at', '>=', now()->subDay())
            ->distinct()
            ->pluck('partner_id');

        foreach ($partnerIds as $partnerId) {
            $partner = Partner::find($partnerId);

            if (! $partner) {
                continue;
            }

            $correlationSignals = $correlationProcessor->process($partner);

            foreach ($correlationSignals as $signalData) {
                $signal = $this->createSignal($partner, $signalData);
                if ($signal) {
                    $correlated++;
                }
            }
        }

        return $correlated;
    }

    /**
     * @return array<string, array{name: string, channel: string, cadence: string, description: string, data_sources: string[], signal_types: array, thresholds: array}>
     */
    public function getCatalog(): array
    {
        $catalog = [];

        foreach ($this->processors as $processor) {
            $meta = $processor->describe();
            $catalog[$processor->name()] = array_merge([
                'name' => $processor->name(),
                'channel' => $processor->channel(),
                'cadence' => $processor->cadence(),
            ], $meta);
        }

        return $catalog;
    }

    /** @return array<string, SignalProcessor> */
    protected function getProcessorsForCadence(?string $cadence): array
    {
        if (! $cadence) {
            return $this->processors;
        }

        return array_filter($this->processors, fn (SignalProcessor $p) => $p->cadence() === $cadence);
    }
}
