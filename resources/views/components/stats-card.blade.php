@props([
    'title' => '',
    'value' => '',
    'icon' => 'fas fa-chart-line',
    'color' => 'primary',
    'trend' => null,
    'trendValue' => '',
    'trendText' => '',
    'colSize' => 'col-12 col-sm-6 col-md-3'
])

<div class="{{ $colSize }} mb-3">
    <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                    <div class="bg-{{ $color }} bg-opacity-10 rounded-circle p-3">
                        <i class="{{ $icon }} text-{{ $color }} fs-4"></i>
                    </div>
                </div>
                <div class="flex-grow-1 ms-3">
                    <h6 class="card-title text-muted mb-1">{{ $title }}</h6>
                    <h4 class="mb-0 fw-bold text-{{ $color }}">{{ $value }}</h4>
                    @if($trend)
                        <small class="text-{{ $trend === 'up' ? 'success' : ($trend === 'down' ? 'danger' : 'muted') }}">
                            <i class="fas fa-arrow-{{ $trend === 'up' ? 'up' : ($trend === 'down' ? 'down' : 'right') }} me-1"></i>
                            {{ $trendValue }}
                            @if($trendText)
                                <span class="text-muted">{{ $trendText }}</span>
                            @endif
                        </small>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
