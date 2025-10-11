@extends('layouts.app')

@section('title', 'Détails de la Carte Enseignant')

@php
use Illuminate\Support\Facades\Storage;
use App\Helpers\SchoolHelper;

$schoolInfo = SchoolHelper::getDocumentInfo();
@endphp

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-id-card me-2"></i>Détails de la Carte Enseignant</h2>
                <div>
                    <a href="{{ route('cartes-enseignants.index') }}" class="btn btn-secondary me-2">
                        <i class="fas fa-arrow-left me-2"></i>Retour
                    </a>
                    <a href="{{ route('cartes-enseignants.edit', $cartes_enseignant) }}" class="btn btn-warning me-2">
                        <i class="fas fa-edit me-2"></i>Modifier
                    </a>
                    <a href="{{ route('cartes-enseignants.imprimer', $cartes_enseignant) }}" 
                       class="btn btn-info me-2" target="_blank">
                        <i class="fas fa-print me-2"></i>Imprimer
                    </a>
                    @if($cartes_enseignant->statut === 'active')
                        <a href="{{ route('cartes-enseignants.renouveler', $cartes_enseignant) }}" class="btn btn-success">
                            <i class="fas fa-sync me-2"></i>Renouveler
                        </a>
                    @endif
                </div>
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
                <!-- Informations de la carte -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informations de la Carte</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Numéro de carte</label>
                                        <p class="form-control-plaintext">{{ $cartes_enseignant->numero_carte }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Statut</label>
                                        <div>
                                            @php
                                                $badgeClass = match($cartes_enseignant->statut) {
                                                    'active' => 'bg-success',
                                                    'expiree' => 'bg-danger',
                                                    'suspendue' => 'bg-warning',
                                                    'annulee' => 'bg-secondary',
                                                    default => 'bg-secondary'
                                                };
                                            @endphp
                                            <span class="badge {{ $badgeClass }} fs-6">{{ $cartes_enseignant->statut_libelle }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Type de carte</label>
                                        <p class="form-control-plaintext">
                                            <span class="badge bg-info">{{ $cartes_enseignant->type_carte_libelle }}</span>
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Date d'émission</label>
                                        <p class="form-control-plaintext">{{ $cartes_enseignant->date_emission->format('d/m/Y') }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Date d'expiration</label>
                                        <p class="form-control-plaintext {{ $cartes_enseignant->date_expiration < now() ? 'text-danger' : '' }}">
                                            {{ $cartes_enseignant->date_expiration->format('d/m/Y') }}
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Émise par</label>
                                        <p class="form-control-plaintext">
                                            {{ $cartes_enseignant->emisePar->nom ?? 'Non spécifié' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            @if($cartes_enseignant->observations)
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Observations</label>
                                    <p class="form-control-plaintext">{{ $cartes_enseignant->observations }}</p>
                                </div>
                            @endif
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
                                @if($cartes_enseignant->enseignant->utilisateur->photo_profil && Storage::disk('public')->exists($cartes_enseignant->enseignant->utilisateur->photo_profil))
                                    <img src="{{ asset('storage/' . $cartes_enseignant->enseignant->utilisateur->photo_profil) }}" 
                                         alt="Photo enseignant" 
                                         class="img-thumbnail rounded-circle mb-3" 
                                         style="width: 100px; height: 100px; object-fit: cover;">
                                @else
                                    <div class="avatar-lg bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                        {{ substr($cartes_enseignant->enseignant->utilisateur->nom, 0, 1) }}
                                    </div>
                                @endif
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
                </div>
            </div>

            <!-- Aperçu de la carte -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-eye me-2"></i>Aperçu de la Carte Enseignant</h5>
                        </div>
                        <div class="card-body">
                            <div class="row justify-content-center">
                                <div class="col-md-8">
                                    <!-- Carte d'identité style Guinée -->
                                    <div class="card border-0 shadow-lg" style="width: 400px; height: 250px; margin: 0 auto; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                                        <div class="card-body p-3">
                                            <!-- En-tête avec drapeau et devise -->
                                            <div class="text-center mb-2">
                                                <div class="d-flex justify-content-center align-items-center mb-1">
                                                    <div class="bg-danger me-1" style="width: 20px; height: 12px;"></div>
                                                    <div class="bg-yellow me-1" style="width: 20px; height: 12px;"></div>
                                                    <div class="bg-green" style="width: 20px; height: 12px;"></div>
                                                </div>
                                                <h6 class="mb-0 fw-bold text-dark">RÉPUBLIQUE DE GUINÉE</h6>
                                                <small class="text-muted fw-bold">TRAVAIL - JUSTICE - SOLIDARITÉ</small>
                                            </div>
                                            
                                            <hr class="my-2 border-dark">
                                            
                                            <!-- Nom de l'école -->
                                            <div class="text-center mb-2">
                                                <h6 class="mb-0 fw-bold text-primary">{{ $schoolInfo['school_name'] }}</h6>
                                                <small class="text-muted">CARTE D'ENSEIGNANT</small>
                                            </div>
                                            
                                            <hr class="my-2 border-primary">
                                            
                                            <!-- Contenu principal -->
                                            <div class="row">
                                                <!-- Photo de l'enseignant -->
                                                <div class="col-4">
                                                    <div class="text-center">
                                                        @if($cartes_enseignant->enseignant->utilisateur->photo_profil && Storage::disk('public')->exists($cartes_enseignant->enseignant->utilisateur->photo_profil))
                                                            <img src="{{ asset('storage/' . $cartes_enseignant->enseignant->utilisateur->photo_profil) }}" 
                                                                 alt="Photo enseignant" 
                                                                 class="img-thumbnail rounded" 
                                                                 style="width: 80px; height: 100px; object-fit: cover;">
                                                        @else
                                                            <div class="bg-light border rounded d-flex align-items-center justify-content-center" 
                                                                 style="width: 80px; height: 100px; margin: 0 auto;">
                                                                <i class="fas fa-user fa-2x text-muted"></i>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                                
                                                <!-- Informations de l'enseignant -->
                                                <div class="col-8">
                                                    <div class="mb-1">
                                                        <small class="fw-bold text-dark">Nom:</small>
                                                        <small class="ms-1">{{ $cartes_enseignant->enseignant->utilisateur->nom }}</small>
                                                    </div>
                                                    <div class="mb-1">
                                                        <small class="fw-bold text-dark">Prénom:</small>
                                                        <small class="ms-1">{{ $cartes_enseignant->enseignant->utilisateur->prenom }}</small>
                                                    </div>
                                                    <div class="mb-1">
                                                        <small class="fw-bold text-dark">Spécialité:</small>
                                                        <small class="ms-1">{{ $cartes_enseignant->enseignant->specialite ?? 'Non renseigné' }}</small>
                                                    </div>
                                                    <div class="mb-1">
                                                        <small class="fw-bold text-dark">N° Téléphone:</small>
                                                        <small class="ms-1">{{ $cartes_enseignant->enseignant->utilisateur->telephone ?? 'Non renseigné' }}</small>
                                                    </div>
                                                    <div class="mb-1">
                                                        <small class="fw-bold text-dark">N° Employé:</small>
                                                        <small class="ms-1">{{ $cartes_enseignant->enseignant->numero_employe ?? 'Non renseigné' }}</small>
                                                    </div>
                                                    <div class="mb-1">
                                                        <small class="fw-bold text-dark">Statut:</small>
                                                        <small class="ms-1">{{ $cartes_enseignant->enseignant->statut ?? 'Non renseigné' }}</small>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Pied de carte -->
                                            <div class="mt-2 pt-2 border-top">
                                                <div class="row">
                                                    <div class="col-6">
                                                        <small class="text-muted">N° Carte: {{ $cartes_enseignant->numero_carte }}</small>
                                                    </div>
                                                    <div class="col-6 text-end">
                                                        <small class="text-muted">Exp: {{ $cartes_enseignant->date_expiration->format('m/Y') }}</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <p class="text-muted mt-3 text-center">
                                        <small>Dimensions : 86mm x 54mm (format carte d'identité standard)</small>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="d-flex justify-content-end">
                        <a href="{{ route('cartes-enseignants.edit', $cartes_enseignant) }}" class="btn btn-warning me-2">
                            <i class="fas fa-edit me-2"></i>Modifier
                        </a>
                        <a href="{{ route('cartes-enseignants.imprimer', $cartes_enseignant) }}" 
                           class="btn btn-info me-2" target="_blank">
                            <i class="fas fa-print me-2"></i>Imprimer
                        </a>
                        @if($cartes_enseignant->statut === 'active')
                            <a href="{{ route('cartes-enseignants.renouveler', $cartes_enseignant) }}" class="btn btn-success me-2">
                                <i class="fas fa-sync me-2"></i>Renouveler
                            </a>
                        @endif
                        <form action="{{ route('cartes-enseignants.destroy', $cartes_enseignant) }}" 
                              method="POST" 
                              class="d-inline"
                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette carte ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash me-2"></i>Supprimer
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

