@extends('layouts.app')

@section('title', 'Créer un compte administrateur')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-user-plus me-2"></i>
        Créer un compte
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
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
    <ul class="mb-0 mt-2">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<form action="{{ route('admin.accounts.store') }}" method="POST" enctype="multipart/form-data" id="createAccountForm">
    @csrf

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Photo</h5>
                </div>
                <div class="card-body text-center">
                    <img id="photo-preview" src="" alt="Aperçu" class="rounded-circle d-none mb-3"
                         style="width: 140px; height: 140px; object-fit: cover;">
                    <div id="photo-fallback"
                         class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white mx-auto mb-3"
                         style="width: 140px; height: 140px; font-size: 2.2rem;">
                        <i class="fas fa-user"></i>
                    </div>
                    <label for="photo_profil" class="form-label text-start w-100">Photo de profil</label>
                    <input type="file" class="form-control @error('photo_profil') is-invalid @enderror"
                           id="photo_profil" name="photo_profil" accept="image/*">
                    <div class="form-text text-start">JPEG, PNG, JPG ou GIF — 2 Mo max.</div>
                    @error('photo_profil')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-lock me-2 text-primary"></i>
                        Sécurité
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="password" class="form-label">Mot de passe <span class="text-danger">*</span></label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                               id="password" name="password" required minlength="8" autocomplete="new-password">
                        <div class="form-text">Au moins 8 caractères.</div>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-0">
                        <label for="password_confirmation" class="form-label">Confirmation <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password_confirmation"
                               name="password_confirmation" required minlength="8" autocomplete="new-password">
                    </div>
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
                                   id="nom" name="nom" value="{{ old('nom') }}" required>
                            @error('nom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="prenom" class="form-label">Prénom <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('prenom') is-invalid @enderror"
                                   id="prenom" name="prenom" value="{{ old('prenom') }}" required>
                            @error('prenom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email" value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="telephone" class="form-label">Téléphone</label>
                            <input type="text" class="form-control @error('telephone') is-invalid @enderror"
                                   id="telephone" name="telephone" value="{{ old('telephone') }}">
                            @error('telephone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="sexe" class="form-label">Sexe <span class="text-danger">*</span></label>
                            <select class="form-select @error('sexe') is-invalid @enderror" id="sexe" name="sexe" required>
                                <option value="">Sélectionner...</option>
                                <option value="M" {{ old('sexe') == 'M' ? 'selected' : '' }}>Masculin</option>
                                <option value="F" {{ old('sexe') == 'F' ? 'selected' : '' }}>Féminin</option>
                            </select>
                            @error('sexe')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="date_naissance" class="form-label">Date de naissance <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('date_naissance') is-invalid @enderror"
                                   id="date_naissance" name="date_naissance" value="{{ old('date_naissance') }}" required>
                            @error('date_naissance')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label for="adresse" class="form-label">Adresse</label>
                            <textarea class="form-control @error('adresse') is-invalid @enderror"
                                      id="adresse" name="adresse" rows="2">{{ old('adresse') }}</textarea>
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
                                   id="poste" name="poste" value="{{ old('poste') }}" required>
                            @error('poste')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="departement" class="form-label">Département</label>
                            <input type="text" class="form-control @error('departement') is-invalid @enderror"
                                   id="departement" name="departement" value="{{ old('departement') }}">
                            @error('departement')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="date_embauche" class="form-label">Date d'embauche <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('date_embauche') is-invalid @enderror"
                                   id="date_embauche" name="date_embauche" value="{{ old('date_embauche') }}" required>
                            @error('date_embauche')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="salaire" class="form-label">Salaire (GNF)</label>
                            <input type="number" class="form-control @error('salaire') is-invalid @enderror"
                                   id="salaire" name="salaire" value="{{ old('salaire') }}" min="0" step="0.01">
                            @error('salaire')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0">
                        <i class="fas fa-shield-alt me-2 text-primary"></i>
                        Permissions
                    </h5>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="select-all">Tout sélectionner</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="deselect-all">Tout désélectionner</button>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Cochez les accès du compte. Cliquez sur le titre d’un groupe pour tout cocher ou décocher.
                    </p>
                    <div class="row">
                        @foreach($permissions as $category => $perms)
                            @if(count($perms) > 0)
                            <div class="col-md-6 mb-3">
                                <div class="border rounded h-100 permission-group">
                                    <div class="px-3 py-2 bg-light border-bottom d-flex justify-content-between align-items-center group-toggle" role="button">
                                        <strong>{{ $category }}</strong>
                                        <span class="badge bg-secondary group-count">0</span>
                                    </div>
                                    <div class="p-3">
                                        @foreach($perms as $key => $label)
                                        <div class="form-check mb-2">
                                            <input class="form-check-input permission-checkbox" type="checkbox"
                                                   name="permissions[]" value="{{ $key }}"
                                                   id="permission_{{ $key }}"
                                                   {{ in_array($key, old('permissions', []), true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="permission_{{ $key }}">
                                                {{ $label }}
                                            </label>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>
                    @error('permissions')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
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
                              placeholder="Notes internes…">{{ old('observations') }}</textarea>
                    @error('observations')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.accounts.index') }}" class="btn btn-outline-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>
                    Créer le compte
                </button>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('photo_profil');
    const preview = document.getElementById('photo-preview');
    const fallback = document.getElementById('photo-fallback');
    if (input && preview) {
        input.addEventListener('change', function () {
            const file = this.files && this.files[0];
            if (!file) return;
            preview.src = URL.createObjectURL(file);
            preview.classList.remove('d-none');
            if (fallback) fallback.classList.add('d-none');
        });
    }

    const boxes = () => document.querySelectorAll('.permission-checkbox');

    function updateCounts() {
        document.querySelectorAll('.permission-group').forEach(function (group) {
            const groupBoxes = group.querySelectorAll('.permission-checkbox');
            const checked = group.querySelectorAll('.permission-checkbox:checked').length;
            const badge = group.querySelector('.group-count');
            if (badge) badge.textContent = checked + '/' + groupBoxes.length;
        });
    }

    document.getElementById('select-all')?.addEventListener('click', function () {
        boxes().forEach(cb => { cb.checked = true; });
        updateCounts();
    });

    document.getElementById('deselect-all')?.addEventListener('click', function () {
        boxes().forEach(cb => { cb.checked = false; });
        updateCounts();
    });

    document.querySelectorAll('.group-toggle').forEach(function (header) {
        header.addEventListener('click', function () {
            const group = header.closest('.permission-group');
            const groupBoxes = group.querySelectorAll('.permission-checkbox');
            const allChecked = Array.from(groupBoxes).every(cb => cb.checked);
            groupBoxes.forEach(cb => { cb.checked = !allChecked; });
            updateCounts();
        });
    });

    document.querySelectorAll('.permission-checkbox').forEach(function (cb) {
        cb.addEventListener('change', updateCounts);
    });

    updateCounts();
});
</script>
@endpush
