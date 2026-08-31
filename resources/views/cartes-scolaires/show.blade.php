@extends('layouts.app')

@push('styles')
    @include('cartes-scolaires._carte-styles')
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-id-card text-primary me-2"></i>
                        Détails de la Carte Scolaire
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('cartes-scolaires.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Retour
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="row">
                        <!-- Informations de la carte -->
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Informations de la carte</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-borderless">
                                                <tr>
                                                    <td><strong>Numéro de carte :</strong></td>
                                                    <td><span class="badge bg-info fs-6">{{ $cartes_scolaire->numero_carte }}</span></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Type :</strong></td>
                                                    <td><span class="badge bg-primary">{{ $cartes_scolaire->type_carte_libelle }}</span></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Statut :</strong></td>
                                                    <td>
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
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Date d'émission :</strong></td>
                                                    <td>{{ $cartes_scolaire->date_emission ? $cartes_scolaire->date_emission->format('d/m/Y') : 'Non définie' }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Date d'expiration :</strong></td>
                                                    <td>
                                                        @if($cartes_scolaire->date_expiration)
                                                            <span class="{{ $cartes_scolaire->date_expiration < now() ? 'text-danger fw-bold' : 'text-success' }}">
                                                                {{ $cartes_scolaire->date_expiration->format('d/m/Y') }}
                                                            </span>
                                                            @if($cartes_scolaire->est_valide)
                                                                <i class="fas fa-check-circle text-success ms-2"></i>
                                                            @else
                                                                <i class="fas fa-times-circle text-danger ms-2"></i>
                                                            @endif
                                                        @else
                                                            <span class="text-warning">Non définie</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-borderless">
                                                <tr>
                                                    <td><strong>Émise par :</strong></td>
                                                    <td>{{ $cartes_scolaire->emisePar->nom ?? 'N/A' }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Validée par :</strong></td>
                                                    <td>{{ $cartes_scolaire->valideePar->nom ?? 'Non validée' }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Créée le :</strong></td>
                                                    <td>{{ $cartes_scolaire->created_at ? $cartes_scolaire->created_at->format('d/m/Y à H:i') : 'Non définie' }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Modifiée le :</strong></td>
                                                    <td>{{ $cartes_scolaire->updated_at ? $cartes_scolaire->updated_at->format('d/m/Y à H:i') : 'Non définie' }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                    
                                    @if($cartes_scolaire->observations)
                                        <div class="mt-3">
                                            <h6>Observations :</h6>
                                            <div class="alert alert-light border">
                                                {{ $cartes_scolaire->observations }}
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Informations de l'élève -->
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Informations de l'élève</h5>
                                </div>
                                <div class="card-body text-center">
                                    @if($cartes_scolaire->eleve->utilisateur->photo_profil)
                                        <img src="{{ asset('storage/' . $cartes_scolaire->eleve->utilisateur->photo_profil) }}" 
                                             class="rounded-circle mb-3" 
                                             width="120" height="120" 
                                             alt="Photo de l'élève">
                                    @else
                                        <div class="bg-secondary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" 
                                             style="width: 120px; height: 120px;">
                                            <i class="fas fa-user fa-3x text-white"></i>
                                        </div>
                                    @endif
                                    
                                    <h5>{{ $cartes_scolaire->eleve->utilisateur->nom }} {{ $cartes_scolaire->eleve->utilisateur->prenom }}</h5>
                                    <p class="text-muted mb-2">{{ $cartes_scolaire->eleve->numero_etudiant }}</p>
                                    
                                    @if($cartes_scolaire->eleve->classe)
                                        <span class="badge bg-secondary mb-2">{{ $cartes_scolaire->eleve->classe->nom }}</span>
                                    @endif
                                    
                                    <div class="mt-3">
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            Né(e) le {{ $cartes_scolaire->eleve->utilisateur->date_naissance ? $cartes_scolaire->eleve->utilisateur->date_naissance->format('d/m/Y') : 'Non définie' }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Aperçu de la carte -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Aperçu de la carte scolaire</h5>
                                </div>
                                <div class="card-body">
                                    <div class="carte-preview-wrap">
                                        <div class="carte-preview-stage">
                                            @include('cartes-scolaires._carte', ['carte' => $cartes_scolaire])
                                        </div>
                                    </div>
                                    <p class="text-muted text-center mt-2 mb-0">
                                        <small>Dimensions : 86 mm × 54 mm (format carte d'identité standard)</small>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4 no-print">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex flex-wrap gap-2">
                                <a href="{{ route('cartes-scolaires.edit', $cartes_scolaire) }}" class="btn btn-warning">
                                    <i class="fas fa-edit me-1"></i> Modifier
                                </a>
                                <a href="{{ route('cartes-scolaires.imprimer', $cartes_scolaire) }}" class="btn btn-info" target="_blank">
                                    <i class="fas fa-print me-1"></i> Imprimer
                                </a>
                                @if($cartes_scolaire->statut === 'active')
                                    <a href="{{ route('cartes-scolaires.renouveler', $cartes_scolaire) }}" class="btn btn-success">
                                        <i class="fas fa-sync me-1"></i> Renouveler
                                    </a>
                                @endif
                                <form action="{{ route('cartes-scolaires.destroy', $cartes_scolaire) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette carte ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger">
                                        <i class="fas fa-trash me-1"></i> Supprimer
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
