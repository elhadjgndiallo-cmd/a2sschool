@extends('layouts.app')

@section('title', 'Modifier Enseignant')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-user-edit me-2"></i>
        Modifier l'Enseignant
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('enseignants.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Retour
        </a>
    </div>
</div>

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <strong>Erreurs de validation :</strong>
    <ul class="mb-0 mt-2">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('debug'))
<div class="alert alert-info alert-dismissible fade show" role="alert">
    <strong>DEBUG:</strong> {{ session('debug') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('info'))
<div class="alert alert-primary alert-dismissible fade show" role="alert">
    <strong>INFO:</strong> {{ session('info') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <strong>ERREUR:</strong> {!! session('error') !!}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<form method="POST" action="{{ route('enseignants.update', $enseignant->id) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    
    <div class="row">
        <!-- Colonne de gauche : Photo + Informations enseignant -->
        <div class="col-md-8">
            
            <!-- Section Photo de l'enseignant -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-camera me-2"></i>
                        Photo de profil
                    </h5>
                </div>
                <div class="card-body text-center">
                    @if($enseignant->utilisateur->photo_profil)
                        <div class="mb-3">
                            <img src="{{ Storage::url($enseignant->utilisateur->photo_profil) }}" 
                                 alt="Photo de {{ $enseignant->utilisateur->prenom }} {{ $enseignant->utilisateur->nom }}" 
                                 class="img-thumbnail" 
                                 style="width: 150px; height: 150px; object-fit: cover;">
                        </div>
                        <form action="{{ route('enseignants.delete-photo', $enseignant->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette photo ?')">
                                <i class="fas fa-trash me-1"></i>Supprimer la photo
                            </button>
                        </form>
                    @else
                        <div class="mb-3">
                            <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center" 
                                 style="width: 150px; height: 150px;">
                                <i class="fas fa-user fa-3x text-muted"></i>
                            </div>
                        </div>
                    @endif
                    
                    <div class="mb-3">
                        <label for="photo_profil" class="form-label">Nouvelle photo</label>
                        <input type="file" class="form-control" id="photo_profil" name="photo_profil" accept="image/*">
                        <div class="form-text">Formats acceptés : JPEG, PNG, JPG, GIF. Taille max : 2MB</div>
                    </div>
                </div>
            </div>

            <!-- Section Informations personnelles -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user me-2"></i>
                        Informations personnelles
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nom" name="nom" 
                                   value="{{ old('nom', $enseignant->utilisateur->nom) }}" 
                                   required minlength="2" maxlength="255">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="prenom" class="form-label">Prénom <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="prenom" name="prenom" 
                                   value="{{ old('prenom', $enseignant->utilisateur->prenom) }}" 
                                   required minlength="2" maxlength="255">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="{{ old('email', $enseignant->utilisateur->email) }}" 
                                   required maxlength="191">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="telephone" class="form-label">Téléphone <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="telephone" name="telephone" 
                                   value="{{ old('telephone', $enseignant->utilisateur->telephone) }}" 
                                   required maxlength="20" pattern="[0-9+\-\s()]+">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="adresse" class="form-label">Adresse <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="adresse" name="adresse" rows="3">{{ old('adresse', $enseignant->utilisateur->adresse) }}</textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="date_naissance" class="form-label">Date de naissance <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="date_naissance" name="date_naissance" 
                                   value="{{ old('date_naissance', $enseignant->utilisateur->date_naissance ? $enseignant->utilisateur->date_naissance->format('Y-m-d') : '') }}" 
                                   required max="{{ date('Y-m-d', strtotime('-1 day')) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="lieu_naissance" class="form-label">Lieu de naissance <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="lieu_naissance" name="lieu_naissance" 
                                   value="{{ old('lieu_naissance', $enseignant->utilisateur->lieu_naissance) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="sexe" class="form-label">Sexe <span class="text-danger">*</span></label>
                            <select class="form-select" id="sexe" name="sexe" required>
                                <option value="">Sélectionner...</option>
                                <option value="M" {{ old('sexe', $enseignant->utilisateur->sexe) == 'M' ? 'selected' : '' }}>Masculin</option>
                                <option value="F" {{ old('sexe', $enseignant->utilisateur->sexe) == 'F' ? 'selected' : '' }}>Féminin</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section Informations professionnelles -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-briefcase me-2"></i>
                        Informations professionnelles
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="numero_employe" class="form-label">Numéro d'employé <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="numero_employe" name="numero_employe" 
                                   value="{{ old('numero_employe', $enseignant->numero_employe) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="specialite" class="form-label">Spécialité <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="specialite" name="specialite" 
                                   value="{{ old('specialite', $enseignant->specialite) }}">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="date_embauche" class="form-label">Date d'embauche <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="date_embauche" name="date_embauche" 
                                   value="{{ old('date_embauche', $enseignant->date_embauche->format('Y-m-d')) }}" 
                                   max="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="statut" class="form-label">Statut <span class="text-danger">*</span></label>
                            <select class="form-select" id="statut" name="statut" required>
                                <option value="">Sélectionner...</option>
                                <option value="titulaire" {{ old('statut', $enseignant->statut) == 'titulaire' ? 'selected' : '' }}>Titulaire</option>
                                <option value="contractuel" {{ old('statut', $enseignant->statut) == 'contractuel' ? 'selected' : '' }}>Contractuel</option>
                                <option value="vacataire" {{ old('statut', $enseignant->statut) == 'vacataire' ? 'selected' : '' }}>Vacataire</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Colonne de droite : Matières enseignées -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-book me-2"></i>
                        Matières enseignées
                    </h5>
                </div>
                <div class="card-body">
                    @if($matieres->count() > 0)
                        <div class="row">
                            @foreach($matieres as $matiere)
                                <div class="col-12 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                               name="matieres[]" 
                                               value="{{ $matiere->id }}" 
                                               id="matiere_{{ $matiere->id }}"
                                               {{ in_array($matiere->id, old('matieres', $enseignant->matieres->pluck('id')->toArray())) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="matiere_{{ $matiere->id }}">
                                            {{ $matiere->nom }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">Aucune matière disponible.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Boutons d'action -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('enseignants.index') }}" class="btn btn-outline-secondary btn-lg">
                            <i class="fas fa-arrow-left me-2"></i>
                            Retour à la liste
                        </a>
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-save me-2"></i>
                            Mettre à jour l'enseignant
                        </button>
                        <button type="button" class="btn btn-warning btn-lg ms-2" onclick="testSimple()">
                            <i class="fas fa-flask me-2"></i>
                            Test Simple
                        </button>
                        <button type="button" class="btn btn-info btn-lg ms-2" onclick="testFormulaire()">
                            <i class="fas fa-bug me-2"></i>
                            Test Formulaire
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
function testSimple() {
    console.log('=== TEST SIMPLE ===');
    alert('Test simple fonctionne ! JavaScript est actif.');
    
    // Vérifier les éléments du formulaire
    const nom = document.getElementById('nom');
    const prenom = document.getElementById('prenom');
    
    if (nom && prenom) {
        console.log('Champs trouvés:', { nom: nom.value, prenom: prenom.value });
        alert(`Champs trouvés:\nNom: ${nom.value}\nPrénom: ${prenom.value}`);
    } else {
        console.error('Champs non trouvés');
        alert('Erreur: Champs non trouvés');
    }
}

function testFormulaire() {
    console.log('=== TEST FORMULAIRE ===');
    
    // Vérifier le formulaire principal
    const form = document.querySelector('form');
    if (!form) {
        alert('Erreur: Formulaire non trouvé');
        return;
    }
    
    console.log('Formulaire trouvé:', form);
    console.log('Action:', form.action);
    console.log('Méthode:', form.method);
    
    // Vérifier le token CSRF
    const csrfToken = document.querySelector('input[name="_token"]');
    if (csrfToken) {
        console.log('Token CSRF trouvé:', csrfToken.value);
    } else {
        console.error('Token CSRF non trouvé');
        alert('Erreur: Token CSRF non trouvé');
        return;
    }
    
    // Modifier temporairement les valeurs pour voir si ça marche
    const nom = document.getElementById('nom');
    const prenom = document.getElementById('prenom');
    
    if (nom && prenom) {
        const ancienNom = nom.value;
        const ancienPrenom = prenom.value;
        
        const timestamp = new Date().toLocaleTimeString().replace(/:/g, '');
        nom.value = ancienNom + '_TEST_' + timestamp;
        prenom.value = ancienPrenom + '_TEST';
        
        alert(`Valeurs modifiées temporairement:\nNom: ${nom.value}\nPrénom: ${prenom.value}\n\nCliquez sur "Mettre à jour l'enseignant" pour tester la soumission.`);
        
        // Restaurer après 5 secondes
        setTimeout(() => {
            nom.value = ancienNom;
            prenom.value = ancienPrenom;
            console.log('Valeurs restaurées');
        }, 5000);
    }
}

// Ajouter un indicateur de chargement au bouton principal
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== DIAGNOSTIC JAVASCRIPT ===');
    console.log('DOM chargé, initialisation des événements...');
    
    const submitButton = document.querySelector('button[type="submit"]');
    const form = document.querySelector('form');
    
    console.log('Bouton submit trouvé:', submitButton);
    console.log('Formulaire trouvé:', form);
    
    if (form) {
        form.addEventListener('submit', function() {
            console.log('Formulaire en cours de soumission...');
            if (submitButton) {
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Mise à jour en cours...';
                submitButton.disabled = true;
            }
        });
    }
    
    // Test des fonctions
    console.log('Fonction testModification:', typeof testModification);
    console.log('Fonction testAjax:', typeof testAjax);
    
    // Vérifier les boutons
    const testButtons = document.querySelectorAll('button[onclick]');
    console.log('Boutons avec onclick trouvés:', testButtons.length);
    
    testButtons.forEach((btn, index) => {
        console.log(`Bouton ${index + 1}:`, btn.textContent.trim(), 'onclick:', btn.getAttribute('onclick'));
    });
});
</script>
@endpush
