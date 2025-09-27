@extends('layouts.app')

@section('title', 'Responsabilités de l\'Établissement')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-users-cog text-primary me-2"></i>
                    Responsabilités de l'Établissement
                </h1>
                @if($etablissement)
                    <a href="{{ route('etablissement.responsabilites.edit') }}" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i>Modifier les Responsabilités
                    </a>
                @endif
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($etablissement)
                <div class="row">
                    <!-- Responsables -->
                    <div class="col-md-6">
                        <div class="card shadow">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-user-tie me-2"></i>
                                    Responsables
                                </h5>
                            </div>
                            <div class="card-body">
                                @if($etablissement->dg || $etablissement->directeur_primaire)
                                    @if($etablissement->dg)
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">
                                                <i class="fas fa-crown me-1 text-warning"></i>Directeur Général
                                            </label>
                                            <p class="form-control-plaintext border p-2 bg-light">{{ $etablissement->dg }}</p>
                                        </div>
                                    @endif

                                    @if($etablissement->directeur_primaire)
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">
                                                <i class="fas fa-user-graduate me-1 text-info"></i>Directeur du Primaire
                                            </label>
                                            <p class="form-control-plaintext border p-2 bg-light">{{ $etablissement->directeur_primaire }}</p>
                                        </div>
                                    @endif

                                    <div class="mb-3">
                                        <label class="form-label fw-bold">
                                            <i class="fas fa-building me-1 text-success"></i>Statut de l'Établissement
                                        </label>
                                        <div>
                                            @php
                                                $statutBadge = match($etablissement->statut_etablissement) {
                                                    'prive' => ['bg-primary', 'Privé'],
                                                    'public' => ['bg-success', 'Public'],
                                                    'semi_prive' => ['bg-info', 'Semi-Privé'],
                                                    default => ['bg-secondary', 'Non défini']
                                                };
                                            @endphp
                                            <span class="badge {{ $statutBadge[0] }} fs-6">{{ $statutBadge[1] }}</span>
                                        </div>
                                    </div>
                                @else
                                    <div class="text-center py-4">
                                        <i class="fas fa-user-times fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">Aucun responsable défini</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Configuration Matricule -->
                    <div class="col-md-6">
                        <div class="card shadow">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-id-card me-2"></i>
                                    Configuration Matricule
                                </h5>
                            </div>
                            <div class="card-body">
                                @if($etablissement->prefixe_matricule)
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">
                                            <i class="fas fa-tag me-1 text-primary"></i>Préfixe Matricule
                                        </label>
                                        <p class="form-control-plaintext border p-2 bg-light font-monospace">{{ $etablissement->prefixe_matricule }}</p>
                                    </div>

                                    @if($etablissement->suffixe_matricule)
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">
                                                <i class="fas fa-tag me-1 text-secondary"></i>Suffixe Matricule
                                            </label>
                                            <p class="form-control-plaintext border p-2 bg-light font-monospace">{{ $etablissement->suffixe_matricule }}</p>
                                        </div>
                                    @endif

                                    <div class="alert alert-success">
                                        <i class="fas fa-eye me-2"></i>
                                        <strong>Format généré:</strong> 
                                        <code class="text-dark">{{ $etablissement->prefixe_matricule }}001{{ $etablissement->suffixe_matricule }}</code>
                                    </div>

                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Prochain matricule:</strong> 
                                        <code class="text-dark">{{ $etablissement->genererNumeroMatricule() }}</code>
                                    </div>
                                @else
                                    <div class="text-center py-4">
                                        <i class="fas fa-id-card-alt fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">Configuration de matricule non définie</p>
                                        <small class="text-muted">Les matricules seront générés automatiquement</small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-users-cog fa-4x text-muted mb-4"></i>
                        <h4 class="text-muted mb-3">Aucune responsabilité configurée</h4>
                        <p class="text-muted mb-4">
                            Configurez les responsabilités de votre établissement pour une meilleure gestion.
                        </p>
                        <a href="{{ route('etablissement.responsabilites.edit') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-plus me-2"></i>Configurer les Responsabilités
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection









































