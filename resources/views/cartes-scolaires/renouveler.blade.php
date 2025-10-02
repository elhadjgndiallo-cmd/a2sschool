@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-sync text-success me-2"></i>
                        Renouveler la Carte Scolaire
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('cartes-scolaires.show', $cartes_scolaire) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Retour
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('cartes-scolaires.traiter-renouvellement', $cartes_scolaire) }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <!-- Informations de la carte actuelle -->
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Carte actuelle</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label">Élève</label>
                                            <div class="form-control-plaintext">
                                                <div class="d-flex align-items-center">
                                                    @if($cartes_scolaire->eleve->utilisateur->photo_profil)
                                                        @php
                                                            $imageName = basename($cartes_scolaire->eleve->utilisateur->photo_profil);
                                                            $imagePath = 'storage/' . $carte->eleve->utilisateur->photo_profil;
                                                        @endphp
                                                        <img src="{{ asset($imagePath) }}" 
                                                             class="rounded-circle me-2" 
                                                             width="50" height="50" 
                                                             alt="Photo">
                                                    @else
                                                        <div class="bg-secondary rounded-circle me-2 d-flex align-items-center justify-content-center" 
                                                             style="width: 50px; height: 50px;">
                                                            <i class="fas fa-user text-white"></i>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <strong>{{ $cartes_scolaire->eleve->utilisateur->nom }} {{ $cartes_scolaire->eleve->utilisateur->prenom }}</strong>
                                                        <br>
                                                        <small class="text-muted">{{ $cartes_scolaire->eleve->numero_etudiant }}</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Numéro de carte actuelle</label>
                                            <div class="form-control-plaintext">
                                                <span class="badge bg-info fs-6">{{ $cartes_scolaire->numero_carte }}</span>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Type de carte</label>
                                            <div class="form-control-plaintext">
                                                <span class="badge bg-primary">{{ $cartes_scolaire->type_carte_libelle }}</span>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Date d'émission</label>
                                            <div class="form-control-plaintext">
                                                {{ $cartes_scolaire->date_emission->format('d/m/Y') }}
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Date d'expiration</label>
                                            <div class="form-control-plaintext">
                                                <span class="{{ $cartes_scolaire->date_expiration < now() ? 'text-danger fw-bold' : 'text-success' }}">
                                                    {{ $cartes_scolaire->date_expiration->format('d/m/Y') }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Statut actuel</label>
                                            <div class="form-control-plaintext">
                                                @php
                                                    $badgeClass = match($cartes_scolaire->statut) {
                                                        'active' => 'bg-success',
                                                        'expiree' => 'bg-danger',
                                                        'suspendue' => 'bg-warning',
                                                        'annulee' => 'bg-secondary',
                                                        default => 'bg-secondary'
                                                    };
                                                @endphp
                                                <span class="badge {{ $badgeClass }} fs-6">{{ $cartes_scolaire->statut_libelle }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Informations de la nouvelle carte -->
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Nouvelle carte</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="date_expiration" class="form-label">Date d'expiration de la nouvelle carte <span class="text-danger">*</span></label>
                                            <input type="date" 
                                                   class="form-control @error('date_expiration') is-invalid @enderror" 
                                                   id="date_expiration" 
                                                   name="date_expiration" 
                                                   value="{{ old('date_expiration') }}" 
                                                   min="{{ now()->addDay()->format('Y-m-d') }}"
                                                   required>
                                            @error('date_expiration')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">
                                                La date doit être postérieure à aujourd'hui
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="observations" class="form-label">Observations pour la nouvelle carte</label>
                                            <textarea class="form-control @error('observations') is-invalid @enderror" 
                                                      id="observations" 
                                                      name="observations" 
                                                      rows="4" 
                                                      placeholder="Raison du renouvellement, observations...">{{ old('observations') }}</textarea>
                                            @error('observations')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="alert alert-info">
                                            <h6><i class="fas fa-info-circle me-2"></i>Informations sur le renouvellement :</h6>
                                            <ul class="mb-0">
                                                <li>L'ancienne carte sera automatiquement annulée</li>
                                                <li>Un nouveau numéro de carte sera généré</li>
                                                <li>Un nouveau QR code sera créé</li>
                                                <li>La nouvelle carte sera de type "Remplacement"</li>
                                                <li>La nouvelle carte sera immédiatement active</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="alert alert-warning">
                                    <h6><i class="fas fa-exclamation-triangle me-2"></i>Attention :</h6>
                                    <p class="mb-0">
                                        Cette action est irréversible. L'ancienne carte sera définitivement annulée et ne pourra plus être utilisée.
                                        Assurez-vous que toutes les informations sont correctes avant de procéder au renouvellement.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-end">
                                    <a href="{{ route('cartes-scolaires.show', $cartes_scolaire) }}" class="btn btn-secondary me-2">
                                        <i class="fas fa-times me-2"></i>Annuler
                                    </a>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-sync me-2"></i>Renouveler la carte
                                    </button>
                                </div>
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
    const dateExpirationInput = document.getElementById('date_expiration');
    
    // Définir une date d'expiration par défaut (1 an à partir d'aujourd'hui)
    if (!dateExpirationInput.value) {
        const today = new Date();
        const oneYearFromNow = new Date(today.getFullYear() + 1, today.getMonth(), today.getDate());
        dateExpirationInput.value = oneYearFromNow.toISOString().split('T')[0];
    }
});
</script>
@endsection
