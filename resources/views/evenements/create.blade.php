@extends('layouts.app')

@section('title', 'Créer un Événement')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-plus mr-2"></i>
                        Créer un Nouvel Événement
                    </h3>
                </div>

                <div class="card-body">
                    <form action="{{ route('evenements.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="titre">Titre de l'événement <span class="text-danger">*</span></label>
                                    <input type="text" name="titre" id="titre" class="form-control @error('titre') is-invalid @enderror" 
                                           value="{{ old('titre') }}" required maxlength="100">
                                    @error('titre')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="type">Type d'événement <span class="text-danger">*</span></label>
                                    <select name="type" id="type" class="form-control @error('type') is-invalid @enderror" required>
                                        <option value="">Sélectionner un type</option>
                                        <option value="cours" {{ old('type') == 'cours' ? 'selected' : '' }}>Cours</option>
                                        <option value="examen" {{ old('type') == 'examen' ? 'selected' : '' }}>Examen</option>
                                        <option value="reunion" {{ old('type') == 'reunion' ? 'selected' : '' }}>Réunion</option>
                                        <option value="conge" {{ old('type') == 'conge' ? 'selected' : '' }}>Congé</option>
                                        <option value="autre" {{ old('type') == 'autre' ? 'selected' : '' }}>Autre</option>
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" 
                                      rows="3" maxlength="500">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="lieu">Lieu</label>
                                    <input type="text" name="lieu" id="lieu" class="form-control @error('lieu') is-invalid @enderror" 
                                           value="{{ old('lieu') }}" maxlength="100">
                                    @error('lieu')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="classe_id">Classe concernée</label>
                                    <select name="classe_id" id="classe_id" class="form-control @error('classe_id') is-invalid @enderror">
                                        <option value="">Toutes les classes</option>
                                        @foreach($classes as $classe)
                                            <option value="{{ $classe->id }}" {{ old('classe_id') == $classe->id ? 'selected' : '' }}>
                                                {{ $classe->nom }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('classe_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="date_debut">Date de début <span class="text-danger">*</span></label>
                                    <input type="date" name="date_debut" id="date_debut" class="form-control @error('date_debut') is-invalid @enderror" 
                                           value="{{ old('date_debut') }}" required>
                                    @error('date_debut')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="date_fin">Date de fin <span class="text-danger">*</span></label>
                                    <input type="date" name="date_fin" id="date_fin" class="form-control @error('date_fin') is-invalid @enderror" 
                                           value="{{ old('date_fin') }}" required>
                                    @error('date_fin')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="heure_debut">Heure de début</label>
                                    <input type="time" name="heure_debut" id="heure_debut" class="form-control @error('heure_debut') is-invalid @enderror" 
                                           value="{{ old('heure_debut') }}">
                                    @error('heure_debut')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="heure_fin">Heure de fin</label>
                                    <input type="time" name="heure_fin" id="heure_fin" class="form-control @error('heure_fin') is-invalid @enderror" 
                                           value="{{ old('heure_fin') }}">
                                    @error('heure_fin')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="couleur">Couleur</label>
                                    <input type="color" name="couleur" id="couleur" class="form-control @error('couleur') is-invalid @enderror" 
                                           value="{{ old('couleur', '#3788d8') }}">
                                    @error('couleur')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="rappel">Rappel (en minutes)</label>
                                    <input type="number" name="rappel" id="rappel" class="form-control @error('rappel') is-invalid @enderror" 
                                           value="{{ old('rappel') }}" min="0">
                                    @error('rappel')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="form-check mt-4">
                                        <input type="checkbox" name="journee_entiere" id="journee_entiere" class="form-check-input" 
                                               value="1" {{ old('journee_entiere') ? 'checked' : '' }}>
                                        <label for="journee_entiere" class="form-check-label">
                                            Journée entière
                                        </label>
                                    </div>
                                    
                                    <div class="form-check">
                                        <input type="checkbox" name="public" id="public" class="form-check-input" 
                                               value="1" {{ old('public', true) ? 'checked' : '' }}>
                                        <label for="public" class="form-check-label">
                                            Événement public
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group text-right">
                            <a href="{{ route('evenements.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left mr-1"></i>
                                Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i>
                                Créer l'événement
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const journeeEntiere = document.getElementById('journee_entiere');
    const heureDebut = document.getElementById('heure_debut');
    const heureFin = document.getElementById('heure_fin');
    
    journeeEntiere.addEventListener('change', function() {
        if (this.checked) {
            heureDebut.disabled = true;
            heureFin.disabled = true;
            heureDebut.value = '';
            heureFin.value = '';
        } else {
            heureDebut.disabled = false;
            heureFin.disabled = false;
        }
    });
    
    // Initialiser l'état au chargement
    if (journeeEntiere.checked) {
        heureDebut.disabled = true;
        heureFin.disabled = true;
    }
});
</script>
@endsection
