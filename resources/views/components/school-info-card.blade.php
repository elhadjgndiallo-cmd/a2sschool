{{-- Composant pour afficher les informations de l'école dans une carte --}}
@props(['editable' => false])

<div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-school me-2"></i>
                Informations de l'École
            </h5>
            @if($editable)
                <div class="btn-group">
                    <a href="{{ route('etablissement.informations') }}" class="btn btn-sm btn-outline-light">
                        <i class="fas fa-edit me-1"></i>Modifier
                    </a>
                </div>
            @endif
        </div>
    </div>
    <div class="card-body">
        @if($schoolInfo['school'])
            <div class="row">
                {{-- Logo et informations principales --}}
                <div class="col-md-8">
                    <h4 class="text-primary mb-2">{{ $schoolInfo['school_name'] }}</h4>
                    
                    @if($schoolInfo['school_slogan'])
                        <p class="text-muted fst-italic mb-3">"{{ $schoolInfo['school_slogan'] }}"</p>
                    @endif
                    
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                <strong>Adresse:</strong><br>
                                <small>{{ $schoolInfo['school_address'] }}</small>
                            </p>
                        </div>
                        
                        <div class="col-md-6">
                            @if($schoolInfo['school_phone'])
                                <p class="mb-2">
                                    <i class="fas fa-phone text-primary me-2"></i>
                                    <strong>Téléphone:</strong><br>
                                    <small>{{ $schoolInfo['school_phone'] }}</small>
                                </p>
                            @endif
                            
                            @if($schoolInfo['school_email'])
                                <p class="mb-2">
                                    <i class="fas fa-envelope text-primary me-2"></i>
                                    <strong>Email:</strong><br>
                                    <small>{{ $schoolInfo['school_email'] }}</small>
                                </p>
                            @endif
                        </div>
                    </div>
                    
                    {{-- Responsables --}}
                    @if(isset($schoolInfo['dg']) && $schoolInfo['dg'] || isset($schoolInfo['directeur_primaire']) && $schoolInfo['directeur_primaire'])
                        <hr>
                        <div class="row">
                            @if(isset($schoolInfo['dg']) && $schoolInfo['dg'])
                                <div class="col-md-6">
                                    <p class="mb-2">
                                        <i class="fas fa-user-tie text-primary me-2"></i>
                                        <strong>Directeur Général:</strong><br>
                                        <small>{{ $schoolInfo['dg'] }}</small>
                                    </p>
                                </div>
                            @endif
                            
                            @if(isset($schoolInfo['directeur_primaire']) && $schoolInfo['directeur_primaire'])
                                <div class="col-md-6">
                                    <p class="mb-2">
                                        <i class="fas fa-user-graduate text-primary me-2"></i>
                                        <strong>Directeur Primaire:</strong><br>
                                        <small>{{ $schoolInfo['directeur_primaire'] }}</small>
                                    </p>
                                </div>
                            @endif
                        </div>
                    @endif
                    
                    {{-- Année scolaire --}}
                    @if(isset($schoolInfo['year']) && $schoolInfo['year'])
                        <hr>
                        <p class="mb-0">
                            <i class="fas fa-calendar-alt text-primary me-2"></i>
                            <strong>Année Scolaire:</strong> 
                            <span class="badge bg-success">{{ $schoolInfo['year_name'] ?? '' }}</span>
                            <small class="text-muted ms-2">{{ $schoolInfo['year_period'] ?? '' }}</small>
                        </p>
                    @endif
                </div>
                
                {{-- Images --}}
                <div class="col-md-4 text-center">
                    @if(isset($schoolInfo['logo_url']) && $schoolInfo['logo_url'])
                        <div class="mb-3">
                            <p class="small text-muted mb-2">Logo</p>
                            <img src="{{ $schoolInfo['logo_url'] }}" 
                                 alt="Logo" 
                                 class="img-thumbnail" 
                                 style="max-width: 120px; max-height: 120px;">
                        </div>
                    @endif
                    
                    @if(isset($schoolInfo['cachet_url']) && $schoolInfo['cachet_url'])
                        <div class="mb-3">
                            <p class="small text-muted mb-2">Cachet</p>
                            <img src="{{ $schoolInfo['cachet_url'] }}" 
                                 alt="Cachet" 
                                 class="img-thumbnail" 
                                 style="max-width: 100px; max-height: 100px;">
                        </div>
                    @endif
                </div>
            </div>
            
            @if(isset($schoolInfo['school_description']) && $schoolInfo['school_description'])
                <hr>
                <div class="alert alert-info">
                    <h6 class="alert-heading">
                        <i class="fas fa-info-circle me-2"></i>Description
                    </h6>
                    <p class="mb-0">{{ $schoolInfo['school_description'] }}</p>
                </div>
            @endif
        @else
            <div class="text-center py-4">
                <i class="fas fa-school fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Aucune information d'école configurée</h5>
                <p class="text-muted">Configurez les informations de votre établissement pour les utiliser dans vos documents.</p>
                @if($editable)
                    <a href="{{ route('etablissement.informations') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Configurer l'École
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>
