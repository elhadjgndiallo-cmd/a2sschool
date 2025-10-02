@extends('layouts.app')

@section('title', 'Modifier les Informations de l\'Établissement')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-edit text-primary me-2"></i>
                    Modifier les Informations de l'Établissement
                </h1>
                <a href="{{ route('etablissement.informations') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour aux Informations
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

            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Formulaire de Modification
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('etablissement.informations.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <!-- Informations de base -->
                            <div class="col-md-8">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="nom" class="form-label">
                                            <i class="fas fa-building me-1"></i>Nom de l'Établissement *
                                        </label>
                                        <input type="text" class="form-control @error('nom') is-invalid @enderror" 
                                               id="nom" name="nom" value="{{ old('nom', $etablissement->nom) }}" required>
                                        @error('nom')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="telephone" class="form-label">
                                            <i class="fas fa-phone me-1"></i>Téléphone
                                        </label>
                                        <input type="tel" class="form-control @error('telephone') is-invalid @enderror" 
                                               id="telephone" name="telephone" value="{{ old('telephone', $etablissement->telephone) }}">
                                        @error('telephone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">
                                        <i class="fas fa-envelope me-1"></i>Email
                                    </label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email', $etablissement->email) }}">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="adresse" class="form-label">
                                        <i class="fas fa-map-marker-alt me-1"></i>Adresse Complète *
                                    </label>
                                    <textarea class="form-control @error('adresse') is-invalid @enderror" 
                                              id="adresse" name="adresse" rows="3" required>{{ old('adresse', $etablissement->adresse) }}</textarea>
                                    @error('adresse')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="slogan" class="form-label">
                                        <i class="fas fa-quote-left me-1"></i>Slogan
                                    </label>
                                    <input type="text" class="form-control @error('slogan') is-invalid @enderror" 
                                           id="slogan" name="slogan" value="{{ old('slogan', $etablissement->slogan) }}"
                                           placeholder="Ex: Excellence et Innovation">
                                    @error('slogan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">
                                        <i class="fas fa-align-left me-1"></i>Description
                                    </label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="4" 
                                              placeholder="Description de l'établissement...">{{ old('description', $etablissement->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Images -->
                            <div class="col-md-4">
                                <!-- Logo -->
                                <div class="mb-4">
                                    <label for="logo" class="form-label">
                                        <i class="fas fa-image me-1"></i>Logo de l'Établissement
                                    </label>
                                    
                                    @if($etablissement->logo)
                                        <div class="text-center mb-3">
                                            <img src="{{ asset('storage/' . $etablissement->logo) }}" 
                                                 alt="Logo" class="img-thumbnail" style="max-width: 150px; max-height: 150px;">
                                            <div class="mt-2">
                                                <small class="text-muted">Logo actuel</small>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <input type="file" class="form-control @error('logo') is-invalid @enderror" 
                                           id="logo" name="logo" accept="image/*">
                                    <small class="text-muted">Formats acceptés: JPG, PNG, GIF (Max: 2MB)</small>
                                    @error('logo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Cachet -->
                                <div class="mb-4">
                                    <label for="cachet" class="form-label">
                                        <i class="fas fa-stamp me-1"></i>Cachet Officiel
                                    </label>
                                    
                                    @if($etablissement->cachet)
                                        <div class="text-center mb-3">
                                            <img src="{{ asset('storage/' . $etablissement->cache) }}" 
                                                 alt="Cachet" class="img-thumbnail" style="max-width: 120px; max-height: 120px;">
                                            <div class="mt-2">
                                                <small class="text-muted">Cachet actuel</small>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <input type="file" class="form-control @error('cachet') is-invalid @enderror" 
                                           id="cachet" name="cachet" accept="image/*">
                                    <small class="text-muted">Formats acceptés: JPG, PNG, GIF (Max: 2MB)</small>
                                    @error('cachet')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('etablissement.informations') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
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









































