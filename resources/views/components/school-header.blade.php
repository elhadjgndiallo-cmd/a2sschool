{{-- Composant pour l'en-tête de l'école sur les documents --}}
@props(['size' => 'normal', 'showYear' => true, 'showContact' => true])

<div class="school-header text-center {{ $size === 'small' ? 'mb-3' : 'mb-4' }}">
    <div class="row align-items-center">
        {{-- Logo --}}
        @if($schoolHeader['logo'])
            <div class="col-md-2">
                <img src="{{ $schoolHeader['logo'] }}" 
                     alt="Logo" 
                     class="img-fluid" 
                     style="max-height: {{ $size === 'small' ? '60px' : '80px' }};">
            </div>
        @endif
        
        {{-- Informations principales --}}
        <div class="{{ $schoolHeader['logo'] ? 'col-md-8' : 'col-md-10' }}">
            <h2 class="{{ $size === 'small' ? 'h4' : 'h2' }} text-primary mb-1 fw-bold">
                {{ $schoolHeader['title'] }}
            </h2>
            
            @if($schoolHeader['subtitle'])
                <p class="text-muted fst-italic mb-2">{{ $schoolHeader['subtitle'] }}</p>
            @endif
            
            @if($showContact && $schoolHeader['address'])
                <p class="mb-1 small">
                    <i class="fas fa-map-marker-alt me-1"></i>{{ $schoolHeader['address'] }}
                </p>
            @endif
            
            @if($showContact && $schoolHeader['contact'])
                <p class="mb-2 small">{{ $schoolHeader['contact'] }}</p>
            @endif
            
            @if($showYear && $schoolHeader['year'])
                <div class="badge bg-primary fs-6">
                    Année Scolaire: {{ $schoolHeader['year'] }}
                </div>
            @endif
        </div>
        
        {{-- Cachet --}}
        @if($schoolHeader['cachet'])
            <div class="col-md-2">
                <img src="{{ $schoolHeader['cachet'] }}" 
                     alt="Cachet" 
                     class="img-fluid" 
                     style="max-height: {{ $size === 'small' ? '50px' : '70px' }};">
            </div>
        @endif
    </div>
    
    {{-- Ligne de séparation --}}
    <hr class="border-primary border-2 mt-3">
</div>

{{-- CSS pour l'impression --}}
<style>
@media print {
    .school-header {
        break-inside: avoid;
    }
    .school-header img {
        max-height: 60px !important;
    }
}
</style>

