@extends('layouts.app')

@section('title', 'Modification Enseignant - Simple')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Modification Enseignant (Simple)</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Tableau de bord</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('enseignants.index') }}">Enseignants</a></li>
                        <li class="breadcrumb-item active">Modification Simple</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user-edit me-2"></i>
                        Modification Simple - {{ $enseignant->utilisateur->nom ?? 'N/A' }} {{ $enseignant->utilisateur->prenom ?? 'N/A' }}
                    </h5>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('enseignants.update-simple', $enseignant) }}" method="POST" id="formModification" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <!-- Photo de profil -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-camera me-2"></i>Photo de Profil
                                </h6>
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        @if($enseignant->utilisateur && $enseignant->utilisateur->photo_profil && \Storage::disk('public')->exists($enseignant->utilisateur->photo_profil))
                                            <img src="{{ asset('storage/' . $enseignant->utilisateur->photo_profil) }}?v={{ time() }}" 
                                                 alt="Photo actuelle" 
                                                 class="rounded-circle" 
                                                 style="width: 80px; height: 80px; object-fit: cover;"
                                                 id="currentPhoto">
                                        @else
                                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white" 
                                                 style="width: 80px; height: 80px; font-size: 24px;">
                                                {{ substr($enseignant->utilisateur->prenom ?? 'A', 0, 1) }}{{ substr($enseignant->utilisateur->nom ?? 'A', 0, 1) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1">
                                        <input type="file" 
                                               class="form-control @error('photo_profil') is-invalid @enderror" 
                                               id="photo_profil" 
                                               name="photo_profil" 
                                               accept="image/*">
                                        <small class="form-text text-muted">Formats acceptés: JPEG, PNG, JPG, GIF (max 2MB)</small>
                                        @error('photo_profil')
                                            <div class="invalid-feedback">{{ $error }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Informations de base -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-user me-2"></i>Informations de Base
                                </h6>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('nom') is-invalid @enderror" 
                                           id="nom" 
                                           name="nom" 
                                           value="{{ old('nom', $enseignant->utilisateur->nom ?? '') }}" 
                                           required>
                                    @error('nom')
                                        <div class="invalid-feedback">{{ $error }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="prenom" class="form-label">Prénom <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('prenom') is-invalid @enderror" 
                                           id="prenom" 
                                           name="prenom" 
                                           value="{{ old('prenom', $enseignant->utilisateur->prenom ?? '') }}" 
                                           required>
                                    @error('prenom')
                                        <div class="invalid-feedback">{{ $error }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" 
                                           class="form-control @error('email') is-invalid @enderror" 
                                           id="email" 
                                           name="email" 
                                           value="{{ old('email', $enseignant->utilisateur->email ?? '') }}" 
                                           required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $error }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="telephone" class="form-label">Téléphone <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('telephone') is-invalid @enderror" 
                                           id="telephone" 
                                           name="telephone" 
                                           value="{{ old('telephone', $enseignant->utilisateur->telephone ?? '') }}" 
                                           required>
                                    @error('telephone')
                                        <div class="invalid-feedback">{{ $error }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Informations personnelles -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-id-card me-2"></i>Informations Personnelles
                                </h6>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="date_naissance" class="form-label">Date de Naissance <span class="text-danger">*</span></label>
                                    <input type="date" 
                                           class="form-control @error('date_naissance') is-invalid @enderror" 
                                           id="date_naissance" 
                                           name="date_naissance" 
                                           value="{{ old('date_naissance', $enseignant->utilisateur->date_naissance ? $enseignant->utilisateur->date_naissance->format('Y-m-d') : '') }}"
                                           max="{{ date('Y-m-d', strtotime('-1 day')) }}"
                                           required>
                                    @error('date_naissance')
                                        <div class="invalid-feedback">{{ $error }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="lieu_naissance" class="form-label">Lieu de Naissance <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('lieu_naissance') is-invalid @enderror" 
                                           id="lieu_naissance" 
                                           name="lieu_naissance" 
                                           value="{{ old('lieu_naissance', $enseignant->utilisateur->lieu_naissance ?? '') }}"
                                           required>
                                    @error('lieu_naissance')
                                        <div class="invalid-feedback">{{ $error }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="sexe" class="form-label">Sexe <span class="text-danger">*</span></label>
                                    <select class="form-control @error('sexe') is-invalid @enderror" 
                                            id="sexe" 
                                            name="sexe" 
                                            required>
                                        <option value="">Sélectionner</option>
                                        <option value="M" {{ old('sexe', $enseignant->utilisateur->sexe) == 'M' ? 'selected' : '' }}>Masculin</option>
                                        <option value="F" {{ old('sexe', $enseignant->utilisateur->sexe) == 'F' ? 'selected' : '' }}>Féminin</option>
                                    </select>
                                    @error('sexe')
                                        <div class="invalid-feedback">{{ $error }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="adresse" class="form-label">Adresse <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('adresse') is-invalid @enderror" 
                                              id="adresse" 
                                              name="adresse" 
                                              rows="2"
                                              required>{{ old('adresse', $enseignant->utilisateur->adresse ?? '') }}</textarea>
                                    @error('adresse')
                                        <div class="invalid-feedback">{{ $error }}</div>
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
                                    <label for="numero_employe" class="form-label">Numéro d'Employé <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('numero_employe') is-invalid @enderror" 
                                           id="numero_employe" 
                                           name="numero_employe" 
                                           value="{{ old('numero_employe', $enseignant->numero_employe ?? '') }}"
                                           required>
                                    @error('numero_employe')
                                        <div class="invalid-feedback">{{ $error }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="specialite" class="form-label">Spécialité <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('specialite') is-invalid @enderror" 
                                           id="specialite" 
                                           name="specialite" 
                                           value="{{ old('specialite', $enseignant->specialite ?? '') }}"
                                           required>
                                    @error('specialite')
                                        <div class="invalid-feedback">{{ $error }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="date_embauche" class="form-label">Date d'Embauche <span class="text-danger">*</span></label>
                                    <input type="date" 
                                           class="form-control @error('date_embauche') is-invalid @enderror" 
                                           id="date_embauche" 
                                           name="date_embauche" 
                                           value="{{ old('date_embauche', $enseignant->date_embauche ? $enseignant->date_embauche->format('Y-m-d') : '') }}"
                                           required>
                                    @error('date_embauche')
                                        <div class="invalid-feedback">{{ $error }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="statut" class="form-label">Statut <span class="text-danger">*</span></label>
                                    <select class="form-control @error('statut') is-invalid @enderror" 
                                            id="statut" 
                                            name="statut"
                                            required>
                                        <option value="">Sélectionner</option>
                                        <option value="titulaire" {{ old('statut', $enseignant->statut) == 'titulaire' ? 'selected' : '' }}>Titulaire</option>
                                        <option value="contractuel" {{ old('statut', $enseignant->statut) == 'contractuel' ? 'selected' : '' }}>Contractuel</option>
                                        <option value="vacataire" {{ old('statut', $enseignant->statut) == 'vacataire' ? 'selected' : '' }}>Vacataire</option>
                                    </select>
                                    @error('statut')
                                        <div class="invalid-feedback">{{ $error }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('enseignants.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>
                                Retour
                            </a>
                            
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-2"></i>
                                Sauvegarder
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Informations
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        @if($enseignant->utilisateur && $enseignant->utilisateur->photo_profil && \Storage::disk('public')->exists($enseignant->utilisateur->photo_profil))
                            <img src="{{ asset('storage/' . $enseignant->utilisateur->photo_profil) }}?v={{ time() }}" 
                                 alt="Photo de profil" 
                                 class="rounded-circle mb-2" 
                                 style="width: 60px; height: 60px; object-fit: cover;"
                                 id="sidebarPhoto">
                        @else
                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white mx-auto mb-2" 
                                 style="width: 60px; height: 60px; font-size: 18px;">
                                {{ substr($enseignant->utilisateur->prenom ?? 'A', 0, 1) }}{{ substr($enseignant->utilisateur->nom ?? 'A', 0, 1) }}
                            </div>
                        @endif
                        <h6 class="mb-0">{{ $enseignant->utilisateur->nom ?? 'N/A' }} {{ $enseignant->utilisateur->prenom ?? 'N/A' }}</h6>
                        <small class="text-muted">{{ $enseignant->specialite ?? 'Spécialité non définie' }}</small>
                    </div>
                    
                    <hr>
                    
                    <p><strong>ID Enseignant:</strong> {{ $enseignant->id }}</p>
                    <p><strong>ID Utilisateur:</strong> {{ $enseignant->utilisateur_id }}</p>
                    <p><strong>Statut:</strong> 
                        <span class="badge bg-{{ $enseignant->actif ? 'success' : 'danger' }}">
                            {{ $enseignant->actif ? 'Actif' : 'Inactif' }}
                        </span>
                    </p>
                    
                    @if($enseignant->date_embauche)
                        <p><strong>Date d'embauche:</strong> {{ $enseignant->date_embauche->format('d/m/Y') }}</p>
                    @endif
                    
                    @if($enseignant->experience_annees)
                        <p><strong>Expérience:</strong> {{ $enseignant->experience_annees }} an(s)</p>
                    @endif
                    
                    @if($enseignant->niveau_etude)
                        <p><strong>Niveau d'étude:</strong> {{ $enseignant->niveau_etude }}</p>
                    @endif
                    
                    <hr>
                    
                    <p><strong>Date création:</strong> {{ $enseignant->created_at->format('d/m/Y H:i') }}</p>
                    <p><strong>Dernière modification:</strong> {{ $enseignant->updated_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== FORMULAIRE SIMPLE CHARGÉ ===');
    
    const form = document.getElementById('formModification');
    if (form) {
        console.log('✅ Formulaire trouvé');
        console.log('Action:', form.action);
        console.log('Méthode:', form.method);
        
        // Vérifier le token CSRF
        const csrfToken = document.querySelector('input[name="_token"]');
        if (csrfToken) {
            console.log('✅ Token CSRF trouvé');
        } else {
            console.log('❌ Token CSRF manquant');
        }
        
        // Prévisualisation de l'image
        const photoInput = document.getElementById('photo_profil');
        if (photoInput) {
            photoInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    console.log('Image sélectionnée:', file.name);
                    
                    // Vérifier la taille du fichier (2MB max)
                    if (file.size > 2 * 1024 * 1024) {
                        alert('Le fichier est trop volumineux. Taille maximale: 2MB');
                        photoInput.value = '';
                        return;
                    }
                    
                    // Vérifier le type de fichier
                    const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
                    if (!allowedTypes.includes(file.type)) {
                        alert('Format de fichier non supporté. Formats acceptés: JPEG, PNG, JPG, GIF');
                        photoInput.value = '';
                        return;
                    }
                    
                    // Afficher la prévisualisation
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        // Mettre à jour l'image dans la section photo
                        const photoPreview = document.querySelector('.me-3 img');
                        if (photoPreview) {
                            photoPreview.src = e.target.result + '?v=' + Date.now();
                            console.log('Image principale mise à jour:', photoPreview.src);
                        }
                        
                        // Mettre à jour l'image dans la sidebar
                        const sidebarPhoto = document.querySelector('.card-body img');
                        if (sidebarPhoto) {
                            sidebarPhoto.src = e.target.result + '?v=' + Date.now();
                            console.log('Image sidebar mise à jour:', sidebarPhoto.src);
                        }
                        
                        // Forcer le rechargement des images
                        if (photoPreview) {
                            photoPreview.onload = function() {
                                console.log('Image principale chargée avec succès');
                            };
                            photoPreview.onerror = function() {
                                console.error('Erreur lors du chargement de l\'image principale');
                            };
                        }
                        
                        if (sidebarPhoto) {
                            sidebarPhoto.onload = function() {
                                console.log('Image sidebar chargée avec succès');
                            };
                            sidebarPhoto.onerror = function() {
                                console.error('Erreur lors du chargement de l\'image sidebar');
                            };
                        }
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
        
        // Ajouter un gestionnaire de soumission
        form.addEventListener('submit', function(e) {
            console.log('=== SOUMISSION DU FORMULAIRE ===');
            console.log('Données du formulaire:');
            
            const formData = new FormData(form);
            for (let [key, value] of formData.entries()) {
                if (key !== 'photo_profil') {
                    console.log(key + ':', value);
                } else {
                    console.log(key + ':', value ? value.name : 'Aucun fichier');
                }
            }
            
            // Afficher un message de confirmation
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sauvegarde...';
                submitBtn.disabled = true;
            }
            
            // Forcer le rechargement de l'image après soumission
            setTimeout(function() {
                const photoInput = document.getElementById('photo_profil');
                if (photoInput && photoInput.files.length > 0) {
                    console.log('Rechargement de l\'image après soumission...');
                    const file = photoInput.files[0];
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const photoPreview = document.querySelector('.me-3 img');
                        const sidebarPhoto = document.querySelector('.card-body img');
                        
                        if (photoPreview) {
                            photoPreview.src = e.target.result + '?v=' + Date.now();
                        }
                        if (sidebarPhoto) {
                            sidebarPhoto.src = e.target.result + '?v=' + Date.now();
                        }
                    };
                    reader.readAsDataURL(file);
                }
            }, 1000);
        });
    } else {
        console.log('❌ Formulaire non trouvé');
    }
    
    // Fonction pour tester les boutons
    window.testSimpleEditButton = function(type, id) {
        console.log(`Test du bouton modification simple ${type} avec ID: ${id}`);
        return true;
    };
});
</script>
@endsection
