@extends('layouts.app')

@section('title', 'Ajouter Enseignant')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center pt-3 pb-2 mb-3 border-bottom gap-2">
    <h1 class="h2 mb-0">
        <i class="fas fa-user-plus me-2"></i>
        Ajouter un Enseignant
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('enseignants.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            <span class="d-none d-sm-inline">Retour</span>
        </a>
    </div>
</div>

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <strong>Veuillez remplir tous les champs obligatoires</strong>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<form method="POST" action="{{ route('enseignants.store') }}" enctype="multipart/form-data" class="needs-validation" novalidate>
    @csrf
    
    <div class="row g-3">
        <!-- Colonne de gauche : Photo + Informations enseignant -->
        <div class="col-12 col-md-8">
            
            <!-- Section Photo de l'enseignant -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-camera me-2"></i>Photo de l'Enseignant
                    </h5>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <div class="photo-preview-container" style="width: 150px; height: 150px; margin: 0 auto;">
                            <div id="photoPreview" class="bg-light border rounded-circle d-flex align-items-center justify-content-center" 
                                 style="width: 150px; height: 150px;">
                                <i class="fas fa-user fa-3x text-muted"></i>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <input type="file" class="form-control @error('photo_profil') is-invalid @enderror" 
                               id="photo_profil" name="photo_profil" accept="image/*">
                        <small class="text-muted">Formats acceptés: JPG, PNG, GIF. Taille max: 2MB</small>
                        @error('photo_profil')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Section Informations personnelles -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user me-2"></i>Informations Personnelles
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nom" class="form-label">Nom *</label>
                                <input type="text" class="form-control @error('nom') is-invalid @enderror" 
                                       id="nom" name="nom" value="{{ old('nom') }}" required>
                                @error('nom')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="prenom" class="form-label">Prénom *</label>
                                <input type="text" class="form-control @error('prenom') is-invalid @enderror" 
                                       id="prenom" name="prenom" value="{{ old('prenom') }}" required>
                                @error('prenom')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email') }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="telephone" class="form-label">Téléphone *</label>
                                <input type="tel" class="form-control @error('telephone') is-invalid @enderror" 
                                       id="telephone" name="telephone" value="{{ old('telephone') }}" required>
                                @error('telephone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="date_naissance" class="form-label">Date de Naissance *</label>
                                <input type="date" class="form-control @error('date_naissance') is-invalid @enderror" 
                                       id="date_naissance" name="date_naissance" value="{{ old('date_naissance') }}" required>
                                @error('date_naissance')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="lieu_naissance" class="form-label">Lieu de Naissance *</label>
                                <input type="text" class="form-control @error('lieu_naissance') is-invalid @enderror" 
                                       id="lieu_naissance" name="lieu_naissance" value="{{ old('lieu_naissance') }}" required>
                                @error('lieu_naissance')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="sexe" class="form-label">Sexe *</label>
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
                    </div>
                    
                    <div class="mb-3">
                        <label for="adresse" class="form-label">Adresse *</label>
                        <textarea class="form-control @error('adresse') is-invalid @enderror" 
                                  id="adresse" name="adresse" rows="2" required>{{ old('adresse') }}</textarea>
                        @error('adresse')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Section Informations professionnelles -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-briefcase me-2"></i>Informations Professionnelles
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="numero_employe" class="form-label">Numéro d'Employé *</label>
                                <input type="text" class="form-control @error('numero_employe') is-invalid @enderror" 
                                       id="numero_employe" name="numero_employe" value="{{ old('numero_employe') }}" required>
                                @error('numero_employe')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="specialite" class="form-label">Spécialité *</label>
                                <input type="text" class="form-control @error('specialite') is-invalid @enderror" 
                                       id="specialite" name="specialite" value="{{ old('specialite') }}" required>
                                @error('specialite')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="date_embauche" class="form-label">Date d'Embauche *</label>
                                <input type="date" class="form-control @error('date_embauche') is-invalid @enderror" 
                                       id="date_embauche" name="date_embauche" value="{{ old('date_embauche') }}" required>
                                @error('date_embauche')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="statut" class="form-label">Statut *</label>
                                <select class="form-select @error('statut') is-invalid @enderror" id="statut" name="statut" required>
                                    <option value="">Sélectionner</option>
                                    <option value="titulaire" {{ old('statut') == 'titulaire' ? 'selected' : '' }}>Titulaire</option>
                                    <option value="contractuel" {{ old('statut') == 'contractuel' ? 'selected' : '' }}>Contractuel</option>
                                    <option value="vacataire" {{ old('statut') == 'vacataire' ? 'selected' : '' }}>Vacataire</option>
                                </select>
                                @error('statut')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>

        <!-- Colonne de droite : Matières enseignées -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-book me-2"></i>Matières Enseignées
                    </h5>
                </div>
                <div class="card-body">
                    @if($matieres->count() > 0)
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="select_all_matieres">
                                <label class="form-check-label fw-bold" for="select_all_matieres">
                                    Sélectionner toutes les matières
                                </label>
                            </div>
                        </div>
                        <hr>
                        @foreach($matieres as $matiere)
                        <div class="form-check mb-2">
                            <input class="form-check-input matiere-checkbox" type="checkbox" id="matiere_{{ $matiere->id }}" 
                                   name="matieres[]" value="{{ $matiere->id }}"
                                   {{ in_array($matiere->id, old('matieres', [])) ? 'checked' : '' }}>
                            <label class="form-check-label" for="matiere_{{ $matiere->id }}">
                                <strong>{{ $matiere->nom }}</strong>
                                <br>
                                <small class="text-muted">Coefficient: {{ $matiere->coefficient }}</small>
                            </label>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted">
                            <i class="fas fa-book fa-3x mb-3"></i>
                            <p>Aucune matière disponible.</p>
                            <p class="small">Veuillez d'abord créer des matières.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Boutons d'action -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <button type="reset" class="btn btn-outline-secondary btn-lg">
                            <i class="fas fa-undo me-2"></i>
                            Réinitialiser
                        </button>
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-save me-2"></i>
                            Enregistrer l'enseignant
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<div class="alert alert-info mt-3">
    <i class="fas fa-info-circle me-2"></i>
    <strong>Note:</strong> Le mot de passe par défaut sera "password123". L'enseignant pourra le modifier lors de sa première connexion.
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Prévisualisation de la photo
    const photoInput = document.getElementById('photo_profil');
    const photoPreview = document.getElementById('photoPreview');
    
    if (photoInput) {
        photoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    if (photoPreview.tagName === 'IMG') {
                        photoPreview.src = e.target.result;
                    } else {
                        // Remplacer la div par une image
                        const img = document.createElement('img');
                        img.id = 'photoPreview';
                        img.src = e.target.result;
                        img.alt = 'Photo de l\'enseignant';
                        img.className = 'img-thumbnail rounded-circle';
                        img.style.cssText = 'width: 150px; height: 150px; object-fit: cover;';
                        photoPreview.parentNode.replaceChild(img, photoPreview);
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Sélection de toutes les matières
    const selectAllCheckbox = document.getElementById('select_all_matieres');
    const matiereCheckboxes = document.querySelectorAll('.matiere-checkbox');
    
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            matiereCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
        
        // Vérifier si toutes les matières sont sélectionnées
        function updateSelectAllState() {
            const checkedCount = document.querySelectorAll('.matiere-checkbox:checked').length;
            const totalCount = matiereCheckboxes.length;
            
            if (checkedCount === 0) {
                selectAllCheckbox.indeterminate = false;
                selectAllCheckbox.checked = false;
            } else if (checkedCount === totalCount) {
                selectAllCheckbox.indeterminate = false;
                selectAllCheckbox.checked = true;
            } else {
                selectAllCheckbox.indeterminate = true;
            }
        }
        
        // Mettre à jour l'état initial
        updateSelectAllState();
        
        // Écouter les changements sur les checkboxes individuelles
        matiereCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateSelectAllState);
        });
    }
    
    // Validation Bootstrap
    const form = document.querySelector('.needs-validation');
    if (form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    }
});
</script>
@endsection
