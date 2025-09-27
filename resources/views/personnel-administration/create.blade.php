@extends('layouts.app')

@section('title', 'Créer un Personnel d\'Administration')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-user-plus me-2"></i>Créer un Personnel d'Administration
                    </h3>
                </div>
                
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('personnel-administration.store') }}" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Section Photo de Profil -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary border-bottom pb-2">
                                    <i class="fas fa-camera me-2"></i>Photo de Profil
                                </h5>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    <div id="photo-preview" class="mb-3" style="display: none;">
                                        <img id="preview-img" src="" alt="Aperçu" 
                                             class="img-thumbnail rounded-circle" 
                                             style="width: 150px; height: 150px; object-fit: cover;">
                                    </div>
                                    <div id="photo-placeholder" class="border rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" 
                                         style="width: 150px; height: 150px; background: #f8f9fa;">
                                        <i class="fas fa-user fa-3x text-muted"></i>
                                    </div>
                                    <input type="file" name="photo_profil" id="photo_profil" 
                                           class="form-control" accept="image/*" onchange="previewPhoto(this)">
                                    <small class="text-muted">Formats acceptés: JPG, PNG, GIF (max 2MB)</small>
                                </div>
                            </div>
                        </div>

                        <!-- Section Informations Personnelles -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary border-bottom pb-2">
                                    <i class="fas fa-user me-2"></i>Informations Personnelles
                                </h5>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('nom') is-invalid @enderror" 
                                           id="nom" name="nom" value="{{ old('nom') }}" required>
                                    @error('nom')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="prenom" class="form-label">Prénom <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('prenom') is-invalid @enderror" 
                                           id="prenom" name="prenom" value="{{ old('prenom') }}" required>
                                    @error('prenom')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email') }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="telephone" class="form-label">Téléphone</label>
                                    <input type="text" class="form-control @error('telephone') is-invalid @enderror" 
                                           id="telephone" name="telephone" value="{{ old('telephone') }}">
                                    @error('telephone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="sexe" class="form-label">Sexe <span class="text-danger">*</span></label>
                                    <select class="form-select @error('sexe') is-invalid @enderror" id="sexe" name="sexe" required>
                                        <option value="">Sélectionner</option>
                                        <option value="M" {{ old('sexe') == 'M' ? 'selected' : '' }}>Masculin</option>
                                        <option value="F" {{ old('sexe') == 'F' ? 'selected' : '' }}>Féminin</option>
                                    </select>
                                    @error('sexe')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="date_naissance" class="form-label">Date de naissance <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('date_naissance') is-invalid @enderror" 
                                           id="date_naissance" name="date_naissance" value="{{ old('date_naissance') }}" required>
                                    @error('date_naissance')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="adresse" class="form-label">Adresse</label>
                                    <textarea class="form-control @error('adresse') is-invalid @enderror" 
                                              id="adresse" name="adresse" rows="2">{{ old('adresse') }}</textarea>
                                    @error('adresse')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Section Informations Professionnelles -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary border-bottom pb-2">
                                    <i class="fas fa-briefcase me-2"></i>Informations Professionnelles
                                </h5>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="poste" class="form-label">Poste <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('poste') is-invalid @enderror" 
                                           id="poste" name="poste" value="{{ old('poste') }}" 
                                           placeholder="Ex: Secrétaire, Comptable, Surveillant" required>
                                    @error('poste')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="departement" class="form-label">Département</label>
                                    <input type="text" class="form-control @error('departement') is-invalid @enderror" 
                                           id="departement" name="departement" value="{{ old('departement') }}" 
                                           placeholder="Ex: Administration, Comptabilité">
                                    @error('departement')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="date_embauche" class="form-label">Date d'embauche <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('date_embauche') is-invalid @enderror" 
                                           id="date_embauche" name="date_embauche" value="{{ old('date_embauche') }}" required>
                                    @error('date_embauche')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="salaire" class="form-label">Salaire (GNF)</label>
                                    <input type="number" class="form-control @error('salaire') is-invalid @enderror" 
                                           id="salaire" name="salaire" value="{{ old('salaire') }}" 
                                           min="0" step="1000" placeholder="Ex: 500000">
                                    @error('salaire')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="observations" class="form-label">Observations</label>
                                    <textarea class="form-control @error('observations') is-invalid @enderror" 
                                              id="observations" name="observations" rows="3" 
                                              placeholder="Notes ou observations sur ce personnel">{{ old('observations') }}</textarea>
                                    @error('observations')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Section Permissions -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary border-bottom pb-2">
                                    <i class="fas fa-key me-2"></i>Permissions
                                </h5>
                                <p class="text-muted">Sélectionnez les permissions à attribuer à ce personnel d'administration.</p>
                            </div>
                            <div class="col-12">
                                <div class="row">
                                    @foreach($permissions as $key => $label)
                                        <div class="col-md-6 col-lg-4 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="permissions[]" value="{{ $key }}" 
                                                       id="permission_{{ $key }}"
                                                       {{ in_array($key, old('permissions', [])) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="permission_{{ $key }}">
                                                    {{ $label }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="mt-3">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAllPermissions()">
                                        <i class="fas fa-check-double me-1"></i>Tout sélectionner
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAllPermissions()">
                                        <i class="fas fa-times me-1"></i>Tout désélectionner
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Boutons d'action -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('personnel-administration.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times me-1"></i>Annuler
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>Créer le Personnel
                                    </button>
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
function previewPhoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-img').src = e.target.result;
            document.getElementById('photo-preview').style.display = 'block';
            document.getElementById('photo-placeholder').style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function selectAllPermissions() {
    const checkboxes = document.querySelectorAll('input[name="permissions[]"]');
    checkboxes.forEach(checkbox => checkbox.checked = true);
}

function deselectAllPermissions() {
    const checkboxes = document.querySelectorAll('input[name="permissions[]"]');
    checkboxes.forEach(checkbox => checkbox.checked = false);
}
</script>
@endsection









