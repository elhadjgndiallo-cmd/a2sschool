@extends('layouts.app')

@section('title', 'Ajouter une note - ' . $eleve->utilisateur->prenom . ' ' . $eleve->utilisateur->nom)

@section('content')
<div class="container-fluid">
    <!-- En-tête -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-plus-circle me-2"></i>
            Ajouter une note pour {{ $eleve->utilisateur->prenom }} {{ $eleve->utilisateur->nom }}
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('notes.eleve', $eleve->id) }}" class="btn btn-outline-secondary">
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
                <div class="col-md-3">
                    <p class="mb-1"><strong>Niveau:</strong> {{ $eleve->classe->isPrimaire() ? ($eleve->classe->niveau == 'Préscolaire' ? 'Préscolaire' : 'Primaire') : 'Collège/Lycée' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulaire de saisie de note -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Saisir une nouvelle note</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('notes.eleve.store', $eleve->id) }}" id="noteForm">
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
                    <div class="col-md-3 mb-3">
                        <label for="type_evaluation" class="form-label">Type d'évaluation <span class="text-danger">*</span></label>
                        <select class="form-select @error('type_evaluation') is-invalid @enderror" id="type_evaluation" name="type_evaluation" required>
                            <option value="devoir" {{ old('type_evaluation') == 'devoir' ? 'selected' : '' }}>Devoir</option>
                            <option value="controle" {{ old('type_evaluation') == 'controle' ? 'selected' : '' }}>Contrôle</option>
                            <option value="examen" {{ old('type_evaluation') == 'examen' ? 'selected' : '' }}>Examen</option>
                            <option value="oral" {{ old('type_evaluation') == 'oral' ? 'selected' : '' }}>Oral</option>
                            <option value="tp" {{ old('type_evaluation') == 'tp' ? 'selected' : '' }}>TP</option>
                        </select>
                        @error('type_evaluation')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="periode" class="form-label">Période <span class="text-danger">*</span></label>
                        <select class="form-select @error('periode') is-invalid @enderror" id="periode" name="periode" required>
                            <option value="trimestre1" {{ old('periode') == 'trimestre1' ? 'selected' : '' }}>Trimestre 1</option>
                            <option value="trimestre2" {{ old('periode') == 'trimestre2' ? 'selected' : '' }}>Trimestre 2</option>
                            <option value="trimestre3" {{ old('periode') == 'trimestre3' ? 'selected' : '' }}>Trimestre 3</option>
                        </select>
                        @error('periode')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="date_evaluation" class="form-label">Date d'évaluation <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('date_evaluation') is-invalid @enderror" id="date_evaluation" name="date_evaluation" value="{{ old('date_evaluation', date('Y-m-d')) }}" required>
                        @error('date_evaluation')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="coefficient" class="form-label">Coefficient <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('coefficient') is-invalid @enderror" id="coefficient" name="coefficient" min="1" max="10" step="1" value="{{ old('coefficient', 1) }}" required>
                        @error('coefficient')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                @if(!$eleve->classe->isPrimaire())
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="note_cours" class="form-label">Note de cours</label>
                        <input type="number" class="form-control @error('note_cours') is-invalid @enderror" id="note_cours" name="note_cours" min="0" max="{{ $eleve->classe->note_max }}" step="0.01" value="{{ old('note_cours') }}" placeholder="0.00">
                        <small class="form-text text-muted">Note sur {{ $eleve->classe->note_max }}</small>
                        @error('note_cours')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="note_composition" class="form-label">Note de composition</label>
                        <input type="number" class="form-control @error('note_composition') is-invalid @enderror" id="note_composition" name="note_composition" min="0" max="{{ $eleve->classe->note_max }}" step="0.01" value="{{ old('note_composition') }}" placeholder="0.00">
                        <small class="form-text text-muted">Note sur {{ $eleve->classe->note_max }}</small>
                        @error('note_composition')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                @else
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="note_composition" class="form-label">Note de composition</label>
                        <input type="number" class="form-control @error('note_composition') is-invalid @enderror" id="note_composition" name="note_composition" min="0" max="{{ $eleve->classe->note_max }}" step="0.01" value="{{ old('note_composition') }}" placeholder="0.00">
                        <small class="form-text text-muted">Note sur {{ $eleve->classe->note_max }}</small>
                        @error('note_composition')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                @endif

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="titre" class="form-label">Titre de l'évaluation</label>
                        <input type="text" class="form-control @error('titre') is-invalid @enderror" id="titre" name="titre" value="{{ old('titre') }}" placeholder="Ex: Contrôle de mathématiques">
                        @error('titre')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="note_finale_display" class="form-label">Note finale (calculée automatiquement)</label>
                        <input type="text" class="form-control" id="note_finale_display" readonly style="background-color: #f8f9fa;">
                        <small class="form-text text-muted">Cette note est calculée automatiquement selon la formule</small>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="commentaire" class="form-label">Commentaire</label>
                    <textarea class="form-control @error('commentaire') is-invalid @enderror" id="commentaire" name="commentaire" rows="3" placeholder="Commentaire optionnel">{{ old('commentaire') }}</textarea>
                    @error('commentaire')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('notes.eleve', $eleve->id) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>
                        Annuler
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i>
                        Enregistrer la note
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isPrimaire = {{ $eleve->classe->isPrimaire() ? 'true' : 'false' }};
    const noteMax = {{ $eleve->classe->note_max }};
    
    const matiereSelect = document.getElementById('matiere_id');
    const coefficientInput = document.getElementById('coefficient');
    const noteCoursInput = document.getElementById('note_cours');
    const noteCompositionInput = document.getElementById('note_composition');
    const noteFinaleDisplay = document.getElementById('note_finale_display');
    
    // Mettre à jour le coefficient quand on change de matière
    if (matiereSelect) {
        matiereSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption && selectedOption.dataset.coefficient) {
                coefficientInput.value = selectedOption.dataset.coefficient;
            }
        });
    }
    
    // Fonction pour calculer la note finale
    function calculerNoteFinale() {
        const noteCours = noteCoursInput && noteCoursInput.value ? parseFloat(noteCoursInput.value) : null;
        const noteComposition = noteCompositionInput && noteCompositionInput.value ? parseFloat(noteCompositionInput.value) : null;
        
        let noteFinale = null;
        
        if (isPrimaire) {
            // Pour primaire : note finale = note composition
            noteFinale = noteComposition;
        } else {
            // Pour collège/lycée : (Note Cours + (Note Composition * 2)) / 3
            if (noteCours === null && noteComposition === null) {
                noteFinale = null;
            } else if (noteCours === null) {
                noteFinale = noteComposition;
            } else if (noteComposition === null) {
                noteFinale = noteCours;
            } else {
                noteFinale = (noteCours + (noteComposition * 2)) / 3;
            }
        }
        
        if (noteFinale !== null) {
            noteFinaleDisplay.value = noteFinale.toFixed(2) + ' / ' + noteMax;
        } else {
            noteFinaleDisplay.value = '-';
        }
    }
    
    // Écouter les changements de notes
    if (noteCoursInput) {
        noteCoursInput.addEventListener('input', calculerNoteFinale);
    }
    if (noteCompositionInput) {
        noteCompositionInput.addEventListener('input', calculerNoteFinale);
    }
    
    // Calculer au chargement
    calculerNoteFinale();
});
</script>
@endpush
@endsection

