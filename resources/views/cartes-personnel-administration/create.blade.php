@extends('layouts.app')

@section('title', 'Créer une Carte Personnel Administration')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0"><i class="fas fa-id-card me-2"></i>Créer une Carte Personnel Administration</h4>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('cartes-personnel-administration.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="personnel_administration_id" class="form-label">Personnel Administration <span class="text-danger">*</span></label>
                                    <select name="personnel_administration_id" id="personnel_administration_id" class="form-select @error('personnel_administration_id') is-invalid @enderror" required>
                                        <option value="">Sélectionner un personnel</option>
                                        @foreach($personnel as $pers)
                                            <option value="{{ $pers->id }}" {{ old('personnel_administration_id', $selectedPersonnelId ?? '') == $pers->id ? 'selected' : '' }}>
                                                {{ $pers->utilisateur->nom }} {{ $pers->utilisateur->prenom }} 
                                                ({{ $pers->poste ?? 'Non renseigné' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('personnel_administration_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="type_carte" class="form-label">Type de carte <span class="text-danger">*</span></label>
                                    <select name="type_carte" id="type_carte" class="form-select @error('type_carte') is-invalid @enderror" required>
                                        <option value="">Sélectionner un type</option>
                                        <option value="standard" {{ old('type_carte') == 'standard' ? 'selected' : '' }}>Standard</option>
                                        <option value="temporaire" {{ old('type_carte') == 'temporaire' ? 'selected' : '' }}>Temporaire</option>
                                        <option value="remplacement" {{ old('type_carte') == 'remplacement' ? 'selected' : '' }}>Remplacement</option>
                                    </select>
                                    @error('type_carte')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="date_emission" class="form-label">Date d'émission <span class="text-danger">*</span></label>
                                    <input type="date" name="date_emission" id="date_emission" 
                                           class="form-control @error('date_emission') is-invalid @enderror" 
                                           value="{{ old('date_emission', now()->format('Y-m-d')) }}" required>
                                    @error('date_emission')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="date_expiration" class="form-label">Date d'expiration <span class="text-danger">*</span></label>
                                    <input type="date" name="date_expiration" id="date_expiration" 
                                           class="form-control @error('date_expiration') is-invalid @enderror" 
                                           value="{{ old('date_expiration', now()->addYear()->format('Y-m-d')) }}" required>
                                    @error('date_expiration')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="observations" class="form-label">Observations</label>
                            <textarea name="observations" id="observations" rows="3" 
                                      class="form-control @error('observations') is-invalid @enderror" 
                                      placeholder="Notes ou commentaires sur cette carte...">{{ old('observations') }}</textarea>
                            @error('observations')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('cartes-personnel-administration.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Retour
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Créer la carte
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

