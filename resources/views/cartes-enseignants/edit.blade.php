@extends('layouts.app')

@section('title', 'Modifier la Carte Enseignant')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-edit me-2"></i>Modifier la Carte Enseignant</h2>
                <a href="{{ route('cartes-enseignants.show', $cartes_enseignant) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour
                </a>
            </div>

            <!-- Messages de session -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Modifier les Informations</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('cartes-enseignants.update', $cartes_enseignant) }}" method="POST">
                                @csrf
                                @method('PUT')
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="numero_carte" class="form-label">Numéro de carte</label>
                                            <input type="text" class="form-control" id="numero_carte" 
                                                   value="{{ $cartes_enseignant->numero_carte }}" readonly>
                                            <div class="form-text">Le numéro de carte ne peut pas être modifié</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="type_carte" class="form-label">Type de carte <span class="text-danger">*</span></label>
                                            <select class="form-select @error('type_carte') is-invalid @enderror" 
                                                    id="type_carte" name="type_carte" required>
                                                <option value="">Sélectionner un type</option>
                                                <option value="standard" {{ old('type_carte', $cartes_enseignant->type_carte) == 'standard' ? 'selected' : '' }}>
                                                    Standard
                                                </option>
                                                <option value="temporaire" {{ old('type_carte', $cartes_enseignant->type_carte) == 'temporaire' ? 'selected' : '' }}>
                                                    Temporaire
                                                </option>
                                                <option value="remplacement" {{ old('type_carte', $cartes_enseignant->type_carte) == 'remplacement' ? 'selected' : '' }}>
                                                    Remplacement
                                                </option>
                                            </select>
                                            @error('type_carte')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="date_emission" class="form-label">Date d'émission <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control @error('date_emission') is-invalid @enderror" 
                                                   id="date_emission" name="date_emission" 
                                                   value="{{ old('date_emission', $cartes_enseignant->date_emission->format('Y-m-d')) }}" required>
                                            @error('date_emission')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="date_expiration" class="form-label">Date d'expiration <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control @error('date_expiration') is-invalid @enderror" 
                                                   id="date_expiration" name="date_expiration" 
                                                   value="{{ old('date_expiration', $cartes_enseignant->date_expiration->format('Y-m-d')) }}" required>
                                            @error('date_expiration')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="statut" class="form-label">Statut <span class="text-danger">*</span></label>
                                            <select class="form-select @error('statut') is-invalid @enderror" 
                                                    id="statut" name="statut" required>
                                                <option value="">Sélectionner un statut</option>
                                                <option value="active" {{ old('statut', $cartes_enseignant->statut) == 'active' ? 'selected' : '' }}>
                                                    Active
                                                </option>
                                                <option value="expiree" {{ old('statut', $cartes_enseignant->statut) == 'expiree' ? 'selected' : '' }}>
                                                    Expirée
                                                </option>
                                                <option value="suspendue" {{ old('statut', $cartes_enseignant->statut) == 'suspendue' ? 'selected' : '' }}>
                                                    Suspendue
                                                </option>
                                                <option value="annulee" {{ old('statut', $cartes_enseignant->statut) == 'annulee' ? 'selected' : '' }}>
                                                    Annulée
                                                </option>
                                            </select>
                                            @error('statut')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="observations" class="form-label">Observations</label>
                                    <textarea class="form-control @error('observations') is-invalid @enderror" 
                                              id="observations" name="observations" rows="3" 
                                              placeholder="Observations sur la carte...">{{ old('observations', $cartes_enseignant->observations) }}</textarea>
                                    @error('observations')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Maximum 500 caractères</div>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <a href="{{ route('cartes-enseignants.show', $cartes_enseignant) }}" class="btn btn-secondary me-2">
                                        <i class="fas fa-times me-2"></i>Annuler
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Enregistrer
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Informations de l'enseignant -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-user me-2"></i>Informations de l'Enseignant</h5>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <div class="avatar-lg bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                    {{ substr($cartes_enseignant->enseignant->utilisateur->nom, 0, 1) }}
                                </div>
                                <h5 class="mb-1">{{ $cartes_enseignant->enseignant->utilisateur->nom }} {{ $cartes_enseignant->enseignant->utilisateur->prenom }}</h5>
                                <p class="text-muted mb-0">{{ $cartes_enseignant->enseignant->numero_employe }}</p>
                            </div>
                            <hr>
                            <div class="mb-2">
                                <strong>Email :</strong> {{ $cartes_enseignant->enseignant->utilisateur->email }}
                            </div>
                            <div class="mb-2">
                                <strong>Téléphone :</strong> {{ $cartes_enseignant->enseignant->utilisateur->telephone ?? 'Non renseigné' }}
                            </div>
                            <div class="mb-2">
                                <strong>Département :</strong> {{ $cartes_enseignant->enseignant->departement ?? 'Non renseigné' }}
                            </div>
                            <div class="mb-2">
                                <strong>Grade :</strong> {{ $cartes_enseignant->enseignant->grade ?? 'Non renseigné' }}
                            </div>
                        </div>
                    </div>

                    <!-- Historique des modifications -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Informations de la Carte</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                <strong>Créée le :</strong> {{ $cartes_enseignant->created_at->format('d/m/Y à H:i') }}
                            </div>
                            <div class="mb-2">
                                <strong>Dernière modification :</strong> {{ $cartes_enseignant->updated_at->format('d/m/Y à H:i') }}
                            </div>
                            @if($cartes_enseignant->emisePar)
                                <div class="mb-2">
                                    <strong>Émise par :</strong> {{ $cartes_enseignant->emisePar->nom }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validation côté client pour s'assurer que la date d'expiration est après la date d'émission
    const dateEmission = document.getElementById('date_emission');
    const dateExpiration = document.getElementById('date_expiration');
    
    function validateDates() {
        if (dateEmission.value && dateExpiration.value) {
            const emission = new Date(dateEmission.value);
            const expiration = new Date(dateExpiration.value);
            
            if (expiration <= emission) {
                dateExpiration.setCustomValidity('La date d\'expiration doit être postérieure à la date d\'émission');
            } else {
                dateExpiration.setCustomValidity('');
            }
        }
    }
    
    dateEmission.addEventListener('change', validateDates);
    dateExpiration.addEventListener('change', validateDates);
});
</script>
@endsection












