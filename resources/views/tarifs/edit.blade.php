@extends('layouts.app')

@section('title', 'Modifier le Tarif')

@section('content')
<div class="container-fluid">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-edit me-2"></i>Modifier le Tarif
            </h1>
            <p class="text-muted">{{ $tarif->classe->nom }} - {{ $tarif->annee_scolaire }}</p>
        </div>
        <div>
            <a href="{{ route('tarifs.show', $tarif) }}" class="btn btn-info">
                <i class="fas fa-eye me-2"></i>Voir
            </a>
            <a href="{{ route('tarifs.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Retour
            </a>
        </div>
    </div>

    <!-- Formulaire -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-edit me-2"></i>Modifier les Informations
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('tarifs.update', $tarif) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Classe et Année Scolaire -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="classe_id" class="form-label text-danger">
                                    <i class="fas fa-chalkboard-teacher me-1"></i>Classe
                                </label>
                                <select class="form-select @error('classe_id') is-invalid @enderror" 
                                        id="classe_id" name="classe_id" required>
                                    <option value="">Sélectionner une classe</option>
                                    @foreach($classes as $classe)
                                    <option value="{{ $classe->id }}" {{ old('classe_id', $tarif->classe_id) == $classe->id ? 'selected' : '' }}>
                                        {{ $classe->nom }} - {{ $classe->niveau ?? 'N/A' }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('classe_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="annee_scolaire" class="form-label text-danger">
                                    <i class="fas fa-calendar-alt me-1"></i>Année Scolaire
                                </label>
                                <input type="text" class="form-control @error('annee_scolaire') is-invalid @enderror" 
                                       id="annee_scolaire" name="annee_scolaire" 
                                       value="{{ old('annee_scolaire', $tarif->annee_scolaire) }}" 
                                       placeholder="Ex: 2025-2026" required>
                                @error('annee_scolaire')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Frais de Scolarité -->
                        <h6 class="text-primary border-bottom pb-2 mb-3">
                            <i class="fas fa-money-bill-wave me-2"></i>Frais de Scolarité
                        </h6>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="frais_inscription" class="form-label">
                                    <i class="fas fa-file-signature me-1"></i>Frais d'Inscription (GNF)
                                </label>
                                <input type="number" class="form-control @error('frais_inscription') is-invalid @enderror" 
                                       id="frais_inscription" name="frais_inscription" 
                                       value="{{ old('frais_inscription', $tarif->frais_inscription) }}" 
                                       min="0" step="100">
                                @error('frais_inscription')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="frais_reinscription" class="form-label">
                                    <i class="fas fa-user-check me-1"></i>Frais de Réinscription (GNF)
                                </label>
                                <input type="number" class="form-control @error('frais_reinscription') is-invalid @enderror" 
                                       id="frais_reinscription" name="frais_reinscription" 
                                       value="{{ old('frais_reinscription', $tarif->frais_reinscription) }}" 
                                       min="0" step="100">
                                <small class="text-muted">Pour les élèves déjà inscrits les années précédentes</small>
                                @error('frais_reinscription')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="frais_scolarite_mensuel" class="form-label">
                                    <i class="fas fa-graduation-cap me-1"></i>Scolarité Mensuelle (GNF)
                                </label>
                                <input type="number" class="form-control @error('frais_scolarite_mensuel') is-invalid @enderror" 
                                       id="frais_scolarite_mensuel" name="frais_scolarite_mensuel" 
                                       value="{{ old('frais_scolarite_mensuel', $tarif->frais_scolarite_mensuel) }}" 
                                       min="0" step="100">
                                @error('frais_scolarite_mensuel')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="frais_cantine_mensuel" class="form-label">
                                    <i class="fas fa-utensils me-1"></i>Cantine Mensuelle (GNF)
                                </label>
                                <input type="number" class="form-control @error('frais_cantine_mensuel') is-invalid @enderror" 
                                       id="frais_cantine_mensuel" name="frais_cantine_mensuel" 
                                       value="{{ old('frais_cantine_mensuel', $tarif->frais_cantine_mensuel) }}" 
                                       min="0" step="100">
                                @error('frais_cantine_mensuel')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="frais_transport_mensuel" class="form-label">
                                    <i class="fas fa-bus me-1"></i>Transport Mensuel (GNF)
                                </label>
                                <input type="number" class="form-control @error('frais_transport_mensuel') is-invalid @enderror" 
                                       id="frais_transport_mensuel" name="frais_transport_mensuel" 
                                       value="{{ old('frais_transport_mensuel', $tarif->frais_transport_mensuel) }}" 
                                       min="0" step="100">
                                @error('frais_transport_mensuel')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Autres Frais -->
                        <h6 class="text-primary border-bottom pb-2 mb-3">
                            <i class="fas fa-plus-circle me-2"></i>Autres Frais
                        </h6>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="frais_uniforme" class="form-label">
                                    <i class="fas fa-tshirt me-1"></i>Uniforme (GNF)
                                </label>
                                <input type="number" class="form-control @error('frais_uniforme') is-invalid @enderror" 
                                       id="frais_uniforme" name="frais_uniforme" 
                                       value="{{ old('frais_uniforme', $tarif->frais_uniforme) }}" 
                                       min="0" step="100">
                                @error('frais_uniforme')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="frais_livres" class="form-label">
                                    <i class="fas fa-book me-1"></i>Livres (GNF)
                                </label>
                                <input type="number" class="form-control @error('frais_livres') is-invalid @enderror" 
                                       id="frais_livres" name="frais_livres" 
                                       value="{{ old('frais_livres', $tarif->frais_livres) }}" 
                                       min="0" step="100">
                                @error('frais_livres')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="frais_autres" class="form-label">
                                    <i class="fas fa-ellipsis-h me-1"></i>Autres Frais (GNF)
                                </label>
                                <input type="number" class="form-control @error('frais_autres') is-invalid @enderror" 
                                       id="frais_autres" name="frais_autres" 
                                       value="{{ old('frais_autres', $tarif->frais_autres) }}" 
                                       min="0" step="100">
                                @error('frais_autres')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Configuration Mensuelle -->
                        <h6 class="text-primary border-bottom pb-2 mb-3">
                            <i class="fas fa-credit-card me-2"></i>Configuration Mensuelle
                        </h6>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" 
                                           id="paiement_par_tranches" name="paiement_par_tranches" 
                                           value="1" {{ old('paiement_par_tranches', $tarif->paiement_par_tranches) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="paiement_par_tranches">
                                        <i class="fas fa-calendar-check me-1"></i>Paiement mensuel
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="nombre_tranches" class="form-label">
                                    <i class="fas fa-calendar-alt me-1"></i>Nombre des Mois
                                </label>
                                <select class="form-select @error('nombre_tranches') is-invalid @enderror" 
                                        id="nombre_tranches" name="nombre_tranches">
                                    @for($i = 1; $i <= 9; $i++)
                                    <option value="{{ $i }}" {{ old('nombre_tranches', $tarif->nombre_tranches) == $i ? 'selected' : '' }}>
                                        {{ $i }} mois
                                    </option>
                                    @endfor
                                </select>
                                <small class="text-muted">Nombre de mois d'école dans l'année</small>
                                @error('nombre_tranches')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="periode_tranche" class="form-label">
                                    <i class="fas fa-clock me-1"></i>Fréquence
                                </label>
                                <select class="form-select @error('periode_tranche') is-invalid @enderror" 
                                        id="periode_tranche" name="periode_tranche">
                                    <option value="mensuel" {{ old('periode_tranche', $tarif->periode_tranche) == 'mensuel' ? 'selected' : '' }}>
                                        Mensuel
                                    </option>
                                    <option value="trimestriel" {{ old('periode_tranche', $tarif->periode_tranche) == 'trimestriel' ? 'selected' : '' }}>
                                        Trimestriel
                                    </option>
                                    <option value="semestriel" {{ old('periode_tranche', $tarif->periode_tranche) == 'semestriel' ? 'selected' : '' }}>
                                        Semestriel
                                    </option>
                                    <option value="annuel" {{ old('periode_tranche', $tarif->periode_tranche) == 'annuel' ? 'selected' : '' }}>
                                        Annuel
                                    </option>
                                </select>
                                @error('periode_tranche')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Statut et Description -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" 
                                           id="actif" name="actif" 
                                           value="1" {{ old('actif', $tarif->actif) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="actif">
                                        <i class="fas fa-toggle-on me-1"></i>Tarif actif
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">
                                <i class="fas fa-comment me-1"></i>Description (optionnel)
                            </label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3" 
                                      placeholder="Description des frais, conditions particulières...">{{ old('description', $tarif->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Boutons -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('tarifs.show', $tarif) }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Mettre à jour
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Aperçu actuel -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-eye me-2"></i>Valeurs Actuelles
                    </h5>
                </div>
                <div class="card-body">
                    @php
                        $totalMensuelActuel = $tarif->frais_scolarite_mensuel + 
                                            $tarif->frais_cantine_mensuel + 
                                            $tarif->frais_transport_mensuel;
                        $totalAnnuelActuel = $tarif->frais_inscription + 
                                           ($totalMensuelActuel * $tarif->nombre_tranches) + 
                                           $tarif->frais_uniforme + 
                                           $tarif->frais_livres + 
                                           $tarif->frais_autres;
                    @endphp

                    <div class="mb-2">
                        <strong>Inscription actuelle:</strong>
                        <span class="float-end">{{ number_format($tarif->frais_inscription, 0, ',', ' ') }} GNF</span>
                    </div>
                    <div class="mb-2">
                        <strong>Mensuel actuel:</strong>
                        <span class="float-end text-info">{{ number_format($totalMensuelActuel, 0, ',', ' ') }} GNF</span>
                    </div>
                    <hr>
                    <div class="mb-2">
                        <strong>Annuel actuel:</strong>
                        <span class="float-end text-success">{{ number_format($totalAnnuelActuel, 0, ',', ' ') }} GNF</span>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-calculator me-2"></i>Nouveau Calcul
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Information:</strong> Les nouveaux montants s'afficheront ici lors de la modification.
                    </div>
                    
                    <div id="cost-preview">
                        <div class="mb-2">
                            <strong>Nouvelle inscription:</strong>
                            <span id="preview-inscription" class="float-end">{{ number_format($tarif->frais_inscription, 0, ',', ' ') }} GNF</span>
                        </div>
                        <div class="mb-2">
                            <strong>Nouveau mensuel:</strong>
                            <span id="preview-mensuel" class="float-end text-primary">{{ number_format($totalMensuelActuel, 0, ',', ' ') }} GNF</span>
                        </div>
                        <hr>
                        <div class="mb-2">
                            <strong>Nouveau annuel:</strong>
                            <span id="preview-annuel" class="float-end text-success">{{ number_format($totalAnnuelActuel, 0, ',', ' ') }} GNF</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Calcul en temps réel des coûts
document.addEventListener('DOMContentLoaded', function() {
    const inputs = [
        'frais_inscription',
        'frais_reinscription',
        'frais_scolarite_mensuel',
        'frais_cantine_mensuel',
        'frais_transport_mensuel',
        'frais_uniforme',
        'frais_livres',
        'frais_autres'
    ];

    function updatePreview() {
        const inscription = parseInt(document.getElementById('frais_inscription').value) || 0;
        const scolarite = parseInt(document.getElementById('frais_scolarite_mensuel').value) || 0;
        const cantine = parseInt(document.getElementById('frais_cantine_mensuel').value) || 0;
        const transport = parseInt(document.getElementById('frais_transport_mensuel').value) || 0;
        const uniforme = parseInt(document.getElementById('frais_uniforme').value) || 0;
        const livres = parseInt(document.getElementById('frais_livres').value) || 0;
        const autres = parseInt(document.getElementById('frais_autres').value) || 0;

        const mensuel = scolarite + cantine + transport;
        const annuel = inscription + (mensuel * 12) + uniforme + livres + autres;

        document.getElementById('preview-inscription').textContent = inscription.toLocaleString() + ' GNF';
        document.getElementById('preview-mensuel').textContent = mensuel.toLocaleString() + ' GNF';
        document.getElementById('preview-annuel').textContent = annuel.toLocaleString() + ' GNF';
    }

    inputs.forEach(inputId => {
        const input = document.getElementById(inputId);
        if (input) {
            input.addEventListener('input', updatePreview);
        }
    });

    updatePreview(); // Initial calculation
});
</script>
@endsection
