@extends('layouts.app')

@section('title', 'Modifier les Responsabilités de l\'Établissement')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-edit text-primary me-2"></i>
                    Modifier les Responsabilités de l'Établissement
                </h1>
                <a href="{{ route('etablissement.responsabilites') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour aux Responsabilités
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="row">
                <!-- Responsables -->
                <div class="col-md-6">
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-user-tie me-2"></i>
                                Responsables
                            </h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('etablissement.responsabilites.update') }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="mb-3">
                                    <label for="dg" class="form-label">
                                        <i class="fas fa-crown me-1"></i>Directeur Général
                                    </label>
                                    <input type="text" class="form-control @error('dg') is-invalid @enderror" 
                                           id="dg" name="dg" value="{{ old('dg', $etablissement->dg) }}"
                                           placeholder="Nom du Directeur Général">
                                    @error('dg')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="directeur_primaire" class="form-label">
                                        <i class="fas fa-user-graduate me-1"></i>Directeur du Primaire
                                    </label>
                                    <input type="text" class="form-control @error('directeur_primaire') is-invalid @enderror" 
                                           id="directeur_primaire" name="directeur_primaire" 
                                           value="{{ old('directeur_primaire', $etablissement->directeur_primaire) }}"
                                           placeholder="Nom du Directeur du Primaire">
                                    @error('directeur_primaire')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="statut_etablissement" class="form-label">
                                        <i class="fas fa-building me-1"></i>Statut de l'Établissement *
                                    </label>
                                    <select class="form-select @error('statut_etablissement') is-invalid @enderror" 
                                            id="statut_etablissement" name="statut_etablissement" required>
                                        <option value="">Sélectionner le statut</option>
                                        <option value="prive" {{ old('statut_etablissement', $etablissement->statut_etablissement) == 'prive' ? 'selected' : '' }}>
                                            Privé
                                        </option>
                                        <option value="public" {{ old('statut_etablissement', $etablissement->statut_etablissement) == 'public' ? 'selected' : '' }}>
                                            Public
                                        </option>
                                        <option value="semi_prive" {{ old('statut_etablissement', $etablissement->statut_etablissement) == 'semi_prive' ? 'selected' : '' }}>
                                            Semi-Privé
                                        </option>
                                    </select>
                                    @error('statut_etablissement')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                        </div>
                    </div>
                </div>

                <!-- Configuration Matricule -->
                <div class="col-md-6">
                    <div class="card shadow">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-id-card me-2"></i>
                                Configuration Matricule
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Format du matricule:</strong> 
                                <code>PRÉFIXE + NUMÉRO + SUFFIXE</code>
                                <br>
                                <small>Exemple: 2025DIAKAD001</small>
                            </div>

                            <div class="mb-3">
                                <label for="prefixe_matricule" class="form-label">
                                    <i class="fas fa-tag me-1"></i>Préfixe Matricule
                                </label>
                                <input type="text" class="form-control @error('prefixe_matricule') is-invalid @enderror" 
                                       id="prefixe_matricule" name="prefixe_matricule" 
                                       value="{{ old('prefixe_matricule', $etablissement->prefixe_matricule) }}"
                                       placeholder="Ex: 2025DIAKAD" maxlength="10">
                                <small class="text-muted">Généralement: ANNÉE + CODE ÉCOLE</small>
                                @error('prefixe_matricule')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="suffixe_matricule" class="form-label">
                                    <i class="fas fa-tag me-1"></i>Suffixe Matricule
                                </label>
                                <input type="text" class="form-control @error('suffixe_matricule') is-invalid @enderror" 
                                       id="suffixe_matricule" name="suffixe_matricule" 
                                       value="{{ old('suffixe_matricule', $etablissement->suffixe_matricule) }}"
                                       placeholder="Ex: (optionnel)" maxlength="10">
                                <small class="text-muted">Optionnel - ajouté à la fin</small>
                                @error('suffixe_matricule')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            @if($etablissement->prefixe_matricule)
                                <div class="alert alert-success">
                                    <i class="fas fa-eye me-2"></i>
                                    <strong>Aperçu:</strong> 
                                    <code>{{ $etablissement->prefixe_matricule }}001{{ $etablissement->suffixe_matricule }}</code>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('etablissement.responsabilites') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>Annuler
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i>Enregistrer les Modifications
                        </button>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection









































