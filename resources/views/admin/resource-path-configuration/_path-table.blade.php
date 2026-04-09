{{-- Reusable path table partial --}}
@php $isCompact = $compact ?? false; @endphp
<table class="table table-hover m-0 {{ $isCompact ? 'table-sm' : '' }}">
    @if(!$isCompact)
    <thead>
        <tr>
            <th>Path Name</th>
            <th class="text-center">Type</th>
            <th>Stages</th>
            <th class="text-center">Gates</th>
            <th class="text-center">Templates</th>
            <th class="text-center">Enrolled</th>
            <th class="text-center">Status</th>
        </tr>
    </thead>
    @endif
    <tbody>
        @forelse($tablePaths as $path)
        @php
            $roleType = $pathRoleTypes[$path->id] ?? 'delivery';
            $roleColor = match($roleType) {
                'sales' => 'success',
                'pre-sales' => 'warning',
                default => 'info',
            };
            $roleLabel = match($roleType) {
                'sales' => 'Sales',
                'pre-sales' => 'Pre-Sales',
                default => 'Delivery',
            };
            // For compact view, show short name (strip prefix)
            $displayName = $isCompact ? (Str::afterLast($path->name, ': ') ?: $path->name) : $path->name;
        @endphp
        <tr data-role-type="{{ $roleType }}" data-path-name="{{ strtolower($path->name) }}">
            <td>
                <a href="{{ route('admin.resource-paths.show', $path) }}" class="fw-500 text-primary">{{ $displayName }}</a>
                @if(!$isCompact && $path->description)
                <br><small class="text-muted">{{ Str::limit($path->description, 60) }}</small>
                @endif
            </td>
            <td class="text-center">
                <span class="badge bg-{{ $roleColor }} {{ $roleType === 'pre-sales' ? 'text-dark' : '' }}">{{ $roleLabel }}</span>
            </td>
            @if(!$isCompact)
            <td>
                <div class="d-flex align-items-center flex-wrap gap-1">
                    @foreach($path->stages->sortBy('stage_number') as $stage)
                        <span class="badge" style="background-color: {{ $stage->color }}; font-size: 0.7rem;">{{ $stage->name }}</span>
                        @if(!$loop->last)
                        <i class="fal fa-chevron-right text-muted" style="font-size:0.5rem"></i>
                        @endif
                    @endforeach
                </div>
            </td>
            @else
            <td class="text-center text-muted small">{{ $path->stages_count ?? $path->stages->count() }}</td>
            @endif
            <td class="text-center">{{ $path->gates_count }}</td>
            <td class="text-center">{{ $path->task_templates_count }}</td>
            <td class="text-center">
                @if($path->enrollments_count > 0)
                <span class="badge bg-primary">{{ $path->enrollments_count }}</span>
                @else
                <span class="text-muted">—</span>
                @endif
            </td>
            @if(!$isCompact)
            <td class="text-center">
                @if($path->is_active)
                <span class="badge bg-success">Active</span>
                @else
                <span class="badge bg-secondary">Inactive</span>
                @endif
            </td>
            @endif
        </tr>
        @empty
        <tr><td colspan="{{ $isCompact ? 5 : 7 }}" class="text-center text-muted py-3">No paths in this category.</td></tr>
        @endforelse
    </tbody>
</table>
