{{-- Resource Utilization Heatmap --}}
<div class="card shadow-0 mb-g">
    <div class="card-header"><div class="card-title">Resource Utilization: Partners x Programs (Completed Delivery Resources)</div></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead>
                    <tr>
                        <th class="sticky-col bg-white" style="min-width:200px">Partner</th>
                        @foreach($heatmap['programs'] as $program)
                        <th class="text-center" style="min-width:70px">{{ $program }}</th>
                        @endforeach
                        <th class="text-center">Total</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($heatmap['matrix'] as $partner => $programs)
                @php $total = array_sum($programs); @endphp
                <tr>
                    <td class="sticky-col bg-white fw-500">{{ $partner }}</td>
                    @foreach($heatmap['programs'] as $program)
                    @php
                        $count = $programs[$program] ?? 0;
                        $bgClass = match(true) {
                            $count === 0 => '',
                            $count <= 2 => 'bg-danger bg-opacity-10 text-danger',
                            $count <= 5 => 'bg-warning bg-opacity-10 text-warning',
                            default => 'bg-success bg-opacity-10 text-success',
                        };
                    @endphp
                    <td class="text-center {{ $bgClass }}">{{ $count > 0 ? $count : '-' }}</td>
                    @endforeach
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
