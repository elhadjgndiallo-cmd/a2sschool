@extends('layouts.app')

@section('title', 'Gestion des Permissions')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-key me-2"></i>Gestion des Permissions
                    </h3>
                    <p class="text-muted mb-0">{{ $personnelAdministration->utilisateur->nom }} {{ $personnelAdministration->utilisateur->prenom }} - {{ $personnelAdministration->poste }}</p>
                </div>
                
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('personnel-administration.update-permissions', $personnelAdministration) }}">
                        @csrf
                        @method('PUT')
                        
                        <!-- Informations du personnel -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="text-center">
                                    @php use Illuminate\Support\Facades\Storage; @endphp
                                    @if($personnelAdministration->utilisateur->photo_profil && Storage::disk('public')->exists($personnelAdministration->utilisateur->photo_profil))
                                        <img src="{{ asset('images/profile_images/' . basename($personnelAdministration->utilisateur->photo_profi)) }}" 
                                             alt="Photo" 
                                             class="img-thumbnail rounded-circle mb-2" 
                                             style="width: 100px; height: 100px; object-fit: cover;">
                                    @else
                                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white mx-auto mb-2" 
                                             style="width: 100px; height: 100px;">
                                            {{ substr($personnelAdministration->utilisateur->prenom, 0, 1) }}{{ substr($personnelAdministration->utilisateur->nom, 0, 1) }}
                                        </div>
                                    @endif
                                    <h6 class="mb-0">{{ $personnelAdministration->utilisateur->nom }} {{ $personnelAdministration->utilisateur->prenom }}</h6>
                                    <small class="text-muted">{{ $personnelAdministration->poste }}</small>
                                </div>
                            </div>
                            <div class="col-md-9">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Email:</strong> {{ $personnelAdministration->utilisateur->email }}</p>
                                        <p><strong>Département:</strong> {{ $personnelAdministration->departement ?? 'Non défini' }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Date d'embauche:</strong> {{ $personnelAdministration->date_embauche->format('d/m/Y') }}</p>
                                        <p><strong>Statut:</strong> 
                                            @if($personnelAdministration->statut === 'actif')
                                                <span class="badge bg-success">Actif</span>
                                            @elseif($personnelAdministration->statut === 'inactif')
                                                <span class="badge bg-secondary">Inactif</span>
                                            @else
                                                <span class="badge bg-warning">Suspendu</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Permissions par catégorie -->
                        <div class="row">
                            @php
                                $permissionsByCategory = [
                                    'Élèves' => array_filter($permissions, fn($key) => str_starts_with($key, 'eleves'), ARRAY_FILTER_USE_KEY),
                                    'Enseignants' => array_filter($permissions, fn($key) => str_starts_with($key, 'enseignants'), ARRAY_FILTER_USE_KEY),
                                    'Classes' => array_filter($permissions, fn($key) => str_starts_with($key, 'classes'), ARRAY_FILTER_USE_KEY),
                                    'Matières' => array_filter($permissions, fn($key) => str_starts_with($key, 'matieres'), ARRAY_FILTER_USE_KEY),
                                    'Emplois du temps' => array_filter($permissions, fn($key) => str_starts_with($key, 'emplois_temps'), ARRAY_FILTER_USE_KEY),
                                    'Absences' => array_filter($permissions, fn($key) => str_starts_with($key, 'absences'), ARRAY_FILTER_USE_KEY),
                                    'Notes' => array_filter($permissions, fn($key) => str_starts_with($key, 'notes'), ARRAY_FILTER_USE_KEY),
                                    'Paiements' => array_filter($permissions, fn($key) => str_starts_with($key, 'paiements'), ARRAY_FILTER_USE_KEY),
                                    'Dépenses' => array_filter($permissions, fn($key) => str_starts_with($key, 'depenses'), ARRAY_FILTER_USE_KEY),
                                    'Statistiques' => array_filter($permissions, fn($key) => str_starts_with($key, 'statistiques'), ARRAY_FILTER_USE_KEY),
                                    'Notifications' => array_filter($permissions, fn($key) => str_starts_with($key, 'notifications'), ARRAY_FILTER_USE_KEY),
                                    'Rapports' => array_filter($permissions, fn($key) => str_starts_with($key, 'rapports'), ARRAY_FILTER_USE_KEY),
                                    'Cartes Enseignants' => array_filter($permissions, fn($key) => str_starts_with($key, 'cartes_enseignants'), ARRAY_FILTER_USE_KEY),
                                ];
                            @endphp

                            @foreach($permissionsByCategory as $category => $categoryPermissions)
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card h-100">
                                        <div class="card-header bg-light">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-folder me-2"></i>{{ $category }}
                                                <span class="badge bg-primary ms-2">{{ count($categoryPermissions) }}</span>
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            @foreach($categoryPermissions as $key => $label)
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="permissions[]" value="{{ $key }}" 
                                                           id="permission_{{ $key }}"
                                                           {{ in_array($key, $personnelAdministration->permissions ?? []) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="permission_{{ $key }}">
                                                        {{ $label }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Actions globales -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <i class="fas fa-tools me-2"></i>Actions Rapides
                                        </h6>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-outline-primary" onclick="selectAllPermissions()">
                                                <i class="fas fa-check-double me-1"></i>Tout sélectionner
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary" onclick="deselectAllPermissions()">
                                                <i class="fas fa-times me-1"></i>Tout désélectionner
                                            </button>
                                            <button type="button" class="btn btn-outline-info" onclick="selectViewOnly()">
                                                <i class="fas fa-eye me-1"></i>Lecture seule
                                            </button>
                                            <button type="button" class="btn btn-outline-success" onclick="selectFullAccess()">
                                                <i class="fas fa-user-shield me-1"></i>Accès complet
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Boutons d'action -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('personnel-administration.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left me-1"></i>Retour à la liste
                                    </a>
                                    <div>
                                        <a href="{{ route('personnel-administration.edit', $personnelAdministration) }}" class="btn btn-outline-warning me-2">
                                            <i class="fas fa-edit me-1"></i>Modifier le profil
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i>Sauvegarder les permissions
                                        </button>
                                    </div>
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
function selectAllPermissions() {
    const checkboxes = document.querySelectorAll('input[name="permissions[]"]');
    checkboxes.forEach(checkbox => checkbox.checked = true);
}

function deselectAllPermissions() {
    const checkboxes = document.querySelectorAll('input[name="permissions[]"]');
    checkboxes.forEach(checkbox => checkbox.checked = false);
}

function selectViewOnly() {
    deselectAllPermissions();
    const viewCheckboxes = document.querySelectorAll('input[name="permissions[]"][value$=".view"]');
    viewCheckboxes.forEach(checkbox => checkbox.checked = true);
}

function selectFullAccess() {
    selectAllPermissions();
}
</script>
@endsection









