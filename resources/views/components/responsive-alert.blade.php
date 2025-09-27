@props([
    'type' => 'info',
    'dismissible' => true,
    'icon' => null,
    'title' => null
])

@php
    $iconMap = [
        'success' => 'fas fa-check-circle',
        'danger' => 'fas fa-exclamation-circle',
        'warning' => 'fas fa-exclamation-triangle',
        'info' => 'fas fa-info-circle',
        'primary' => 'fas fa-info-circle',
        'secondary' => 'fas fa-info-circle'
    ];
    
    $defaultIcon = $iconMap[$type] ?? 'fas fa-info-circle';
    $alertIcon = $icon ?? $defaultIcon;
@endphp

<div class="alert alert-{{ $type }} {{ $dismissible ? 'alert-dismissible fade show' : '' }} mb-3" role="alert">
    <div class="d-flex align-items-start">
        <div class="flex-shrink-0 me-2">
            <i class="{{ $alertIcon }}"></i>
        </div>
        <div class="flex-grow-1">
            @if($title)
                <h6 class="alert-heading mb-2">{{ $title }}</h6>
            @endif
            {{ $slot }}
        </div>
    </div>
    @if($dismissible)
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    @endif
</div>
