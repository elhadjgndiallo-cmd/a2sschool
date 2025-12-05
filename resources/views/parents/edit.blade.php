@extends('layouts.app')

@section('title', 'Modifier Parent - ' . $parent->utilisateur->prenom . ' ' . $parent->utilisateur->nom)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-user-edit me-2"></i>
            Modifier Parent - {{ $parent->utilisateur->prenom }} {{ $parent->utilisateur->nom }}
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('parents.show', $parent->id) }}" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-1"></i>
                Retour
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Veuillez corriger les erreurs suivantes :</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('parents.update', $parent->id) }}">
        @csrf
        @method('PUT')

        <div class="row">
            <!-- Informations personnelles -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-user-circle me-2"></i>
                            Informations personnelles
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="prenom" class="form-label">Prénom <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('prenom') is-invalid @enderror" 
                                   id="prenom" name="prenom" 
                                   value="{{ old('prenom', $parent->utilisateur->prenom) }}" required>
                            @error('prenom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nom') is-invalid @enderror" 
                                   id="nom" name="nom" 
                                   value="{{ old('nom', $parent->utilisateur->nom) }}" required>
                            @error('nom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="telephone" class="form-label">Téléphone</label>
                            <input type="tel" class="form-control @error('telephone') is-invalid @enderror" 
                                   id="telephone" name="telephone" 
                                   value="{{ old('telephone', $parent->utilisateur->telephone) }}">
                            @error('telephone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" 
                                   value="{{ old('email', $parent->utilisateur->email) }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="adresse" class="form-label">Adresse</label>
                            <textarea class="form-control @error('adresse') is-invalid @enderror" 
                                      id="adresse" name="adresse" rows="2">{{ old('adresse', $parent->utilisateur->adresse) }}</textarea>
                            @error('adresse')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="date_naissance" class="form-label">Date de naissance</label>
                                <input type="date" class="form-control @error('date_naissance') is-invalid @enderror" 
                                       id="date_naissance" name="date_naissance" 
                                       value="{{ old('date_naissance', $parent->utilisateur->date_naissance ? $parent->utilisateur->date_naissance->format('Y-m-d') : '') }}">
                                @error('date_naissance')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="sexe" class="form-label">Sexe</label>
                                <select class="form-select @error('sexe') is-invalid @enderror" id="sexe" name="sexe">
                                    <option value="">Sélectionner</option>
                                    <option value="M" {{ old('sexe', $parent->utilisateur->sexe) == 'M' ? 'selected' : '' }}>Masculin</option>
                                    <option value="F" {{ old('sexe', $parent->utilisateur->sexe) == 'F' ? 'selected' : '' }}>Féminin</option>
                                </select>
                                @error('sexe')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informations professionnelles -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-briefcase me-2"></i>
                            Informations professionnelles
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="profession" class="form-label">Profession</label>
                            <input type="text" class="form-control @error('profession') is-invalid @enderror" 
                                   id="profession" name="profession" 
                                   value="{{ old('profession', $parent->profession) }}">
                            @error('profession')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="employeur" class="form-label">Employeur</label>
                            <input type="text" class="form-control @error('employeur') is-invalid @enderror" 
                                   id="employeur" name="employeur" 
                                   value="{{ old('employeur', $parent->employeur) }}">
                            @error('employeur')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="telephone_travail" class="form-label">Téléphone travail</label>
                            <input type="tel" class="form-control @error('telephone_travail') is-invalid @enderror" 
                                   id="telephone_travail" name="telephone_travail" 
                                   value="{{ old('telephone_travail', $parent->telephone_travail) }}">
                            @error('telephone_travail')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="lien_parente" class="form-label">Lien de parenté <span class="text-danger">*</span></label>
                            <select class="form-select @error('lien_parente') is-invalid @enderror" id="lien_parente" name="lien_parente" required>
                                <option value="">Sélectionner</option>
                                <option value="pere" {{ old('lien_parente', $parent->lien_parente) == 'pere' ? 'selected' : '' }}>Père</option>
                                <option value="mere" {{ old('lien_parente', $parent->lien_parente) == 'mere' ? 'selected' : '' }}>Mère</option>
                                <option value="tuteur" {{ old('lien_parente', $parent->lien_parente) == 'tuteur' ? 'selected' : '' }}>Tuteur</option>
                                <option value="autre" {{ old('lien_parente', $parent->lien_parente) == 'autre' ? 'selected' : '' }}>Autre</option>
                            </select>
                            @error('lien_parente')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="hidden" name="contact_urgence" value="0">
                                <input class="form-check-input @error('contact_urgence') is-invalid @enderror" 
                                       type="checkbox" name="contact_urgence" value="1" 
                                       id="contact_urgence" {{ old('contact_urgence', $parent->contact_urgence) ? 'checked' : '' }}>
                                <label class="form-check-label" for="contact_urgence">
                                    Contact d'urgence
                                </label>
                                @error('contact_urgence')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="hidden" name="actif" value="0">
                                <input class="form-check-input @error('actif') is-invalid @enderror" 
                                       type="checkbox" name="actif" value="1" 
                                       id="actif" {{ old('actif', $parent->actif) ? 'checked' : '' }}>
                                <label class="form-check-label" for="actif">
                                    Parent actif
                                </label>
                                @error('actif')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Boutons d'action -->
        <div class="d-flex justify-content-between">
            <a href="{{ route('parents.show', $parent->id) }}" class="btn btn-outline-secondary">
                <i class="fas fa-times me-1"></i>
                Annuler
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i>
                Enregistrer les modifications
            </button>
        </div>
    </form>
</div>
@endsection

