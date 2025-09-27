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
<div class="alert alert-danger">
    <h5><i class="fas fa-exclamation-triangle me-2"></i>Erreur de mise à jour</h5>
    <div class="mt-2">
        {!! session('error') !!}
    </div>
</div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <h5>Erreurs de validation :</h5>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
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
                        <i class="fas fa-camera me-2"></i>Photo de l'Enseignant
                    </h5>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <div class="photo-preview-container" style="width: 150px; height: 150px; margin: 0 auto;">
                            @if($enseignant->utilisateur && $enseignant->utilisateur->photo_profil && Storage::disk('public')->exists($enseignant->utilisateur->photo_profil))
                                <img id="photoPreview" src="{{ asset('storage/' . $enseignant->utilisateur->photo_profil) }}" 
                                     alt="Photo de l'enseignant" class="img-thumbnail rounded-circle" 
                                     style="width: 150px; height: 150px; object-fit: cover;">
                            @else
                                <div id="photoPreview" class="bg-light border rounded-circle d-flex align-items-center justify-content-center" 
                                     style="width: 150px; height: 150px;">
                                    <i class="fas fa-user fa-3x text-muted"></i>
                                </div>
                            @endif
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
                    @if($enseignant->utilisateur && $enseignant->utilisateur->photo_profil)
                        <div class="mb-2">
                            <form action="{{ route('enseignants.delete-photo', $enseignant->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette photo ?')">
                                    <i class="fas fa-trash me-1"></i>Supprimer la photo
                                </button>
                            </form>
                        </div>
                    @endif
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
                                       id="nom" name="nom" value="{{ old('nom', $enseignant->utilisateur->nom) }}" required>
                                @error('nom')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="prenom" class="form-label">Prénom *</label>
                                <input type="text" class="form-control @error('prenom') is-invalid @enderror" 
                                       id="prenom" name="prenom" value="{{ old('prenom', $enseignant->utilisateur->prenom) }}" required>
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
                                       id="email" name="email" value="{{ old('email', $enseignant->utilisateur->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="telephone" class="form-label">Téléphone *</label>
                                <input type="tel" class="form-control @error('telephone') is-invalid @enderror" 
                                       id="telephone" name="telephone" value="{{ old('telephone', $enseignant->utilisateur->telephone) }}" required>
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
                                       id="date_naissance" name="date_naissance" value="{{ old('date_naissance', $enseignant->utilisateur->date_naissance) }}" required>
                                @error('date_naissance')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="lieu_naissance" class="form-label">Lieu de Naissance *</label>
                                <input type="text" class="form-control @error('lieu_naissance') is-invalid @enderror" 
                                       id="lieu_naissance" name="lieu_naissance" value="{{ old('lieu_naissance', $enseignant->utilisateur->lieu_naissance) }}" required>
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
                                    <option value="M" {{ old('sexe', $enseignant->utilisateur->sexe) == 'M' ? 'selected' : '' }}>Masculin</option>
                                    <option value="F" {{ old('sexe', $enseignant->utilisateur->sexe) == 'F' ? 'selected' : '' }}>Féminin</option>
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
                                  id="adresse" name="adresse" rows="2" required>{{ old('adresse', $enseignant->utilisateur->adresse) }}</textarea>
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
                                       id="numero_employe" name="numero_employe" value="{{ old('numero_employe', $enseignant->numero_employe) }}" required>
                                @error('numero_employe')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="specialite" class="form-label">Spécialité *</label>
                                <input type="text" class="form-control @error('specialite') is-invalid @enderror" 
                                       id="specialite" name="specialite" value="{{ old('specialite', $enseignant->specialite) }}" required>
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
                                       id="date_embauche" name="date_embauche" value="{{ old('date_embauche', $enseignant->date_embauche) }}" required>
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
                                    <option value="titulaire" {{ old('statut', $enseignant->statut) == 'titulaire' ? 'selected' : '' }}>Titulaire</option>
                                    <option value="contractuel" {{ old('statut', $enseignant->statut) == 'contractuel' ? 'selected' : '' }}>Contractuel</option>
                                    <option value="vacataire" {{ old('statut', $enseignant->statut) == 'vacataire' ? 'selected' : '' }}>Vacataire</option>
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
                                   {{ in_array($matiere->id, old('matieres', $enseignant->matieres->pluck('id')->toArray())) ? 'checked' : '' }}>
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
                        <a href="{{ route('enseignants.index') }}" class="btn btn-outline-secondary btn-lg">
                            <i class="fas fa-arrow-left me-2"></i>
                            Retour à la liste
                        </a>
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-save me-2"></i>
                            Mettre à jour l'enseignant
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
        if (!form) {
            alert('ERREUR: Formulaire non trouvé');
            return;
        }
        
        alert('Formulaire trouvé ! Action: ' + form.action);
        console.log('Formulaire trouvé:', form);
        console.log('Action:', form.action);
        console.log('Méthode:', form.method);
        
        // Soumettre directement
        alert('Soumission du formulaire...');
        form.submit();
    };
    
    // Fonction de test PUT simplifiée
    window.createPUTForm = function() {
        alert('Création du formulaire PUT...');
        
        try {
            // Créer un formulaire temporaire pour la requête PUT
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('test.update.simple', $enseignant->id) }}';
            form.style.display = 'none';
            
            // Ajouter le token CSRF
            const csrfToken = document.querySelector('input[name="_token"]').value;
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);
            
            // Ajouter la méthode PUT
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'PUT';
            form.appendChild(methodInput);
            
            // Ajouter les données minimales requises
            const nomInput = document.createElement('input');
            nomInput.type = 'hidden';
            nomInput.name = 'nom';
            nomInput.value = 'Test Nom';
            form.appendChild(nomInput);
            
            const prenomInput = document.createElement('input');
            prenomInput.type = 'hidden';
            prenomInput.name = 'prenom';
            prenomInput.value = 'Test Prenom';
            form.appendChild(prenomInput);
            
            const emailInput = document.createElement('input');
            emailInput.type = 'hidden';
            emailInput.name = 'email';
            emailInput.value = 'test@example.com';
            form.appendChild(emailInput);
            
            const telephoneInput = document.createElement('input');
            telephoneInput.type = 'hidden';
            telephoneInput.name = 'telephone';
            telephoneInput.value = '123456789';
            form.appendChild(telephoneInput);
            
            const adresseInput = document.createElement('input');
            adresseInput.type = 'hidden';
            adresseInput.name = 'adresse';
            adresseInput.value = 'Test Adresse';
            form.appendChild(adresseInput);
            
            const dateNaissanceInput = document.createElement('input');
            dateNaissanceInput.type = 'hidden';
            dateNaissanceInput.name = 'date_naissance';
            dateNaissanceInput.value = '1990-01-01';
            form.appendChild(dateNaissanceInput);
            
            const lieuNaissanceInput = document.createElement('input');
            lieuNaissanceInput.type = 'hidden';
            lieuNaissanceInput.name = 'lieu_naissance';
            lieuNaissanceInput.value = 'Test Lieu';
            form.appendChild(lieuNaissanceInput);
            
            const sexeInput = document.createElement('input');
            sexeInput.type = 'hidden';
            sexeInput.name = 'sexe';
            sexeInput.value = 'M';
            form.appendChild(sexeInput);
            
            const numeroEmployeInput = document.createElement('input');
            numeroEmployeInput.type = 'hidden';
            numeroEmployeInput.name = 'numero_employe';
            numeroEmployeInput.value = 'EMP001';
            form.appendChild(numeroEmployeInput);
            
            const specialiteInput = document.createElement('input');
            specialiteInput.type = 'hidden';
            specialiteInput.name = 'specialite';
            specialiteInput.value = 'Test Spécialité';
            form.appendChild(specialiteInput);
            
            const dateEmbaucheInput = document.createElement('input');
            dateEmbaucheInput.type = 'hidden';
            dateEmbaucheInput.name = 'date_embauche';
            dateEmbaucheInput.value = '2020-01-01';
            form.appendChild(dateEmbaucheInput);
            
            const statutInput = document.createElement('input');
            statutInput.type = 'hidden';
            statutInput.name = 'statut';
            statutInput.value = 'titulaire';
            form.appendChild(statutInput);
            
            alert('Formulaire créé ! Soumission...');
            
            // Ajouter le formulaire au DOM et le soumettre
            document.body.appendChild(form);
            form.submit();
            
        } catch (error) {
            alert('ERREUR: ' + error.message);
            console.error('Erreur création formulaire:', error);
        }
    };

// Test au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== DOM CHARGÉ ===');
    alert('Page chargée - JavaScript actif');
    
    const form = document.querySelector('form');
    if (form) {
        console.log('Formulaire trouvé au chargement:', form);
        alert('Formulaire trouvé au chargement !');
    } else {
        console.log('ERREUR: Formulaire non trouvé au chargement');
        alert('ERREUR: Formulaire non trouvé au chargement');
    }
});
</script>
@endpush
