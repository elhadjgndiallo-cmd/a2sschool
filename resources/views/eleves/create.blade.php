@extends('layouts.app')

@section('title', 'Inscription Élève')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
                <h1 class="h3 mb-0">
                    <i class="fas fa-user-plus text-primary me-2"></i>
                    Inscription d'un Nouvel Élève
                </h1>
                <a href="{{ route('eleves.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>
                    <span class="d-none d-sm-inline">Retour à la liste</span>
                    <span class="d-sm-none">Retour</span>
                </a>
            </div>

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form action="{{ route('eleves.store-step') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="step" value="final">

                <div class="row g-3">
                    {{-- Colonne de gauche: Photo + Informations de l'élève --}}
                    <div class="col-12 col-lg-8">
                        <div class="row g-3">
                            {{-- Photo de l'élève --}}
                            <div class="col-12 col-md-4">
                                <div class="card h-100">
                                    <div class="card-body text-center">
                                        <div class="photo-preview-container mb-3">
                                            <div id="photo-preview" class="photo-preview">
                                                <div class="placeholder-photo">
                                                    <i class="fas fa-user fa-4x text-muted mb-3"></i>
                                                    <p class="text-muted">Photo de l'élève</p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <input type="file" class="form-control @error('photo_profil') is-invalid @enderror" 
                                                   id="photo_profil" name="photo_profil" accept="image/*" onchange="previewPhoto(this)">
                                            <small class="text-muted">JPG, PNG, GIF (Max: 2MB)</small>
                                            @error('photo_profil')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <button type="button" class="btn btn-outline-secondary btn-sm w-100">
                                            <i class="fas fa-camera me-2"></i>Changer de classe
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- Informations de l'élève --}}
                            <div class="col-md-8 mb-4">
                                <div class="card h-100">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0">
                                            <i class="fas fa-user me-2"></i>Informations de l'Élève
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        {{-- Matricule --}}
                                        <div class="mb-3">
                                            <label for="numero_etudiant" class="form-label text-danger">
                                                Matricule
                                            </label>
                                            <div class="input-group">
                                                <input type="text" class="form-control @error('numero_etudiant') is-invalid @enderror" 
                                                       id="numero_etudiant" name="numero_etudiant" 
                                                       value="{{ old('numero_etudiant') }}" 
                                                       placeholder="Génération automatique..." readonly>
                                                <button type="button" class="btn btn-outline-secondary" onclick="generateMatricule()" 
                                                        title="Générer un nouveau matricule">
                                                    <i class="fas fa-sync-alt"></i>
                                                </button>
                                            </div>
                                            @error('numero_etudiant')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="row">
                                            {{-- Prénom --}}
                                            <div class="col-md-6 mb-3">
                                                <label for="prenom" class="form-label text-danger">
                                                    Prénom
                                                </label>
                                                <input type="text" class="form-control @error('prenom') is-invalid @enderror" 
                                                       id="prenom" name="prenom" value="{{ old('prenom') }}" 
                                                       placeholder="Saisie obligatoire" required>
                                                @error('prenom')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            {{-- Nom --}}
                                            <div class="col-md-6 mb-3">
                                                <label for="nom" class="form-label text-danger">
                                                    Nom
                                                </label>
                                                <input type="text" class="form-control @error('nom') is-invalid @enderror" 
                                                       id="nom" name="nom" value="{{ old('nom') }}" 
                                                       placeholder="Saisie obligatoire" required>
                                                @error('nom')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="row">
                                            {{-- Sexe --}}
                                            <div class="col-md-6 mb-3">
                                                <label for="sexe" class="form-label">Sexe</label>
                                                <select class="form-select @error('sexe') is-invalid @enderror" id="sexe" name="sexe" required>
                                                    <option value="M" {{ old('sexe') == 'M' ? 'selected' : '' }}>MASCULIN</option>
                                                    <option value="F" {{ old('sexe') == 'F' ? 'selected' : '' }}>FEMININ</option>
                                                </select>
                                                @error('sexe')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            {{-- Date de naissance --}}
                                            <div class="col-md-6 mb-3">
                                                <label for="date_naissance" class="form-label">Date de naissance</label>
                                                <input type="date" class="form-control @error('date_naissance') is-invalid @enderror" 
                                                       id="date_naissance" name="date_naissance" 
                                                       value="{{ old('date_naissance') }}" required>
                                                @error('date_naissance')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        {{-- Lieu de naissance --}}
                                        <div class="mb-3">
                                            <label for="lieu_naissance" class="form-label">Lieu de naissance</label>
                                            <input type="text" class="form-control @error('lieu_naissance') is-invalid @enderror" 
                                                   id="lieu_naissance" name="lieu_naissance" 
                                                   value="{{ old('lieu_naissance') }}" required>
                                            @error('lieu_naissance')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        {{-- Adresse --}}
                                        <div class="mb-3">
                                            <label for="adresse" class="form-label">Adresse actuel</label>
                                            <textarea class="form-control @error('adresse') is-invalid @enderror" 
                                                      id="adresse" name="adresse" rows="2" required>{{ old('adresse') }}</textarea>
                                            @error('adresse')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="row">
                                            {{-- Téléphone --}}
                                            <div class="col-md-6 mb-3">
                                                <label for="telephone" class="form-label text-danger">Téléphone</label>
                                                <input type="tel" class="form-control @error('telephone') is-invalid @enderror" 
                                                       id="telephone" name="telephone" value="{{ old('telephone') }}"
                                                       placeholder="Saisie obligatoire">
                                                @error('telephone')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            {{-- Situation matrimoniale --}}
                                            <div class="col-md-6 mb-3">
                                                <label for="situation_matrimoniale" class="form-label">Situation matrimoniale</label>
                                                <select class="form-select @error('situation_matrimoniale') is-invalid @enderror" 
                                                        id="situation_matrimoniale" name="situation_matrimoniale">
                                                    <option value="celibataire" {{ old('situation_matrimoniale', 'celibataire') == 'celibataire' ? 'selected' : '' }}>CELIBATAIRE</option>
                                                    <option value="marie" {{ old('situation_matrimoniale') == 'marie' ? 'selected' : '' }}>MARIE(E)</option>
                                                    <option value="divorce" {{ old('situation_matrimoniale') == 'divorce' ? 'selected' : '' }}>DIVORCE(E)</option>
                                                    <option value="veuf" {{ old('situation_matrimoniale') == 'veuf' ? 'selected' : '' }}>VEUF/VEUVE</option>
                                                </select>
                                                @error('situation_matrimoniale')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        {{-- Parents --}}
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="parent_pere" class="form-label">Père</label>
                                                <input type="text" class="form-control" id="parent_pere" name="parent_pere" 
                                                       value="{{ old('parent_pere') }}">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="parent_mere" class="form-label">Mère</label>
                                                <input type="text" class="form-control" id="parent_mere" name="parent_mere" 
                                                       value="{{ old('parent_mere') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Colonne de droite: Inscription --}}
                    <div class="col-lg-4 mb-4">
                        <div class="card h-100">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-graduation-cap me-2"></i>Inscription
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    {{-- Date d'inscription --}}
                                    <div class="col-md-12 mb-3">
                                        <label for="date_inscription" class="form-label">Date d'inscription</label>
                                        <input type="date" class="form-control @error('date_inscription') is-invalid @enderror" 
                                               id="date_inscription" name="date_inscription" 
                                               value="{{ old('date_inscription', date('Y-m-d')) }}" required>
                                        @error('date_inscription')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- Type d'inscription --}}
                                    <div class="col-md-12 mb-3">
                                        <label for="type_inscription" class="form-label">Type d'inscription</label>
                                        <select class="form-select @error('type_inscription') is-invalid @enderror" 
                                                id="type_inscription" name="type_inscription" required>
                                            <option value="nouvelle" {{ old('type_inscription', 'nouvelle') == 'nouvelle' ? 'selected' : '' }}>NOUVELLE</option>
                                            <option value="reinscription" {{ old('type_inscription') == 'reinscription' ? 'selected' : '' }}>REINSCRIPTION</option>
                                            <option value="transfert" {{ old('type_inscription') == 'transfert' ? 'selected' : '' }}>TRANSFERT</option>
                                        </select>
                                        @error('type_inscription')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- École d'origine --}}
                                <div class="mb-3">
                                    <label for="ecole_origine" class="form-label">École d'origine</label>
                                    <input type="text" class="form-control @error('ecole_origine') is-invalid @enderror" 
                                           id="ecole_origine" name="ecole_origine" 
                                           value="{{ old('ecole_origine') }}">
                                    @error('ecole_origine')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- État d'activité --}}
                                <div class="mb-3">
                                    <label for="statut" class="form-label">État d'activité</label>
                                    <select class="form-select @error('statut') is-invalid @enderror" id="statut" name="statut" required>
                                        <option value="inscrit" {{ old('statut', 'inscrit') == 'inscrit' ? 'selected' : '' }}>ACTIF</option>
                                        <option value="en_cours" {{ old('statut') == 'en_cours' ? 'selected' : '' }}>EN COURS</option>
                                    </select>
                                    @error('statut')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Classe --}}
                                <div class="mb-3">
                                    <label for="classe_id" class="form-label">Classe</label>
                                    <select class="form-select @error('classe_id') is-invalid @enderror" id="classe_id" name="classe_id" required>
                                        <option value="">Sélectionner une classe</option>
                                        @foreach($classes as $classe)
                                            <option value="{{ $classe->id }}" {{ old('classe_id') == $classe->id ? 'selected' : '' }}>
                                                {{ $classe->nom }} - {{ $classe->niveau }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('classe_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Année scolaire --}}
                                <div class="mb-3">
                                    <label for="annee_scolaire_id" class="form-label">Année scolaire</label>
                                    <select class="form-select @error('annee_scolaire_id') is-invalid @enderror" 
                                            id="annee_scolaire_id" name="annee_scolaire_id" required>
                                        <option value="">Sélectionner l'année</option>
                                        @foreach($anneesScolarites as $annee)
                                            <option value="{{ $annee->id }}" 
                                                    {{ old('annee_scolaire_id', ($annee->active ? $annee->id : '')) == $annee->id ? 'selected' : '' }}>
                                                {{ $annee->nom }}
                                                @if($annee->active) (Active) @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('annee_scolaire_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Options de paiement --}}
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input type="hidden" name="exempte_frais" value="0">
                                        <input class="form-check-input" type="checkbox" name="exempte_frais" value="1" 
                                               id="exempte_frais" {{ old('exempte_frais') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="exempte_frais">
                                            exempter des frais de scolarités
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input type="hidden" name="paiement_annuel" value="0">
                                        <input class="form-check-input" type="checkbox" name="paiement_annuel" value="1" 
                                               id="paiement_annuel" {{ old('paiement_annuel') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="paiement_annuel">
                                            Paiement Annuel
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Section des parents --}}
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-users me-2"></i>Informations du Parent/Tuteur
                                </h5>
                            </div>
                            <div class="card-body">
                                {{-- Type de parent --}}
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="parent_type" id="existing_parent" 
                                                   value="existing" {{ old('parent_type') == 'existing' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="existing_parent">
                                                Parent existant
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="parent_type" id="new_parent" 
                                                   value="new" {{ old('parent_type', 'new') == 'new' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="new_parent">
                                                Nouveau parent
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                {{-- Sélection parent existant --}}
                                <div id="existing-parent-section" class="d-none">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="parent_id" class="form-label">Sélectionner le Parent</label>
                                            <select class="form-select @error('parent_id') is-invalid @enderror" id="parent_id" name="parent_id">
                                                <option value="">Choisir un parent</option>
                                                @foreach($parents as $parent)
                                                    <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                                                        {{ $parent->utilisateur->nom ?? '' }} {{ $parent->utilisateur->prenom ?? '' }}
                                                        @if($parent->utilisateur->telephone)
                                                            - {{ $parent->utilisateur->telephone }}
                                                        @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('parent_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                {{-- Nouveau parent --}}
                                <div id="new-parent-section">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="parent_prenom" class="form-label">Prénom du Parent</label>
                                            <input type="text" class="form-control @error('parent_prenom') is-invalid @enderror" 
                                                   id="parent_prenom" name="parent_prenom" 
                                                   value="{{ old('parent_prenom') }}">
                                            @error('parent_prenom')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="parent_nom" class="form-label">Nom du Parent</label>
                                            <input type="text" class="form-control @error('parent_nom') is-invalid @enderror" 
                                                   id="parent_nom" name="parent_nom" 
                                                   value="{{ old('parent_nom') }}">
                                            @error('parent_nom')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label for="parent_telephone" class="form-label">Téléphone (Optionnel)</label>
                                            <input type="tel" class="form-control @error('parent_telephone') is-invalid @enderror" 
                                                   id="parent_telephone" name="parent_telephone" 
                                                   value="{{ old('parent_telephone') }}">
                                            @error('parent_telephone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label for="parent_email" class="form-label">Email (Optionnel)</label>
                                            <input type="email" class="form-control @error('parent_email') is-invalid @enderror" 
                                                   id="parent_email" name="parent_email" 
                                                   value="{{ old('parent_email') }}">
                                            @error('parent_email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                    </div>

                                    <div class="mb-3">
                                        <label for="parent_adresse" class="form-label">Adresse Complète (Optionnel)</label>
                                        <textarea class="form-control @error('parent_adresse') is-invalid @enderror" 
                                                  id="parent_adresse" name="parent_adresse" rows="2">{{ old('parent_adresse') }}</textarea>
                                        @error('parent_adresse')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Lien de parenté (toujours visible) --}}
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="lien_parente" class="form-label">
                                            <i class="fas fa-heart me-1"></i>Lien de Parenté *
                                        </label>
                                        <select class="form-select @error('lien_parente') is-invalid @enderror" id="lien_parente" name="lien_parente" required>
                                            <option value="">Sélectionner le lien</option>
                                            <option value="pere" {{ old('lien_parente') == 'pere' ? 'selected' : '' }}>Père</option>
                                            <option value="mere" {{ old('lien_parente') == 'mere' ? 'selected' : '' }}>Mère</option>
                                            <option value="tuteur" {{ old('lien_parente') == 'tuteur' ? 'selected' : '' }}>Tuteur</option>
                                            <option value="tutrice" {{ old('lien_parente') == 'tutrice' ? 'selected' : '' }}>Tutrice</option>
                                            <option value="grand_pere" {{ old('lien_parente') == 'grand_pere' ? 'selected' : '' }}>Grand-père</option>
                                            <option value="grand_mere" {{ old('lien_parente') == 'grand_mere' ? 'selected' : '' }}>Grand-mère</option>
                                            <option value="oncle" {{ old('lien_parente') == 'oncle' ? 'selected' : '' }}>Oncle</option>
                                            <option value="tante" {{ old('lien_parente') == 'tante' ? 'selected' : '' }}>Tante</option>
                                            <option value="autre" {{ old('lien_parente') == 'autre' ? 'selected' : '' }}>Autre</option>
                                        </select>
                                        @error('lien_parente')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6" id="autre-lien-section" style="display: none;">
                                        <label for="autre_lien_parente" class="form-label">
                                            <i class="fas fa-edit me-1"></i>Préciser le Lien
                                        </label>
                                        <input type="text" class="form-control @error('autre_lien_parente') is-invalid @enderror" 
                                               id="autre_lien_parente" name="autre_lien_parente" 
                                               value="{{ old('autre_lien_parente') }}"
                                               placeholder="Préciser le lien de parenté">
                                        @error('autre_lien_parente')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Options supplémentaires --}}
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-check mb-2">
                                            <input type="hidden" name="responsable_legal" value="0">
                                            <input class="form-check-input" type="checkbox" name="responsable_legal" value="1" 
                                                   id="responsable_legal" {{ old('responsable_legal') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="responsable_legal">
                                                Responsable légal
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check mb-2">
                                            <input type="hidden" name="contact_urgence" value="0">
                                            <input class="form-check-input" type="checkbox" name="contact_urgence" value="1" 
                                                   id="contact_urgence" {{ old('contact_urgence') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="contact_urgence">
                                                Contact d'urgence
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check mb-2">
                                            <input type="hidden" name="autorise_sortie" value="0">
                                            <input class="form-check-input" type="checkbox" name="autorise_sortie" value="1" 
                                                   id="autorise_sortie" {{ old('autorise_sortie') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="autorise_sortie">
                                                Autorisé à récupérer l'élève
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Boutons d'action --}}
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('eleves.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Annuler
                            </a>
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-save me-2"></i>Enregistrer l'Inscription
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.photo-preview-container {
    display: flex;
    justify-content: center;
}

.photo-preview {
    width: 150px;
    height: 150px;
    border: 2px dashed #dee2e6;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f8f9fa;
    position: relative;
    overflow: hidden;
}

.photo-preview.has-image {
    border: 2px solid #0d6efd;
    background-color: white;
}

.placeholder-photo {
    text-align: center;
}

.preview-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 8px;
}

.text-danger {
    color: #dc3545 !important;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.card-header {
    border-bottom: 1px solid rgba(0, 0, 0, 0.125);
}
</style>

<script>
function previewPhoto(input) {
    const preview = document.getElementById('photo-preview');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" alt="Photo élève" class="preview-image">`;
            preview.classList.add('has-image');
        };
        
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.innerHTML = `
            <div class="placeholder-photo">
                <i class="fas fa-user fa-4x text-muted mb-3"></i>
                <p class="text-muted">Photo de l'élève</p>
            </div>
        `;
        preview.classList.remove('has-image');
    }
}

// Générer automatiquement le matricule
function generateMatricule() {
    const button = document.querySelector('button[onclick="generateMatricule()"]');
    const matriculeField = document.getElementById('numero_etudiant');
    const originalButtonContent = button.innerHTML;
    
    // Feedback visuel pendant le chargement
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    button.disabled = true;
    matriculeField.placeholder = 'Génération en cours...';
    
    fetch('{{ route("eleves.generate-matricule") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.matricule) {
            matriculeField.value = data.matricule;
            matriculeField.placeholder = 'Matricule généré automatiquement';
            
            // Animation de succès
            matriculeField.classList.add('border-success');
            setTimeout(() => {
                matriculeField.classList.remove('border-success');
            }, 2000);
        } else if (data.error) {
            console.error('Erreur:', data.error);
            matriculeField.placeholder = 'Erreur de génération';
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        matriculeField.placeholder = 'Erreur de connexion';
    })
    .finally(() => {
        // Restaurer le bouton
        button.innerHTML = originalButtonContent;
        button.disabled = false;
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const existingParentRadio = document.getElementById('existing_parent');
    const newParentRadio = document.getElementById('new_parent');
    const existingParentSection = document.getElementById('existing-parent-section');
    const newParentSection = document.getElementById('new-parent-section');

    function toggleParentSections() {
        if (existingParentRadio.checked) {
            existingParentSection.classList.remove('d-none');
            newParentSection.classList.add('d-none');
            
            // Rendre les champs nouveau parent optionnels et les vider
            newParentSection.querySelectorAll('input[required]').forEach(input => {
                input.removeAttribute('required');
            });
            
            // Vider les champs du nouveau parent pour éviter les conflits
            document.getElementById('parent_prenom').value = '';
            document.getElementById('parent_nom').value = '';
            document.getElementById('parent_telephone').value = '';
            document.getElementById('parent_email').value = '';
            document.getElementById('parent_adresse').value = '';
            
            // Rendre le parent_id requis
            document.getElementById('parent_id').setAttribute('required', 'required');
        } else {
            existingParentSection.classList.add('d-none');
            newParentSection.classList.remove('d-none');
            
            // Rendre les champs nouveau parent requis
            document.getElementById('parent_prenom').setAttribute('required', 'required');
            document.getElementById('parent_nom').setAttribute('required', 'required');
            
            // Vider la sélection du parent existant
            document.getElementById('parent_id').value = '';
            document.getElementById('parent_id').removeAttribute('required');
        }
    }

    existingParentRadio.addEventListener('change', toggleParentSections);
    newParentRadio.addEventListener('change', toggleParentSections);

    // Gestion du lien de parenté "autre"
    const lienParenteSelect = document.getElementById('lien_parente');
    const autreLienSection = document.getElementById('autre-lien-section');

    function toggleAutreLien() {
        if (lienParenteSelect.value === 'autre') {
            autreLienSection.style.display = 'block';
            document.getElementById('autre_lien_parente').setAttribute('required', 'required');
        } else {
            autreLienSection.style.display = 'none';
            document.getElementById('autre_lien_parente').removeAttribute('required');
            document.getElementById('autre_lien_parente').value = '';
        }
    }

    lienParenteSelect.addEventListener('change', toggleAutreLien);

    // Initialiser l'état
    toggleParentSections();
    toggleAutreLien();

    // Générer automatiquement le matricule au chargement
    const matriculeField = document.getElementById('numero_etudiant');
    if (!matriculeField.value) {
        generateMatricule();
    }
});
</script>
@endsection
