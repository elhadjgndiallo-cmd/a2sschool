@extends('layouts.app')

@section('title', 'Créer une Année Scolaire')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-plus text-primary me-2"></i>
                    Créer une Année Scolaire
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
                        <i class="fas fa-calendar-plus me-2"></i>
                        Nouvelle Année Scolaire
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('annees-scolaires.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nom" class="form-label">
                                        <i class="fas fa-tag me-1"></i>Nom de l'Année Scolaire *
                                    </label>
                                    <input type="text" class="form-control @error('nom') is-invalid @enderror" 
                                           id="nom" name="nom" value="{{ old('nom') }}" 
                                           placeholder="Ex: 2024-2025" required>
                                    <small class="text-muted">Format recommandé: AAAA-AAAA</small>
                                    @error('nom')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
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
                                           id="date_debut" name="date_debut" value="{{ old('date_debut') }}" required>
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
                                           id="date_fin" name="date_fin" value="{{ old('date_fin') }}" required>
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
                                      placeholder="Description optionnelle de l'année scolaire...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Note:</strong> Si c'est votre première année scolaire, elle sera automatiquement activée.
                        </div>

                        <hr>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('annees-scolaires.index') }}" class="btn btn-secondary me-2">
                                <i class="fas fa-times me-2"></i>Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Créer l'Année Scolaire
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-générer le nom basé sur l'année de début
    const dateDebutInput = document.getElementById('date_debut');
    const nomInput = document.getElementById('nom');
    
    dateDebutInput.addEventListener('change', function() {
        if (this.value && !nomInput.value) {
            const anneeDebut = new Date(this.value).getFullYear();
            const anneeFin = anneeDebut + 1;
            nomInput.value = `${anneeDebut}-${anneeFin}`;
        }
    });
    
    // Validation: date_fin doit être après date_debut
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

