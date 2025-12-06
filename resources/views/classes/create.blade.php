@extends('layouts.app')

@section('title', 'Ajouter une Classe')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-school me-2"></i>
        Ajouter une Classe
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('classes.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Retour à la liste
        </a>
    </div>
</div>

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <ul class="mb-0">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card">
    <div class="card-header bg-white">
        <h5 class="mb-0">Informations de la Classe</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('classes.store') }}" method="POST">
            @csrf
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="nom" class="form-label">Nom de la classe *</label>
                        <input type="text" class="form-control @error('nom') is-invalid @enderror" id="nom" name="nom" value="{{ old('nom') }}" required>
                        @error('nom')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="niveau" class="form-label">Niveau *</label>
                        <select class="form-select @error('niveau') is-invalid @enderror" id="niveau" name="niveau" required>
                            <option value="">Sélectionner un niveau</option>
                            <option value="Préscolaire" {{ old('niveau') == 'Préscolaire' ? 'selected' : '' }}>Préscolaire</option>
                            <option value="Primaire" {{ old('niveau') == 'Primaire' ? 'selected' : '' }}>Primaire</option>
                            <option value="Collège" {{ old('niveau') == 'Collège' ? 'selected' : '' }}>Collège</option>
                            <option value="Lycée" {{ old('niveau') == 'Lycée' ? 'selected' : '' }}>Lycée</option>
                            <option value="Supérieur" {{ old('niveau') == 'Supérieur' ? 'selected' : '' }}>Supérieur</option>
                        </select>
                        @error('niveau')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="section" class="form-label">Section *</label>
                        <input type="text" class="form-control @error('section') is-invalid @enderror" id="section" name="section" value="{{ old('section') }}" required>
                        @error('section')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Exemple: Scientifique, Littéraire, Technique, etc.</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="effectif_max" class="form-label">Effectif maximum *</label>
                        <input type="number" class="form-control @error('effectif_max') is-invalid @enderror" id="effectif_max" name="effectif_max" value="{{ old('effectif_max', 30) }}" min="1" required>
                        @error('effectif_max')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="reset" class="btn btn-outline-secondary">
                    <i class="fas fa-undo me-1"></i>
                    Réinitialiser
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>
@endsection