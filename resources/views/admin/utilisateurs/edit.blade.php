@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Modifier l'utilisateur</h4>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('admin.utilisateurs.update', $utilisateur->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nom">Nom</label>
                                    <input id="nom" type="text" class="form-control @error('nom') is-invalid @enderror" name="nom" value="{{ old('nom', $utilisateur->nom) }}" required autofocus>
                                    @error('nom')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="prenom">Prénom</label>
                                    <input id="prenom" type="text" class="form-control @error('prenom') is-invalid @enderror" name="prenom" value="{{ old('prenom', $utilisateur->prenom) }}" required>
                                    @error('prenom')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Adresse e-mail</label>
                                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', $utilisateur->email) }}" required>
                                    @error('email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="role">Rôle</label>
                                    <select id="role" class="form-control @error('role') is-invalid @enderror" name="role" required>
                                        <option value="admin" {{ old('role', $utilisateur->role) == 'admin' ? 'selected' : '' }}>Administrateur</option>
                                        <option value="teacher" {{ old('role', $utilisateur->role) == 'teacher' ? 'selected' : '' }}>Enseignant</option>
                                        <option value="student" {{ old('role', $utilisateur->role) == 'student' ? 'selected' : '' }}>Élève</option>
                                        <option value="parent" {{ old('role', $utilisateur->role) == 'parent' ? 'selected' : '' }}>Parent</option>
                                    </select>
                                    @error('role')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="telephone">Téléphone</label>
                                    <input id="telephone" type="text" class="form-control @error('telephone') is-invalid @enderror" name="telephone" value="{{ old('telephone', $utilisateur->telephone) }}">
                                    @error('telephone')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sexe">Sexe</label>
                                    <select id="sexe" class="form-control @error('sexe') is-invalid @enderror" name="sexe">
                                        <option value="">-- Sélectionner --</option>
                                        <option value="M" {{ old('sexe', $utilisateur->sexe) == 'M' ? 'selected' : '' }}>Masculin</option>
                                        <option value="F" {{ old('sexe', $utilisateur->sexe) == 'F' ? 'selected' : '' }}>Féminin</option>
                                    </select>
                                    @error('sexe')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="date_naissance">Date de naissance</label>
                                    <input id="date_naissance" type="date" class="form-control @error('date_naissance') is-invalid @enderror" name="date_naissance" value="{{ old('date_naissance', $utilisateur->date_naissance) }}">
                                    @error('date_naissance')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="lieu_naissance">Lieu de naissance</label>
                                    <input id="lieu_naissance" type="text" class="form-control @error('lieu_naissance') is-invalid @enderror" name="lieu_naissance" value="{{ old('lieu_naissance', $utilisateur->lieu_naissance) }}">
                                    @error('lieu_naissance')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="adresse">Adresse</label>
                                    <textarea id="adresse" class="form-control @error('adresse') is-invalid @enderror" name="adresse" rows="3">{{ old('adresse', $utilisateur->adresse) }}</textarea>
                                    @error('adresse')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password">Nouveau mot de passe (laisser vide pour conserver l'actuel)</label>
                                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password">
                                    @error('password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password-confirm">Confirmer le mot de passe</label>
                                    <input id="password-confirm" type="password" class="form-control" name="password_confirmation">
                                </div>
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Enregistrer les modifications
                                </button>
                            </div>
                            <div class="col-md-6 text-right">
                                <a href="{{ route('admin.utilisateurs') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Retour à la liste
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection