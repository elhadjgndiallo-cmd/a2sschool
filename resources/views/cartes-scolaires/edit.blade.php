@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-edit text-warning me-2"></i>
                        Modifier la Carte Scolaire
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('cartes-scolaires.show', $cartes_scolaire) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Retour
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('cartes-scolaires.update', $cartes_scolaire) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Informations de la carte</h5>
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
                                                             width="40" height="40" 
                                                             alt="Photo">
                                                    @else
                                                        <div class="bg-secondary rounded-circle me-2 d-flex align-items-center justify-content-center" 
                                                             style="width: 40px; height: 40px;">
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
                                            <label class="form-label">Numéro de carte</label>
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
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Modifications possibles</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="statut" class="form-label">Statut <span class="text-danger">*</span></label>
                                            <select class="form-select @error('statut') is-invalid @enderror" 
                                                    id="statut" 
                                                    name="statut" 
                                                    required>
                                                <option value="active" {{ old('statut', $cartes_scolaire->statut) == 'active' ? 'selected' : '' }}>Active</option>
                                                <option value="expiree" {{ old('statut', $cartes_scolaire->statut) == 'expiree' ? 'selected' : '' }}>Expirée</option>
                                                <option value="suspendue" {{ old('statut', $cartes_scolaire->statut) == 'suspendue' ? 'selected' : '' }}>Suspendue</option>
                                                <option value="annulee" {{ old('statut', $cartes_scolaire->statut) == 'annulee' ? 'selected' : '' }}>Annulée</option>
                                            </select>
                                            @error('statut')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="date_expiration" class="form-label">Date d'expiration <span class="text-danger">*</span></label>
                                            <input type="date" 
                                                   class="form-control @error('date_expiration') is-invalid @enderror" 
                                                   id="date_expiration" 
                                                   name="date_expiration" 
                                                   value="{{ old('date_expiration', $cartes_scolaire->date_expiration->format('Y-m-d')) }}" 
                                                   required>
                                            @error('date_expiration')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="observations" class="form-label">Observations</label>
                                            <textarea class="form-control @error('observations') is-invalid @enderror" 
                                                      id="observations" 
                                                      name="observations" 
                                                      rows="4" 
                                                      placeholder="Observations sur la carte...">{{ old('observations', $cartes_scolaire->observations) }}</textarea>
                                            @error('observations')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="alert alert-warning">
                                    <h6><i class="fas fa-exclamation-triangle me-2"></i>Attention :</h6>
                                    <ul class="mb-0">
                                        <li>La modification du statut ou de la date d'expiration sera enregistrée avec votre nom comme validateur</li>
                                        <li>Certaines modifications peuvent affecter la validité de la carte</li>
                                        <li>Les informations de base (élève, numéro, type) ne peuvent pas être modifiées</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-end">
                                    <a href="{{ route('cartes-scolaires.show', $cartes_scolaire) }}" class="btn btn-secondary me-2">
                                        <i class="fas fa-times me-2"></i>Annuler
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Enregistrer les modifications
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
@endsection
