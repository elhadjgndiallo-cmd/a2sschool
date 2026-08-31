@extends('layouts.app')

@section('title', 'Détails de la Carte Administration')

@php
use Illuminate\Support\Facades\Storage;
$personnel = $cartes_personnel_administration->personnelAdministration;
$user = $personnel->utilisateur ?? null;
@endphp

@push('styles')
    @include('cartes-enseignants._carte-styles')
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-id-card me-2"></i>Détails de la Carte Administration</h2>
                <div>
                    <a href="{{ route('cartes-personnel-administration.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Retour
                    </a>
                </div>
            </div>

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
                            <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informations de la Carte</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Numéro de carte</label>
                                        <p class="form-control-plaintext">{{ $cartes_personnel_administration->numero_carte }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Statut</label>
                                        <div>
                                            @php
                                                $badgeClass = match($cartes_personnel_administration->statut) {
                                                    'active' => 'bg-success',
                                                    'expiree' => 'bg-danger',
                                                    'suspendue' => 'bg-warning',
                                                    'annulee' => 'bg-secondary',
                                                    default => 'bg-secondary'
                                                };
                                            @endphp
                                            <span class="badge {{ $badgeClass }} fs-6">{{ $cartes_personnel_administration->statut_libelle }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Type de carte</label>
                                        <p class="form-control-plaintext">
                                            <span class="badge bg-info">{{ $cartes_personnel_administration->type_carte_libelle }}</span>
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Date d'émission</label>
                                        <p class="form-control-plaintext">{{ $cartes_personnel_administration->date_emission?->format('d/m/Y') ?? '—' }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Date d'expiration</label>
                                        <p class="form-control-plaintext {{ $cartes_personnel_administration->date_expiration && $cartes_personnel_administration->date_expiration < now() ? 'text-danger' : '' }}">
                                            {{ $cartes_personnel_administration->date_expiration?->format('d/m/Y') ?? '—' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Émise par</label>
                                        <p class="form-control-plaintext">
                                            {{ $cartes_personnel_administration->emisePar->nom ?? 'Non spécifié' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            @if($cartes_personnel_administration->observations)
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Observations</label>
                                    <p class="form-control-plaintext">{{ $cartes_personnel_administration->observations }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-user-tie me-2"></i>Personnel d'administration</h5>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-3">
                                @if($user?->photo_profil && Storage::disk('public')->exists($user->photo_profil))
                                    <img src="{{ asset('storage/' . $user->photo_profil) }}"
                                         alt="Photo"
                                         class="img-thumbnail rounded-circle mb-3"
                                         style="width: 100px; height: 100px; object-fit: cover;">
                                @else
                                    <div class="avatar-lg bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                        {{ substr($user->nom ?? 'A', 0, 1) }}
                                    </div>
                                @endif
                                <h5 class="mb-1">{{ $user->prenom ?? '' }} {{ $user->nom ?? '' }}</h5>
                                <p class="text-muted mb-0">{{ $personnel->poste ?? 'Administration' }}</p>
                            </div>
                            <hr>
                            <div class="mb-2">
                                <strong>Email :</strong> {{ $user->email ?? 'Non renseigné' }}
                            </div>
                            <div class="mb-2">
                                <strong>Téléphone :</strong> {{ $user->telephone ?? 'Non renseigné' }}
                            </div>
                            <div class="mb-2">
                                <strong>Département :</strong> {{ $personnel->departement ?? 'Non renseigné' }}
                            </div>
                            <div class="mb-2">
                                <strong>Adresse :</strong> {{ $user->adresse ?? 'Non renseignée' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-eye me-2"></i>Aperçu de la carte administration</h5>
                        </div>
                        <div class="card-body">
                            <div class="carte-preview-wrap">
                                <div class="carte-preview-stage">
                                    @include('cartes-personnel-administration._carte', ['carte' => $cartes_personnel_administration])
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
                        <a href="{{ route('cartes-personnel-administration.edit', $cartes_personnel_administration) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-1"></i> Modifier
                        </a>
                        <a href="{{ route('cartes-personnel-administration.imprimer', $cartes_personnel_administration) }}" class="btn btn-info" target="_blank">
                            <i class="fas fa-print me-1"></i> Imprimer
                        </a>
                        @if($cartes_personnel_administration->statut === 'active')
                            <a href="{{ route('cartes-personnel-administration.renouveler', $cartes_personnel_administration) }}" class="btn btn-success">
                                <i class="fas fa-sync me-1"></i> Renouveler
                            </a>
                        @endif
                        <form action="{{ route('cartes-personnel-administration.destroy', $cartes_personnel_administration) }}" method="POST" class="d-inline"
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
@endsection
