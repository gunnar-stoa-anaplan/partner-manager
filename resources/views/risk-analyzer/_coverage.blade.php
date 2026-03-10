{{-- Partner Resource Coverage Grid --}}
<div class="card shadow-0 mb-g">
    <div class="card-header">
        <div class="card-title">Partner Resource Coverage: 7-Product Certification Grid</div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead>
                    <tr>
                        <th class="sticky-col bg-white" style="min-width:200px">Partner</th>
                        @foreach($heatmap['programs'] as $program)
                        <th class="text-center" style="min-width:80px">{{ $program }}</th>
                        @endforeach
                        <th class="text-center">Coverage</th>
                        <th class="text-center">Total</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($heatmap['matrix'] as $partner => $programs)
                @php
                    $total = array_sum($programs);
                    $covered = collect($programs)->filter(fn($c) => $c > 0)->count();
                    $coveragePct = count($heatmap['programs']) > 0 ? round(($covered / count($heatmap['programs'])) * 100) : 0;
                    $coverageColor = match(true) {
                        $coveragePct >= 70 => 'success',
                        $coveragePct >= 40 => 'warning',
                        default => 'danger',
                    };
                @endphp
                <tr>
                    <td class="sticky-col bg-white fw-500">{{ $partner }}</td>
                    @foreach($heatmap['programs'] as $program)
                    @php
                        $count = $programs[$program] ?? 0;
                    @endphp
                    <td class="text-center">
                        @if($count > 0)
                        <span class="badge bg-success bg-opacity-25 text-success">{{ $count }}</span>
                        @else
                        <span class="text-muted">-</span>
                        @endif
                    </td>
                    @endforeach
                    <td class="text-center">
                        <div class="progress" style="height:6px;width:60px;display:inline-block;vertical-align:middle">
                            <div class="progress-bar bg-{{ $coverageColor }}" style="width:{{ $coveragePct }}%"></div>
                        </div>
                        <small class="text-{{ $coverageColor }} ms-1">{{ $covered }}/{{ count($heatmap['programs']) }}</small>
                    </td>
                    <td class="text-center fw-500">{{ $total }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('styles')
<style>
.sticky-col { position: sticky; left: 0; z-index: 1; }
</style>
@endpush
