@props([
    'title' => '',
    'subtitle' => '',
    'icon' => 'fas fa-info-circle',
    'color' => 'primary',
    'colSize' => 'col-12 col-md-6',
    'class' => ''
])

<div class="{{ $colSize }} mb-3">
    <div class="card h-100 {{ $class }}">
        <div class="card-body">
            <div class="d-flex align-items-start">
                <div class="flex-shrink-0">
                    <div class="bg-{{ $color }} bg-opacity-10 rounded-circle p-2">
                        <i class="{{ $icon }} text-{{ $color }}"></i>
                    </div>
                </div>
                <div class="flex-grow-1 ms-3">
                    @if($title)
                        <h6 class="card-title mb-1">{{ $title }}</h6>
                    @endif
                    @if($subtitle)
                        <p class="card-text text-muted mb-0">{{ $subtitle }}</p>
                    @endif
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
</div>
