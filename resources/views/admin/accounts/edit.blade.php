@extends('layouts.app')

@section('title', 'Modifier le compte administrateur')

@php
use Illuminate\Support\Facades\Storage;
$utilisateur = $adminAccount->utilisateur ?? null;
$photoPath = $utilisateur->photo_profil ?? null;
$photoUrl = ($photoPath && Storage::disk('public')->exists($photoPath))
    ? asset('storage/' . $photoPath)
    : null;
$initiales = $utilisateur
    ? strtoupper(substr($utilisateur->prenom ?? '', 0, 1) . substr($utilisateur->nom ?? '', 0, 1))
    : '?';
$statut = old('statut', $adminAccount->statut ?? 'actif');
@endphp

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-user-edit me-2"></i>
        Modifier le compte
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0 gap-2">
        <a href="{{ route('admin.accounts.show', $adminAccount) }}" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-eye me-1"></i>
            Voir
        </a>
        <a href="{{ route('admin.accounts.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Retour
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

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <strong>Veuillez corriger les erreurs du formulaire.</strong>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(!$utilisateur)
<div class="alert alert-danger">
    Le compte administrateur n’a pas été trouvé.
</div>
@else
<form action="{{ route('admin.accounts.update', $adminAccount->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Profil</h5>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <img id="photo-preview"
                             src="{{ $photoUrl }}"
                             alt="Photo"
                             class="rounded-circle {{ $photoUrl ? '' : 'd-none' }}"
                             style="width: 140px; height: 140px; object-fit: cover;">
                        <div id="photo-fallback"
                             class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white mx-auto {{ $photoUrl ? 'd-none' : '' }}"
                             style="width: 140px; height: 140px; font-size: 2.4rem;">
                            {{ $initiales }}
                        </div>
                    </div>
                    <h5 class="mb-1">{{ $utilisateur->nom }} {{ $utilisateur->prenom }}</h5>
                    <p class="text-muted mb-2">{{ $adminAccount->poste }}</p>
                    <span class="badge bg-{{ $statut === 'actif' ? 'success' : ($statut === 'inactif' ? 'danger' : 'warning') }} mb-3">
                        {{ ucfirst($statut) }}
                    </span>

                    <div class="text-start">
                        <label for="photo_profil" class="form-label">Nouvelle photo</label>
                        <input type="file" class="form-control @error('photo_profil') is-invalid @enderror"
                               id="photo_profil" name="photo_profil" accept="image/*">
                        <div class="form-text">JPEG, PNG, JPG ou GIF — 2 Mo max.</div>
                        @error('photo_profil')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="card-footer bg-white d-grid gap-2">
                    <a href="{{ route('admin.accounts.permissions', $adminAccount) }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-shield-alt me-1"></i>
                        Permissions
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user me-2 text-primary"></i>
                        Informations personnelles
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nom') is-invalid @enderror"
                                   id="nom" name="nom" value="{{ old('nom', $utilisateur->nom) }}" required>
                            @error('nom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="prenom" class="form-label">Prénom <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('prenom') is-invalid @enderror"
                                   id="prenom" name="prenom" value="{{ old('prenom', $utilisateur->prenom) }}" required>
                            @error('prenom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email" value="{{ old('email', $utilisateur->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="telephone" class="form-label">Téléphone</label>
                            <input type="text" class="form-control @error('telephone') is-invalid @enderror"
                                   id="telephone" name="telephone" value="{{ old('telephone', $utilisateur->telephone) }}">
                            @error('telephone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="sexe" class="form-label">Sexe</label>
                            <select class="form-select @error('sexe') is-invalid @enderror" id="sexe" name="sexe">
                                <option value="">Sélectionner...</option>
                                <option value="M" {{ old('sexe', $utilisateur->sexe) == 'M' ? 'selected' : '' }}>Masculin</option>
                                <option value="F" {{ old('sexe', $utilisateur->sexe) == 'F' ? 'selected' : '' }}>Féminin</option>
                            </select>
                            @error('sexe')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="date_naissance" class="form-label">Date de naissance</label>
                            <input type="date" class="form-control @error('date_naissance') is-invalid @enderror"
                                   id="date_naissance" name="date_naissance"
                                   value="{{ old('date_naissance', optional($utilisateur->date_naissance)->format('Y-m-d')) }}">
                            @error('date_naissance')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label for="adresse" class="form-label">Adresse</label>
                            <textarea class="form-control @error('adresse') is-invalid @enderror"
                                      id="adresse" name="adresse" rows="2">{{ old('adresse', $utilisateur->adresse) }}</textarea>
                            @error('adresse')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-briefcase me-2 text-primary"></i>
                        Informations professionnelles
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="poste" class="form-label">Poste <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('poste') is-invalid @enderror"
                                   id="poste" name="poste" value="{{ old('poste', $adminAccount->poste) }}" required>
                            @error('poste')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="departement" class="form-label">Département</label>
                            <input type="text" class="form-control @error('departement') is-invalid @enderror"
                                   id="departement" name="departement" value="{{ old('departement', $adminAccount->departement) }}">
                            @error('departement')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="date_embauche" class="form-label">Date d'embauche <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('date_embauche') is-invalid @enderror"
                                   id="date_embauche" name="date_embauche"
                                   value="{{ old('date_embauche', optional($adminAccount->date_embauche)->format('Y-m-d')) }}" required>
                            @error('date_embauche')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="salaire" class="form-label">Salaire (GNF)</label>
                            <input type="number" class="form-control @error('salaire') is-invalid @enderror"
                                   id="salaire" name="salaire" value="{{ old('salaire', $adminAccount->salaire) }}" min="0" step="0.01">
                            @error('salaire')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="statut" class="form-label">Statut <span class="text-danger">*</span></label>
                            <select class="form-select @error('statut') is-invalid @enderror" id="statut" name="statut" required>
                                <option value="actif" {{ $statut == 'actif' ? 'selected' : '' }}>Actif</option>
                                <option value="inactif" {{ $statut == 'inactif' ? 'selected' : '' }}>Inactif</option>
                                <option value="suspendu" {{ $statut == 'suspendu' ? 'selected' : '' }}>Suspendu</option>
                            </select>
                            @error('statut')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-sticky-note me-2 text-primary"></i>
                        Observations
                    </h5>
                </div>
                <div class="card-body">
                    <textarea class="form-control @error('observations') is-invalid @enderror"
                              id="observations" name="observations" rows="3"
                              placeholder="Notes internes…">{{ old('observations', $adminAccount->observations) }}</textarea>
                    @error('observations')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.accounts.show', $adminAccount) }}" class="btn btn-outline-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>
                    Enregistrer
                </button>
            </div>
        </div>
    </div>
</form>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('photo_profil');
    const preview = document.getElementById('photo-preview');
    const fallback = document.getElementById('photo-fallback');
    if (!input || !preview) return;

    input.addEventListener('change', function () {
        const file = this.files && this.files[0];
        if (!file) return;
        const url = URL.createObjectURL(file);
        preview.src = url;
        preview.classList.remove('d-none');
        if (fallback) fallback.classList.add('d-none');
    });
});
</script>
@endpush
