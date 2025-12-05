@extends('layouts.app')

@section('title', 'Modifier Élève')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-user-edit me-2"></i>
        Modifier un Élève
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('eleves.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Retour
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


<form method="POST" action="{{ route('eleves.update-simple', $eleve->id) }}" enctype="multipart/form-data" class="needs-validation" novalidate>
    @csrf
    @method('PUT')

    <div class="row">
        <!-- Colonne de gauche : Photo + Informations élève -->
        <div class="col-md-8">
            
            <!-- Section Photo de l'élève -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-camera me-2"></i>Photo de l'Élève
                    </h5>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <div class="photo-preview-container" style="width: 150px; height: 150px; margin: 0 auto;">
                            <div id="photoPreview">
                                <x-profile-image 
                                    :photo-path="$eleve->utilisateur->photo_profil ?? null"
                                    :name="($eleve->utilisateur->prenom ?? '') . ' ' . ($eleve->utilisateur->nom ?? '')"
                                    size="lg" 
                                    class="img-thumbnail" />
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <input type="file" class="form-control @error('photo_profil') is-invalid @enderror" 
                               id="photo_profil" name="photo_profil" accept="image/*" onchange="handleImageUpload(this)">
                        <small class="text-muted">Formats acceptés: JPG, PNG, GIF. Taille max: 2MB</small>
                        @error('photo_profil')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    @if($eleve->utilisateur && $eleve->utilisateur->photo_profil)
                        <div class="mb-2">
                            <a href="{{ route('eleves.delete-photo', $eleve->id) }}" 
                               class="btn btn-outline-danger btn-sm"
                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette photo ?')">
                                <i class="fas fa-trash me-1"></i>Supprimer la photo
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Section Informations de l'élève -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user me-2"></i>Informations de l'Élève
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="numero_etudiant" class="form-label">
                                <i class="fas fa-id-card me-1"></i>Matricule *
                            </label>
                            <input type="text" class="form-control @error('numero_etudiant') is-invalid @enderror" 
                                   id="numero_etudiant" name="numero_etudiant" 
                                   value="{{ old('numero_etudiant', $eleve->numero_etudiant) }}" required>
                            @error('numero_etudiant')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="prenom" class="form-label">
                                <i class="fas fa-user me-1"></i>Prénom *
                            </label>
                            <input type="text" class="form-control @error('prenom') is-invalid @enderror" 
                                   id="prenom" name="prenom" 
                                   value="{{ old('prenom', $eleve->utilisateur->prenom ?? '') }}" required>
                            @error('prenom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="nom" class="form-label">
                                <i class="fas fa-user me-1"></i>Nom *
                            </label>
                            <input type="text" class="form-control @error('nom') is-invalid @enderror" 
                                   id="nom" name="nom" 
                                   value="{{ old('nom', $eleve->utilisateur->nom ?? '') }}" required>
                            @error('nom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="sexe" class="form-label">
                                <i class="fas fa-venus-mars me-1"></i>Sexe *
                            </label>
                            <select class="form-select @error('sexe') is-invalid @enderror" id="sexe" name="sexe" required>
                                <option value="">Sélectionner</option>
                                <option value="M" {{ old('sexe', $eleve->utilisateur->sexe ?? '') == 'M' ? 'selected' : '' }}>Masculin</option>
                                <option value="F" {{ old('sexe', $eleve->utilisateur->sexe ?? '') == 'F' ? 'selected' : '' }}>Féminin</option>
                            </select>
                            @error('sexe')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="date_naissance" class="form-label">
                                <i class="fas fa-calendar me-1"></i>Date de Naissance *
                            </label>
                            @php
                                $dateNaissance = '';
                                if (old('date_naissance')) {
                                    $dateNaissance = old('date_naissance');
                                } elseif (isset($eleve->utilisateur->date_naissance) && $eleve->utilisateur->date_naissance) {
                                    $dateNaissance = $eleve->utilisateur->date_naissance->format('Y-m-d');
                                }
                            @endphp
                            <input type="date" class="form-control @error('date_naissance') is-invalid @enderror" 
                                   id="date_naissance" name="date_naissance" 
                                   value="{{ $dateNaissance }}" required>
                            @error('date_naissance')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="lieu_naissance" class="form-label">
                                <i class="fas fa-map-marker-alt me-1"></i>Lieu de Naissance *
                            </label>
                            <input type="text" class="form-control @error('lieu_naissance') is-invalid @enderror" 
                                   id="lieu_naissance" name="lieu_naissance" 
                                   value="{{ old('lieu_naissance', $eleve->utilisateur->lieu_naissance ?? '') }}" required>
                            @error('lieu_naissance')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="telephone" class="form-label">
                                <i class="fas fa-phone me-1"></i>Téléphone
                            </label>
                            <input type="tel" class="form-control @error('telephone') is-invalid @enderror" 
                                   id="telephone" name="telephone" 
                                   value="{{ old('telephone', $eleve->utilisateur->telephone ?? '') }}">
                            @error('telephone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="adresse" class="form-label">
                                <i class="fas fa-home me-1"></i>Adresse
                            </label>
                            <textarea class="form-control @error('adresse') is-invalid @enderror" 
                                      id="adresse" name="adresse" rows="2">{{ old('adresse', $eleve->utilisateur->adresse ?? '') }}</textarea>
                            @error('adresse')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="situation_matrimoniale" class="form-label">
                                <i class="fas fa-heart me-1"></i>Situation Matrimoniale
                            </label>
                            <select class="form-select @error('situation_matrimoniale') is-invalid @enderror" 
                                    id="situation_matrimoniale" name="situation_matrimoniale">
                                <option value="">Sélectionner</option>
                                <option value="celibataire" {{ old('situation_matrimoniale', $eleve->situation_matrimoniale ?? '') == 'celibataire' ? 'selected' : '' }}>Célibataire</option>
                                <option value="marie" {{ old('situation_matrimoniale', $eleve->situation_matrimoniale ?? '') == 'marie' ? 'selected' : '' }}>Marié(e)</option>
                                <option value="divorce" {{ old('situation_matrimoniale', $eleve->situation_matrimoniale ?? '') == 'divorce' ? 'selected' : '' }}>Divorcé(e)</option>
                                <option value="veuf" {{ old('situation_matrimoniale', $eleve->situation_matrimoniale ?? '') == 'veuf' ? 'selected' : '' }}>Veuf/Veuve</option>
                            </select>
                            @error('situation_matrimoniale')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope me-1"></i>Email
                            </label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" 
                                   value="{{ old('email', $eleve->utilisateur->email ?? '') }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Colonne de droite : Inscription -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-graduation-cap me-2"></i>Inscription
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="date_inscription" class="form-label">
                            <i class="fas fa-calendar-plus me-1"></i>Date d'Inscription *
                        </label>
                        @php
                            $dateInscription = '';
                            if (old('date_inscription')) {
                                $dateInscription = old('date_inscription');
                            } elseif (isset($eleve->date_inscription) && $eleve->date_inscription) {
                                $dateInscription = $eleve->date_inscription->format('Y-m-d');
                            }
                        @endphp
                        <input type="date" class="form-control @error('date_inscription') is-invalid @enderror" 
                               id="date_inscription" name="date_inscription" 
                               value="{{ $dateInscription }}" required>
                        @error('date_inscription')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="type_inscription" class="form-label">
                            <i class="fas fa-user-plus me-1"></i>Type d'Inscription *
                        </label>
                        <select class="form-select @error('type_inscription') is-invalid @enderror" 
                                id="type_inscription" name="type_inscription" required>
                            <option value="">Sélectionner</option>
                            <option value="nouvelle" {{ old('type_inscription', $eleve->type_inscription ?? '') == 'nouvelle' ? 'selected' : '' }}>Nouvelle inscription</option>
                            <option value="reinscription" {{ old('type_inscription', $eleve->type_inscription ?? '') == 'reinscription' ? 'selected' : '' }}>Réinscription</option>
                            <option value="transfert" {{ old('type_inscription', $eleve->type_inscription ?? '') == 'transfert' ? 'selected' : '' }}>Transfert</option>
                        </select>
                        @error('type_inscription')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="ecole_origine" class="form-label">
                            <i class="fas fa-school me-1"></i>École d'Origine
                        </label>
                        <input type="text" class="form-control @error('ecole_origine') is-invalid @enderror" 
                               id="ecole_origine" name="ecole_origine" 
                               value="{{ old('ecole_origine', $eleve->ecole_origine ?? '') }}">
                        @error('ecole_origine')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="statut" class="form-label">
                            <i class="fas fa-info-circle me-1"></i>État d'Activité *
                        </label>
                        <select class="form-select @error('statut') is-invalid @enderror" id="statut" name="statut" required>
                            <option value="">Sélectionner</option>
                            <option value="actif" {{ old('statut', $eleve->statut ?? '') == 'actif' ? 'selected' : '' }}>Actif</option>
                            <option value="inactif" {{ old('statut', $eleve->statut ?? '') == 'inactif' ? 'selected' : '' }}>Inactif</option>
                            <option value="suspendu" {{ old('statut', $eleve->statut ?? '') == 'suspendu' ? 'selected' : '' }}>Suspendu</option>
                            <option value="diplome" {{ old('statut', $eleve->statut ?? '') == 'diplome' ? 'selected' : '' }}>Diplômé</option>
                            <option value="abandonne" {{ old('statut', $eleve->statut ?? '') == 'abandonne' ? 'selected' : '' }}>Abandonné</option>
                        </select>
                        @error('statut')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="classe_id" class="form-label">
                            <i class="fas fa-chalkboard me-1"></i>Classe *
                        </label>
                        <select class="form-select @error('classe_id') is-invalid @enderror" id="classe_id" name="classe_id" required>
                            <option value="">Sélectionner une classe</option>
                            @foreach($classes as $classe)
                            <option value="{{ $classe->id }}" {{ old('classe_id', $eleve->classe_id) == $classe->id ? 'selected' : '' }}>
                                {{ $classe->nom }} ({{ $classe->niveau }} {{ $classe->section }})
                            </option>
                            @endforeach
                        </select>
                        @error('classe_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="annee_scolaire_id" class="form-label">
                            <i class="fas fa-calendar-alt me-1"></i>Année Scolaire *
                        </label>
                        <select class="form-select @error('annee_scolaire_id') is-invalid @enderror" 
                                id="annee_scolaire_id" name="annee_scolaire_id" required>
                            <option value="">Sélectionner une année</option>
                            @foreach($anneesScolarites as $annee)
                            <option value="{{ $annee->id }}" {{ old('annee_scolaire_id', $eleve->annee_scolaire_id ?? '') == $annee->id ? 'selected' : '' }}>
                                {{ $annee->nom }} ({{ $annee->date_debut->format('Y') }}-{{ $annee->date_fin->format('Y') }})
                                @if($annee->active) <span class="badge bg-success ms-1">Active</span> @endif
                            </option>
                            @endforeach
                        </select>
                        @error('annee_scolaire_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input type="hidden" name="exempte_frais" value="0">
                            <input class="form-check-input @error('exempte_frais') is-invalid @enderror" 
                                   type="checkbox" name="exempte_frais" value="1" id="exempte_frais"
                                   {{ old('exempte_frais', $eleve->exempte_frais ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="exempte_frais">
                                <i class="fas fa-hand-holding-usd me-1"></i>Exempté des frais de scolarité
                            </label>
                            @error('exempte_frais')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input type="hidden" name="paiement_annuel" value="0">
                            <input class="form-check-input @error('paiement_annuel') is-invalid @enderror" 
                                   type="checkbox" name="paiement_annuel" value="1" id="paiement_annuel"
                                   {{ old('paiement_annuel', $eleve->paiement_annuel ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="paiement_annuel">
                                <i class="fas fa-calendar-check me-1"></i>Paiement annuel
                            </label>
                            @error('paiement_annuel')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Boutons d'action -->
    <div class="d-grid gap-2 d-md-flex justify-content-md-end mb-4">
        <a href="{{ route('eleves.index') }}" class="btn btn-outline-secondary me-md-2">
            <i class="fas fa-times me-1"></i>Annuler
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i>Enregistrer les modifications
        </button>
    </div>
</form>

@push('scripts')
<script>
function handleFormSubmit(button) {
    console.log('🔍 Début de la soumission du formulaire élève');
    
    // Désactiver le bouton pour éviter les double-clics
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Enregistrement...';
    
    // Récupérer le formulaire
    const form = button.closest('form');
    if (!form) {
        console.error('❌ Formulaire non trouvé');
        return false;
    }
    
    console.log('📋 Formulaire trouvé:', form);
    console.log('📋 Action:', form.action);
    console.log('📋 Méthode:', form.method);
    
    // Vérifier les champs requis
    const requiredFields = form.querySelectorAll('[required]');
    let missingFields = [];
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            missingFields.push(field.name || field.id);
        }
    });
    
    if (missingFields.length > 0) {
        console.error('❌ Champs requis manquants:', missingFields);
        alert('Veuillez remplir tous les champs requis: ' + missingFields.join(', '));
        button.disabled = false;
        button.innerHTML = '<i class="fas fa-save me-1"></i>Enregistrer les modifications';
        return false;
    }
    
    console.log('✅ Validation des champs réussie');
    
    // Ajouter un indicateur de chargement
    const loadingDiv = document.createElement('div');
    loadingDiv.className = 'alert alert-info';
    loadingDiv.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Enregistrement en cours...';
    form.parentNode.insertBefore(loadingDiv, form);
    
    // Soumettre le formulaire
    console.log('🚀 Soumission du formulaire...');
    form.submit();
    
    return true;
}

// Ajouter un gestionnaire d'événement sur le formulaire
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form[action*="eleves"]');
    if (form) {
        form.addEventListener('submit', function(e) {
            console.log('📤 Événement submit déclenché');
            console.log('📤 Action:', this.action);
            console.log('📤 Méthode:', this.method);
        });
    }
    

    // Prévisualisation de la photo
    const photoInput = document.getElementById('photo_profil');
    const photoPreview = document.getElementById('photoPreview');

    if (photoInput) {
        photoInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    photoPreview.innerHTML = `<img src="${e.target.result}" alt="Prévisualisation" class="img-thumbnail rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">`;
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Validation du formulaire avant soumission
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Afficher un message de confirmation
            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Enregistrement en cours...';
                submitButton.disabled = true;
            }
        });
    }

    // Améliorer l'expérience utilisateur avec des indicateurs visuels
    const inputs = document.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
        input.addEventListener('change', function() {
            if (this.value.trim() !== '') {
                this.classList.add('is-valid');
                this.classList.remove('is-invalid');
            }
        });
    });
});

// Fonction pour gérer l'upload d'images
function handleImageUpload(input) {
    const file = input.files[0];
    if (!file) return;

    // Vérifier la taille du fichier (2MB max)
    if (file.size > 2 * 1024 * 1024) {
        alert('Le fichier est trop volumineux. Taille maximale: 2MB');
        input.value = '';
        return;
    }

    // Vérifier le type de fichier
    if (!file.type.startsWith('image/')) {
        alert('Veuillez sélectionner un fichier image valide.');
        input.value = '';
        return;
    }

    // Afficher la prévisualisation
    const reader = new FileReader();
    reader.onload = (e) => {
        updateImagePreview(e.target.result);
    };
    reader.readAsDataURL(file);
}

function updateImagePreview(imageSrc) {
    const previewContainer = document.getElementById('photoPreview');
    if (!previewContainer) return;

    // Créer l'image de prévisualisation
    const img = document.createElement('img');
    img.src = imageSrc;
    img.className = 'img-thumbnail rounded-circle';
    img.style.cssText = 'width: 150px; height: 150px; object-fit: cover;';
    img.alt = 'Photo de profil';

    // Remplacer le contenu du conteneur
    previewContainer.innerHTML = '';
    previewContainer.appendChild(img);
}
</script>
@endpush
@endsection