@extends('layouts.app')

@section('title', 'Modifier le Compte Administrateur')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-user-edit me-2"></i>
        Modifier le Compte Administrateur
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.accounts.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Retour à la liste
        </a>
    </div>
</div>

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Informations du Compte</h5>
            </div>
            <div class="card-body">
                @if(!isset($adminAccount) || !$adminAccount)
                    <div class="alert alert-danger">
                        <strong>Erreur:</strong> Le compte administrateur n'a pas été trouvé.
                    </div>
                @else
                <form action="{{ route('admin.accounts.update', $adminAccount->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <!-- Informations personnelles -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-user me-2"></i>Informations Personnelles
                            </h6>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nom') is-invalid @enderror" 
                                       id="nom" name="nom" value="{{ old('nom', $adminAccount->utilisateur->nom) }}" required>
                                @error('nom')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="prenom" class="form-label">Prénom <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('prenom') is-invalid @enderror" 
                                       id="prenom" name="prenom" value="{{ old('prenom', $adminAccount->utilisateur->prenom) }}" required>
                                @error('prenom')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email', $adminAccount->utilisateur->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="telephone" class="form-label">Téléphone</label>
                                <input type="text" class="form-control @error('telephone') is-invalid @enderror" 
                                       id="telephone" name="telephone" value="{{ old('telephone', $adminAccount->utilisateur->telephone) }}">
                                @error('telephone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="sexe" class="form-label">Sexe <span class="text-danger">*</span></label>
                                <select class="form-select @error('sexe') is-invalid @enderror" id="sexe" name="sexe" required>
                                    <option value="">Sélectionner...</option>
                                    <option value="M" {{ old('sexe', $adminAccount->utilisateur->sexe) == 'M' ? 'selected' : '' }}>Masculin</option>
                                    <option value="F" {{ old('sexe', $adminAccount->utilisateur->sexe) == 'F' ? 'selected' : '' }}>Féminin</option>
                                </select>
                                @error('sexe')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="date_naissance" class="form-label">Date de naissance <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('date_naissance') is-invalid @enderror" 
                                       id="date_naissance" name="date_naissance" value="{{ old('date_naissance', $adminAccount->utilisateur->date_naissance) }}" required>
                                @error('date_naissance')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="adresse" class="form-label">Adresse</label>
                                <textarea class="form-control @error('adresse') is-invalid @enderror" 
                                          id="adresse" name="adresse" rows="2">{{ old('adresse', $adminAccount->utilisateur->adresse) }}</textarea>
                                @error('adresse')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Informations professionnelles -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-briefcase me-2"></i>Informations Professionnelles
                            </h6>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="poste" class="form-label">Poste <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('poste') is-invalid @enderror" 
                                       id="poste" name="poste" value="{{ old('poste', $adminAccount->poste) }}" required>
                                @error('poste')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="departement" class="form-label">Département</label>
                                <input type="text" class="form-control @error('departement') is-invalid @enderror" 
                                       id="departement" name="departement" value="{{ old('departement', $adminAccount->departement) }}">
                                @error('departement')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="date_embauche" class="form-label">Date d'embauche <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('date_embauche') is-invalid @enderror" 
                                       id="date_embauche" name="date_embauche" value="{{ old('date_embauche', $adminAccount->date_embauche) }}" required>
                                @error('date_embauche')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="salaire" class="form-label">Salaire (GNF)</label>
                                <input type="number" class="form-control @error('salaire') is-invalid @enderror" 
                                       id="salaire" name="salaire" value="{{ old('salaire', $adminAccount->salaire) }}" min="0" step="0.01">
                                @error('salaire')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="statut" class="form-label">Statut <span class="text-danger">*</span></label>
                                <select class="form-select @error('statut') is-invalid @enderror" id="statut" name="statut" required>
                                    <option value="actif" {{ old('statut', $adminAccount->statut) == 'actif' ? 'selected' : '' }}>Actif</option>
                                    <option value="inactif" {{ old('statut', $adminAccount->statut) == 'inactif' ? 'selected' : '' }}>Inactif</option>
                                    <option value="suspendu" {{ old('statut', $adminAccount->statut) == 'suspendu' ? 'selected' : '' }}>Suspendu</option>
                                </select>
                                @error('statut')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Photo de profil -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-camera me-2"></i>Photo de Profil
                            </h6>
                        </div>
                        <div class="col-12">
                            @if($adminAccount->utilisateur->photo_profil)
                            <div class="mb-3">
                                <label class="form-label">Photo actuelle</label>
                                <div>
                                    <img src="{{ asset('storage/' . $adminAccount->utilisateur->photo_profil) }}" 
                                         alt="Photo actuelle" 
                                         class="rounded" 
                                         style="width: 100px; height: 100px; object-fit: cover;">
                                </div>
                            </div>
                            @endif
                            <div class="mb-3">
                                <label for="photo_profil" class="form-label">Nouvelle photo de profil</label>
                                <input type="file" class="form-control @error('photo_profil') is-invalid @enderror" 
                                       id="photo_profil" name="photo_profil" accept="image/*">
                                <div class="form-text">Formats acceptés: JPEG, PNG, JPG, GIF. Taille max: 2MB</div>
                                @error('photo_profil')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Observations -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-sticky-note me-2"></i>Observations
                            </h6>
                        </div>
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="observations" class="form-label">Observations</label>
                                <textarea class="form-control @error('observations') is-invalid @enderror" 
                                          id="observations" name="observations" rows="3">{{ old('observations', $adminAccount->observations) }}</textarea>
                                @error('observations')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <a href="{{ route('admin.accounts.index') }}" class="btn btn-secondary me-2">Annuler</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>
                            Mettre à jour
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Permissions</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Sélectionnez au moins une permission pour ce compte administrateur.
                </div>
                <div class="d-flex justify-content-end mb-3">
                    <button type="button" class="btn btn-outline-primary btn-sm me-2" onclick="selectAllPermissions()">
                        <i class="fas fa-check-square me-1"></i>
                        Tout sélectionner
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="deselectAllPermissions()">
                        <i class="fas fa-square me-1"></i>
                        Tout désélectionner
                    </button>
                </div>
                
                <div class="permissions-container" style="max-height: 400px; overflow-y: auto;">
                    <div class="row">
                        @foreach($permissions as $category => $perms)
                            <div class="col-md-6 mb-3">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">{{ $category }}</h6>
                                    </div>
                                    <div class="card-body">
                                        @foreach($perms as $key => $label)
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       name="permissions[]" 
                                                       value="{{ $key }}" 
                                                       id="permission_{{ $key }}"
                                                       {{ in_array($key, old('permissions', $adminAccount->permissions ?? [])) ? 'checked' : '' }}>
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
                </div>
                
                @error('permissions')
                    <div class="text-danger mt-2">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
</div>
@endif

@section('scripts')
<script>
function selectAllPermissions() {
    document.querySelectorAll('input[name="permissions[]"]').forEach(checkbox => {
        checkbox.checked = true;
    });
}

function deselectAllPermissions() {
    document.querySelectorAll('input[name="permissions[]"]').forEach(checkbox => {
        checkbox.checked = false;
    });
}
</script>
@endsection
