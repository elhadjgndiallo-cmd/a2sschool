@extends('layouts.app')

@section('title', 'Détails du Compte Administrateur')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-user me-2"></i>
        Détails du Compte Administrateur
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.accounts.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Retour à la liste
        </a>
        <a href="{{ route('admin.accounts.edit', $adminAccount->id) }}" class="btn btn-outline-warning ms-2">
            <i class="fas fa-edit me-1"></i>
            Modifier
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Photo de Profil</h5>
            </div>
            <div class="card-body text-center">
                @if($adminAccount->utilisateur->photo_profil && Storage::disk('public')->exists($adminAccount->utilisateur->photo_profil))
                    <img src="{{ asset('storage/' . $adminAccount->utilisateur->photo_profil) }}" 
                         alt="Photo de {{ $adminAccount->utilisateur->nom }}" 
                         class="rounded-circle mb-3" 
                         style="width: 150px; height: 150px; object-fit: cover;">
                @else
                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white mx-auto mb-3" style="width: 150px; height: 150px; font-size: 3rem;">
                        {{ substr($adminAccount->utilisateur->prenom, 0, 1) }}{{ substr($adminAccount->utilisateur->nom, 0, 1) }}
                    </div>
                @endif
                
                <h4 class="mb-1">{{ $adminAccount->utilisateur->nom }} {{ $adminAccount->utilisateur->prenom }}</h4>
                <p class="text-muted mb-3">{{ $adminAccount->poste }}</p>
                
                <span class="badge bg-{{ $adminAccount->statut === 'actif' ? 'success' : ($adminAccount->statut === 'inactif' ? 'danger' : 'warning') }} fs-6">
                    {{ ucfirst($adminAccount->statut) }}
                </span>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0">Actions Rapides</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.accounts.edit', $adminAccount->id) }}" class="btn btn-outline-warning">
                        <i class="fas fa-edit me-2"></i>Modifier le Compte
                    </a>
                    <a href="{{ route('admin.accounts.permissions', $adminAccount->id) }}" class="btn btn-outline-primary">
                        <i class="fas fa-key me-2"></i>Gérer les Permissions
                    </a>
                    <button type="button" class="btn btn-outline-secondary" onclick="resetPassword({{ $adminAccount->id }})">
                        <i class="fas fa-lock me-2"></i>Réinitialiser Mot de Passe
                    </button>
                    @if($adminAccount->utilisateur->email !== 'admin@gmail.com')
                    <button type="button" class="btn btn-outline-{{ $adminAccount->statut === 'actif' ? 'danger' : 'success' }}" onclick="toggleStatus({{ $adminAccount->id }})">
                        <i class="fas fa-{{ $adminAccount->statut === 'actif' ? 'ban' : 'check' }} me-2"></i>
                        {{ $adminAccount->statut === 'actif' ? 'Désactiver' : 'Activer' }} le Compte
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Informations Personnelles</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nom</label>
                            <p class="form-control-plaintext">{{ $adminAccount->utilisateur->nom }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Prénom</label>
                            <p class="form-control-plaintext">{{ $adminAccount->utilisateur->prenom }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Email</label>
                            <p class="form-control-plaintext">{{ $adminAccount->utilisateur->email }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Téléphone</label>
                            <p class="form-control-plaintext">{{ $adminAccount->utilisateur->telephone ?? 'Non renseigné' }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Sexe</label>
                            <p class="form-control-plaintext">{{ $adminAccount->utilisateur->sexe == 'M' ? 'Masculin' : 'Féminin' }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Date de naissance</label>
                            <p class="form-control-plaintext">{{ $adminAccount->utilisateur->date_naissance ? \Carbon\Carbon::parse($adminAccount->utilisateur->date_naissance)->format('d/m/Y') : 'Non renseignée' }}</p>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Adresse</label>
                            <p class="form-control-plaintext">{{ $adminAccount->utilisateur->adresse ?? 'Non renseignée' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0">Informations Professionnelles</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Poste</label>
                            <p class="form-control-plaintext">{{ $adminAccount->poste }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Département</label>
                            <p class="form-control-plaintext">{{ $adminAccount->departement ?? 'Non spécifié' }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Date d'embauche</label>
                            <p class="form-control-plaintext">{{ \Carbon\Carbon::parse($adminAccount->date_embauche)->format('d/m/Y') }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Salaire</label>
                            <p class="form-control-plaintext">
                                @if($adminAccount->salaire)
                                    {{ number_format($adminAccount->salaire, 0, ',', ' ') }} GNF
                                @else
                                    Non renseigné
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Statut</label>
                            <p class="form-control-plaintext">
                                <span class="badge bg-{{ $adminAccount->statut === 'actif' ? 'success' : ($adminAccount->statut === 'inactif' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($adminAccount->statut) }}
                                </span>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Compte créé le</label>
                            <p class="form-control-plaintext">{{ $adminAccount->created_at->format('d/m/Y à H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        @if($adminAccount->observations)
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0">Observations</h5>
            </div>
            <div class="card-body">
                <p class="form-control-plaintext">{{ $adminAccount->observations }}</p>
            </div>
        </div>
        @endif
        
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0">Permissions Accordées</h5>
            </div>
            <div class="card-body">
                @if(count($adminAccount->permissions ?? []) > 0)
                    <div class="row">
                        @php
                            $groupedPermissions = [
                                'Gestion des élèves' => array_filter($permissions, function($key) {
                                    return str_starts_with($key, 'eleves.');
                                }, ARRAY_FILTER_USE_KEY),
                                'Gestion des enseignants' => array_filter($permissions, function($key) {
                                    return str_starts_with($key, 'enseignants.');
                                }, ARRAY_FILTER_USE_KEY),
                                'Gestion des classes' => array_filter($permissions, function($key) {
                                    return str_starts_with($key, 'classes.');
                                }, ARRAY_FILTER_USE_KEY),
                                'Gestion des matières' => array_filter($permissions, function($key) {
                                    return str_starts_with($key, 'matieres.');
                                }, ARRAY_FILTER_USE_KEY),
                                'Gestion des emplois du temps' => array_filter($permissions, function($key) {
                                    return str_starts_with($key, 'emplois_temps.');
                                }, ARRAY_FILTER_USE_KEY),
                                'Gestion des absences' => array_filter($permissions, function($key) {
                                    return str_starts_with($key, 'absences.');
                                }, ARRAY_FILTER_USE_KEY),
                                'Gestion des notes' => array_filter($permissions, function($key) {
                                    return str_starts_with($key, 'notes.');
                                }, ARRAY_FILTER_USE_KEY),
                                'Gestion des paiements' => array_filter($permissions, function($key) {
                                    return str_starts_with($key, 'paiements.');
                                }, ARRAY_FILTER_USE_KEY),
                                'Gestion des dépenses' => array_filter($permissions, function($key) {
                                    return str_starts_with($key, 'depenses.');
                                }, ARRAY_FILTER_USE_KEY),
                                'Gestion des salaires' => array_filter($permissions, function($key) {
                                    return str_starts_with($key, 'salaires.');
                                }, ARRAY_FILTER_USE_KEY),
                                'Gestion des tarifs' => array_filter($permissions, function($key) {
                                    return str_starts_with($key, 'tarifs.');
                                }, ARRAY_FILTER_USE_KEY),
                                'Statistiques et rapports' => array_filter($permissions, function($key) {
                                    return str_starts_with($key, 'statistiques.') || str_starts_with($key, 'rapports.');
                                }, ARRAY_FILTER_USE_KEY),
                                'Notifications' => array_filter($permissions, function($key) {
                                    return str_starts_with($key, 'notifications.');
                                }, ARRAY_FILTER_USE_KEY),
                                'Cartes' => array_filter($permissions, function($key) {
                                    return str_starts_with($key, 'cartes_');
                                }, ARRAY_FILTER_USE_KEY),
                                'Gestion des comptes administrateurs' => array_filter($permissions, function($key) {
                                    return str_starts_with($key, 'admin_accounts.');
                                }, ARRAY_FILTER_USE_KEY),
                                'Gestion de l\'établissement' => array_filter($permissions, function($key) {
                                    return str_starts_with($key, 'etablissement.');
                                }, ARRAY_FILTER_USE_KEY),
                                'Gestion des années scolaires' => array_filter($permissions, function($key) {
                                    return str_starts_with($key, 'annees_scolaires.');
                                }, ARRAY_FILTER_USE_KEY)
                            ];
                        @endphp
                        
                        @foreach($groupedPermissions as $groupName => $groupPermissions)
                        @php
                            $userPermissionsInGroup = array_intersect_key($adminAccount->permissions ?? [], $groupPermissions);
                        @endphp
                        @if(count($userPermissionsInGroup) > 0)
                        <div class="col-md-6 mb-3">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0">{{ $groupName }}</h6>
                                </div>
                                <div class="card-body">
                                    @foreach($userPermissionsInGroup as $permission)
                                        <span class="badge bg-success me-1 mb-1">{{ $permissions[$permission] }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endif
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Aucune permission accordée à ce compte administrateur.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function resetPassword(id) {
    if (confirm('Êtes-vous sûr de vouloir réinitialiser le mot de passe de ce compte administrateur ?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ url('/admin/accounts') }}/${id}/reset-password`;
        form.style.display = 'none';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        form.appendChild(csrfToken);
        
        document.body.appendChild(form);
        form.submit();
    }
}

function toggleStatus(id) {
    if (confirm('Êtes-vous sûr de vouloir changer le statut de ce compte administrateur ?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ url('/admin/accounts') }}/${id}/toggle-status`;
        form.style.display = 'none';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        form.appendChild(csrfToken);
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush
@endsection








