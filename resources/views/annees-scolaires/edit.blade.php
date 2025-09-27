@extends('layouts.app')

@section('title', 'Modifier l\'Année Scolaire')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-edit text-primary me-2"></i>
                    Modifier l'Année Scolaire: {{ $anneesScolaire->nom }}
                </h1>
                <a href="{{ route('annees-scolaires.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour à la liste
                </a>
            </div>

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-edit me-2"></i>
                        Modification de l'Année Scolaire
                        @if($anneesScolaire->active)
                            <span class="badge bg-success ms-2">ACTIVE</span>
                        @endif
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('annees-scolaires.update', $anneesScolaire) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nom" class="form-label">
                                        <i class="fas fa-tag me-1"></i>Nom de l'Année Scolaire *
                                    </label>
                                    <input type="text" class="form-control @error('nom') is-invalid @enderror" 
                                           id="nom" name="nom" value="{{ old('nom', $anneesScolaire->nom) }}" 
                                           placeholder="Ex: 2024-2025" required>
                                    <small class="text-muted">Format recommandé: AAAA-AAAA</small>
                                    @error('nom')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-info-circle me-1"></i>Statut Actuel
                                    </label>
                                    <div>
                                        @php
                                            $statut = $anneesScolaire->statut;
                                            $badgeClass = match($statut) {
                                                'en_cours' => 'bg-primary',
                                                'à_venir' => 'bg-info',
                                                'terminee' => 'bg-secondary',
                                                default => 'bg-light text-dark'
                                            };
                                            $statutText = match($statut) {
                                                'en_cours' => 'En cours',
                                                'à_venir' => 'À venir',
                                                'terminee' => 'Terminée',
                                                default => 'Indéterminé'
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }} fs-6">{{ $statutText }}</span>
                                        @if($anneesScolaire->active)
                                            <span class="badge bg-success fs-6 ms-2">ACTIVE</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="date_debut" class="form-label">
                                        <i class="fas fa-calendar-check me-1"></i>Date de Début *
                                    </label>
                                    <input type="date" class="form-control @error('date_debut') is-invalid @enderror" 
                                           id="date_debut" name="date_debut" 
                                           value="{{ old('date_debut', $anneesScolaire->date_debut->format('Y-m-d')) }}" required>
                                    @error('date_debut')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="date_fin" class="form-label">
                                        <i class="fas fa-calendar-times me-1"></i>Date de Fin *
                                    </label>
                                    <input type="date" class="form-control @error('date_fin') is-invalid @enderror" 
                                           id="date_fin" name="date_fin" 
                                           value="{{ old('date_fin', $anneesScolaire->date_fin->format('Y-m-d')) }}" required>
                                    @error('date_fin')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">
                                <i class="fas fa-align-left me-1"></i>Description
                            </label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3" 
                                      placeholder="Description optionnelle de l'année scolaire...">{{ old('description', $anneesScolaire->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if($anneesScolaire->active)
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Attention:</strong> Cette année scolaire est actuellement active. Toute modification affectera le système en cours.
                            </div>
                        @endif

                        <hr>

                        <div class="d-flex justify-content-between">
                            <div>
                                @if(!$anneesScolaire->active)
                                    <form action="{{ route('annees-scolaires.activer', $anneesScolaire) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success" 
                                                onclick="return confirm('Activer cette année scolaire ?')">
                                            <i class="fas fa-check me-2"></i>Activer cette Année
                                        </button>
                                    </form>
                                @endif
                            </div>
                            
                            <div>
                                <a href="{{ route('annees-scolaires.index') }}" class="btn btn-secondary me-2">
                                    <i class="fas fa-times me-2"></i>Annuler
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Enregistrer les Modifications
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validation: date_fin doit être après date_debut
    const dateDebutInput = document.getElementById('date_debut');
    const dateFinInput = document.getElementById('date_fin');
    
    function validateDates() {
        if (dateDebutInput.value && dateFinInput.value) {
            if (dateFinInput.value <= dateDebutInput.value) {
                dateFinInput.setCustomValidity('La date de fin doit être après la date de début');
            } else {
                dateFinInput.setCustomValidity('');
            }
        }
    }
    
    dateDebutInput.addEventListener('change', validateDates);
    dateFinInput.addEventListener('change', validateDates);
});
</script>
@endsection

