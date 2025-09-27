@extends('layouts.app')

@section('title', 'Renouveler la Carte Enseignant')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-sync me-2"></i>Renouveler la Carte Enseignant</h2>
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
                            <h5 class="mb-0"><i class="fas fa-sync me-2"></i>Renouvellement de la Carte</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Information :</strong> Le renouvellement d'une carte créera une nouvelle carte avec un nouveau numéro et désactivera l'ancienne carte.
                            </div>

                            <form action="{{ route('cartes-enseignants.traiter-renouvellement', $cartes_enseignant) }}" method="POST">
                                @csrf
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="date_expiration" class="form-label">Nouvelle date d'expiration <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control @error('date_expiration') is-invalid @enderror" 
                                                   id="date_expiration" name="date_expiration" 
                                                   value="{{ old('date_expiration', now()->addYear()->format('Y-m-d')) }}" 
                                                   min="{{ now()->addDay()->format('Y-m-d') }}" required>
                                            @error('date_expiration')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">La date doit être postérieure à aujourd'hui</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="observations" class="form-label">Observations sur le renouvellement</label>
                                    <textarea class="form-control @error('observations') is-invalid @enderror" 
                                              id="observations" name="observations" rows="3" 
                                              placeholder="Raison du renouvellement, observations...">{{ old('observations') }}</textarea>
                                    @error('observations')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Maximum 500 caractères</div>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <a href="{{ route('cartes-enseignants.show', $cartes_enseignant) }}" class="btn btn-secondary me-2">
                                        <i class="fas fa-times me-2"></i>Annuler
                                    </a>
                                    <button type="submit" class="btn btn-success" 
                                            onclick="return confirm('Êtes-vous sûr de vouloir renouveler cette carte ? L\'ancienne carte sera désactivée.')">
                                        <i class="fas fa-sync me-2"></i>Renouveler la Carte
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Informations de la carte actuelle -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-id-card me-2"></i>Carte Actuelle</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>Numéro :</strong> {{ $cartes_enseignant->numero_carte }}
                            </div>
                            <div class="mb-3">
                                <strong>Type :</strong> 
                                <span class="badge bg-info">{{ $cartes_enseignant->type_carte_libelle }}</span>
                            </div>
                            <div class="mb-3">
                                <strong>Statut :</strong>
                                @php
                                    $badgeClass = match($cartes_enseignant->statut) {
                                        'active' => 'bg-success',
                                        'expiree' => 'bg-danger',
                                        'suspendue' => 'bg-warning',
                                        'annulee' => 'bg-secondary',
                                        default => 'bg-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ $cartes_enseignant->statut_libelle }}</span>
                            </div>
                            <div class="mb-3">
                                <strong>Date d'émission :</strong> {{ $cartes_enseignant->date_emission->format('d/m/Y') }}
                            </div>
                            <div class="mb-3">
                                <strong>Date d'expiration :</strong> 
                                <span class="{{ $cartes_enseignant->date_expiration < now() ? 'text-danger' : '' }}">
                                    {{ $cartes_enseignant->date_expiration->format('d/m/Y') }}
                                </span>
                            </div>
                            @if($cartes_enseignant->observations)
                                <div class="mb-3">
                                    <strong>Observations :</strong>
                                    <p class="text-muted small">{{ $cartes_enseignant->observations }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Informations de l'enseignant -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-user me-2"></i>Enseignant</h5>
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
                                <strong>Département :</strong> {{ $cartes_enseignant->enseignant->departement ?? 'Non renseigné' }}
                            </div>
                            <div class="mb-2">
                                <strong>Grade :</strong> {{ $cartes_enseignant->enseignant->grade ?? 'Non renseigné' }}
                            </div>
                        </div>
                    </div>

                    <!-- Aperçu de la nouvelle carte -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-eye me-2"></i>Aperçu Nouvelle Carte</h5>
                        </div>
                        <div class="card-body">
                            <div class="card border" style="width: 100%; height: 120px;">
                                <div class="card-body p-2">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <div>
                                            <h6 class="card-title mb-0 text-primary fw-bold" style="font-size: 0.7rem;">CARTE ENSEIGNANT</h6>
                                            <small class="text-muted" style="font-size: 0.6rem;">Nouveau numéro</small>
                                        </div>
                                        <div class="text-end">
                                            <small class="text-muted" style="font-size: 0.6rem;" id="preview-expiration">Nouvelle date</small>
                                        </div>
                                    </div>
                                    <hr class="my-1">
                                    <div class="d-flex align-items-center mb-1">
                                        <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 20px; height: 20px; font-size: 0.6rem;">
                                            {{ substr($cartes_enseignant->enseignant->utilisateur->nom, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold" style="font-size: 0.7rem;">{{ $cartes_enseignant->enseignant->utilisateur->nom }}</div>
                                            <div class="fw-bold" style="font-size: 0.7rem;">{{ $cartes_enseignant->enseignant->utilisateur->prenom }}</div>
                                        </div>
                                    </div>
                                    <div class="mb-1">
                                        <small class="text-muted" style="font-size: 0.6rem;">N° Employé: {{ $cartes_enseignant->enseignant->numero_employe }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dateExpiration = document.getElementById('date_expiration');
    const previewExpiration = document.getElementById('preview-expiration');
    
    function updatePreview() {
        if (dateExpiration.value) {
            const date = new Date(dateExpiration.value);
            const formattedDate = date.toLocaleDateString('fr-FR', { 
                month: '2-digit', 
                year: 'numeric' 
            });
            previewExpiration.textContent = formattedDate;
        }
    }
    
    dateExpiration.addEventListener('change', updatePreview);
    updatePreview(); // Initial update
});
</script>
@endsection












