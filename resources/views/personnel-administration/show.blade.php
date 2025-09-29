@extends('layouts.app')

@section('title', 'Détails du Personnel')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-user-tie me-2"></i>Détails du Personnel d'Administration
                    </h3>
                    <div class="btn-group">
                        <a href="{{ route('personnel-administration.edit', $personnelAdministration) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-1"></i>Modifier
                        </a>
                        <a href="{{ route('personnel-administration.permissions', $personnelAdministration) }}" class="btn btn-primary">
                            <i class="fas fa-key me-1"></i>Permissions
                        </a>
                        <a href="{{ route('personnel-administration.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Retour
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <!-- Photo et informations principales -->
                        <div class="col-md-4">
                            <div class="text-center mb-4">
                                @php use Illuminate\Support\Facades\Storage; @endphp
                                @if($personnelAdministration->utilisateur->photo_profil && Storage::disk('public')->exists($personnelAdministration->utilisateur->photo_profil))
                                    <img src="{{ asset('images/profile_images/' . basename($personnelAdministration->utilisateur->photo_profi)) }}" 
                                         alt="Photo" 
                                         class="img-thumbnail rounded-circle mb-3" 
                                         style="width: 200px; height: 200px; object-fit: cover;">
                                @else
                                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white mx-auto mb-3" 
                                         style="width: 200px; height: 200px;">
                                        <i class="fas fa-user fa-4x"></i>
                                    </div>
                                @endif
                                <h4 class="mb-1">{{ $personnelAdministration->utilisateur->nom }} {{ $personnelAdministration->utilisateur->prenom }}</h4>
                                <p class="text-muted mb-2">{{ $personnelAdministration->poste }}</p>
                                @if($personnelAdministration->statut === 'actif')
                                    <span class="badge bg-success fs-6">Actif</span>
                                @elseif($personnelAdministration->statut === 'inactif')
                                    <span class="badge bg-secondary fs-6">Inactif</span>
                                @else
                                    <span class="badge bg-warning fs-6">Suspendu</span>
                                @endif
                            </div>
                        </div>

                        <!-- Informations détaillées -->
                        <div class="col-md-8">
                            <div class="row">
                                <!-- Informations personnelles -->
                                <div class="col-md-6">
                                    <div class="card h-100">
                                        <div class="card-header bg-primary text-white">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-user me-2"></i>Informations Personnelles
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <strong>Email:</strong><br>
                                                <span class="text-muted">{{ $personnelAdministration->utilisateur->email }}</span>
                                            </div>
                                            <div class="mb-3">
                                                <strong>Téléphone:</strong><br>
                                                <span class="text-muted">{{ $personnelAdministration->utilisateur->telephone ?? 'Non renseigné' }}</span>
                                            </div>
                                            <div class="mb-3">
                                                <strong>Sexe:</strong><br>
                                                <span class="text-muted">{{ $personnelAdministration->utilisateur->sexe === 'M' ? 'Masculin' : 'Féminin' }}</span>
                                            </div>
                                            <div class="mb-3">
                                                <strong>Date de naissance:</strong><br>
                                                <span class="text-muted">{{ $personnelAdministration->utilisateur->date_naissance->format('d/m/Y') }}</span>
                                            </div>
                                            <div class="mb-3">
                                                <strong>Adresse:</strong><br>
                                                <span class="text-muted">{{ $personnelAdministration->utilisateur->adresse ?? 'Non renseignée' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Informations professionnelles -->
                                <div class="col-md-6">
                                    <div class="card h-100">
                                        <div class="card-header bg-info text-white">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-briefcase me-2"></i>Informations Professionnelles
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <strong>Poste:</strong><br>
                                                <span class="badge bg-info">{{ $personnelAdministration->poste }}</span>
                                            </div>
                                            <div class="mb-3">
                                                <strong>Département:</strong><br>
                                                <span class="text-muted">{{ $personnelAdministration->departement ?? 'Non défini' }}</span>
                                            </div>
                                            <div class="mb-3">
                                                <strong>Date d'embauche:</strong><br>
                                                <span class="text-muted">{{ $personnelAdministration->date_embauche->format('d/m/Y') }}</span>
                                            </div>
                                            <div class="mb-3">
                                                <strong>Ancienneté:</strong><br>
                                                <span class="text-muted">{{ $personnelAdministration->date_embauche->diffForHumans() }}</span>
                                            </div>
                                            <div class="mb-3">
                                                <strong>Salaire:</strong><br>
                                                @if($personnelAdministration->salaire)
                                                    <span class="text-success fw-bold">{{ number_format($personnelAdministration->salaire, 0, ',', ' ') }} GNF</span>
                                                @else
                                                    <span class="text-muted">Non défini</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Permissions -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header bg-success text-white">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-key me-2"></i>Permissions Attribuées
                                                <span class="badge bg-light text-dark ms-2">{{ count($personnelAdministration->permissions ?? []) }}</span>
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            @if($personnelAdministration->permissions && count($personnelAdministration->permissions) > 0)
                                                <div class="row">
                                                    @php
                                                        $permissionsByCategory = [
                                                            'Élèves' => array_filter($personnelAdministration->permissions, fn($key) => str_starts_with($key, 'eleves')),
                                                            'Enseignants' => array_filter($personnelAdministration->permissions, fn($key) => str_starts_with($key, 'enseignants')),
                                                            'Classes' => array_filter($personnelAdministration->permissions, fn($key) => str_starts_with($key, 'classes')),
                                                            'Matières' => array_filter($personnelAdministration->permissions, fn($key) => str_starts_with($key, 'matieres')),
                                                            'Emplois du temps' => array_filter($personnelAdministration->permissions, fn($key) => str_starts_with($key, 'emplois_temps')),
                                                            'Absences' => array_filter($personnelAdministration->permissions, fn($key) => str_starts_with($key, 'absences')),
                                                            'Notes' => array_filter($personnelAdministration->permissions, fn($key) => str_starts_with($key, 'notes')),
                                                            'Paiements' => array_filter($personnelAdministration->permissions, fn($key) => str_starts_with($key, 'paiements')),
                                                            'Dépenses' => array_filter($personnelAdministration->permissions, fn($key) => str_starts_with($key, 'depenses')),
                                                            'Statistiques' => array_filter($personnelAdministration->permissions, fn($key) => str_starts_with($key, 'statistiques')),
                                                            'Notifications' => array_filter($personnelAdministration->permissions, fn($key) => str_starts_with($key, 'notifications')),
                                                            'Rapports' => array_filter($personnelAdministration->permissions, fn($key) => str_starts_with($key, 'rapports')),
                                                            'Cartes Enseignants' => array_filter($personnelAdministration->permissions, fn($key) => str_starts_with($key, 'cartes_enseignants')),
                                                        ];
                                                    @endphp

                                                    @foreach($permissionsByCategory as $category => $categoryPermissions)
                                                        @if(count($categoryPermissions) > 0)
                                                            <div class="col-md-6 col-lg-4 mb-3">
                                                                <div class="border rounded p-3">
                                                                    <h6 class="text-primary mb-2">
                                                                        <i class="fas fa-folder me-1"></i>{{ $category }}
                                                                        <span class="badge bg-primary ms-1">{{ count($categoryPermissions) }}</span>
                                                                    </h6>
                                                                    <div class="d-flex flex-wrap gap-1">
                                                                        @foreach($categoryPermissions as $permission)
                                                                            <span class="badge bg-success">{{ $permission }}</span>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="text-center text-muted py-4">
                                                    <i class="fas fa-lock fa-3x mb-3"></i>
                                                    <p>Aucune permission attribuée</p>
                                                    <a href="{{ route('personnel-administration.permissions', $personnelAdministration) }}" class="btn btn-primary">
                                                        <i class="fas fa-key me-1"></i>Attribuer des permissions
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Observations -->
                            @if($personnelAdministration->observations)
                                <div class="row mt-4">
                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-header bg-warning text-dark">
                                                <h6 class="card-title mb-0">
                                                    <i class="fas fa-sticky-note me-2"></i>Observations
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <p class="mb-0">{{ $personnelAdministration->observations }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection









