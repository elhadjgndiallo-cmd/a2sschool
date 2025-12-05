@extends('layouts.app')

@section('title', 'Ajouter un test mensuel - ' . $eleve->utilisateur->prenom . ' ' . $eleve->utilisateur->nom)

@section('content')
<div class="container-fluid">
    <!-- En-tête -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-plus-circle me-2"></i>
            Ajouter un test mensuel pour {{ $eleve->utilisateur->prenom }} {{ $eleve->utilisateur->nom }}
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('notes.mensuel.classe', $eleve->classe_id) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Retour
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Veuillez corriger les erreurs suivantes :</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Informations de l'élève -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Informations de l'élève</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <p class="mb-1"><strong>Nom complet:</strong> {{ $eleve->utilisateur->prenom }} {{ $eleve->utilisateur->nom }}</p>
                </div>
                <div class="col-md-3">
                    <p class="mb-1"><strong>Numéro étudiant:</strong> {{ $eleve->numero_etudiant }}</p>
                </div>
                <div class="col-md-3">
                    <p class="mb-1"><strong>Classe:</strong> {{ $eleve->classe->nom }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulaire de saisie de test mensuel -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Saisir un nouveau test mensuel</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('notes.mensuel.eleve.store', $eleve->id) }}" id="mensuelNoteForm">
                @csrf
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="matiere_id" class="form-label">Matière <span class="text-danger">*</span></label>
                        <select class="form-select @error('matiere_id') is-invalid @enderror" id="matiere_id" name="matiere_id" required>
                            <option value="">Choisir une matière</option>
                            @foreach($matieres as $matiere)
                                <option value="{{ $matiere->id }}" data-coefficient="{{ $matiere->coefficient }}" {{ old('matiere_id') == $matiere->id ? 'selected' : '' }}>
                                    {{ $matiere->nom }} (Coeff: {{ $matiere->coefficient }})
                                </option>
                            @endforeach
                        </select>
                        @error('matiere_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="enseignant_id" class="form-label">Enseignant <span class="text-danger">*</span></label>
                        <select class="form-select @error('enseignant_id') is-invalid @enderror" id="enseignant_id" name="enseignant_id" required>
                            <option value="">Choisir un enseignant</option>
                            @foreach($enseignants as $enseignant)
                                <option value="{{ $enseignant->id }}" {{ old('enseignant_id') == $enseignant->id ? 'selected' : '' }}>
                                    {{ $enseignant->utilisateur->prenom }} {{ $enseignant->utilisateur->nom }}
                                </option>
                            @endforeach
                        </select>
                        @error('enseignant_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="mois" class="form-label">Mois <span class="text-danger">*</span></label>
                        <select class="form-select @error('mois') is-invalid @enderror" id="mois" name="mois" required>
                            @foreach($moisListe as $num => $nom)
                                <option value="{{ $num }}" {{ old('mois', date('n')) == $num ? 'selected' : '' }}>
                                    {{ $nom }}
                                </option>
                            @endforeach
                        </select>
                        @error('mois')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="annee" class="form-label">Année <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('annee') is-invalid @enderror" id="annee" name="annee" min="2020" max="2030" value="{{ old('annee', $annee) }}" required>
                        @error('annee')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="coefficient" class="form-label">Coefficient <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('coefficient') is-invalid @enderror" id="coefficient" name="coefficient" min="1" max="10" step="1" value="{{ old('coefficient', 1) }}" required>
                        @error('coefficient')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="note" class="form-label">Note <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('note') is-invalid @enderror" id="note" name="note" min="0" max="20" step="0.25" value="{{ old('note') }}" placeholder="0.00" required>
                        <small class="form-text text-muted">Note sur 20</small>
                        @error('note')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('notes.mensuel.classe', $eleve->classe_id) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>
                        Annuler
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i>
                        Enregistrer le test mensuel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const matiereSelect = document.getElementById('matiere_id');
    const coefficientInput = document.getElementById('coefficient');
    
    // Mettre à jour le coefficient quand on change de matière
    if (matiereSelect && coefficientInput) {
        matiereSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption && selectedOption.dataset.coefficient) {
                coefficientInput.value = selectedOption.dataset.coefficient;
            }
        });
    }
});
</script>
@endpush
@endsection

