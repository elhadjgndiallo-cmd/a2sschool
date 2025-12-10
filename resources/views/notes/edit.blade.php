@extends('layouts.app')

@section('title', 'Modifier une note')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-edit"></i>
                        Modifier une note
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('notes.eleve', $note->eleve_id) }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Retour
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i>
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Erreur de validation :</strong>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <!-- Informations de la note -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-info">
                                    <i class="fas fa-user"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Élève</span>
                                    <span class="info-box-number">{{ $note->eleve->nom_complet }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-success">
                                    <i class="fas fa-book"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Matière</span>
                                    <span class="info-box-number">{{ $note->matiere->nom }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('notes.update', $note) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <!-- Note Cours -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="note_cours">
                                        <i class="fas fa-pen"></i>
                                        Note Cours
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" 
                                           class="form-control @error('note_cours') is-invalid @enderror" 
                                           id="note_cours" 
                                           name="note_cours" 
                                           value="{{ old('note_cours', $note->note_cours) }}"
                                           min="0" 
                                           max="{{ $note->eleve->classe->note_max }}" 
                                           step="0.1"
                                           placeholder="Ex: 15.5">
                                    @error('note_cours')
                                        <div class="invalid-feedback">{{ $error }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Note sur {{ $note->eleve->classe->note_max }}. Laissez vide pour une note par défaut de 2/{{ $note->eleve->classe->note_max }}.
                                    </small>
                                </div>
                            </div>

                            <!-- Note Composition -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="note_composition">
                                        <i class="fas fa-file-alt"></i>
                                        Note Composition
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" 
                                           class="form-control @error('note_composition') is-invalid @enderror" 
                                           id="note_composition" 
                                           name="note_composition" 
                                           value="{{ old('note_composition', $note->note_composition) }}"
                                           min="0" 
                                           max="{{ $note->eleve->classe->note_max }}" 
                                           step="0.1"
                                           placeholder="Ex: 16.0">
                                    @error('note_composition')
                                        <div class="invalid-feedback">{{ $error }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Note sur {{ $note->eleve->classe->note_max }}. Laissez vide pour une note par défaut de 2/{{ $note->eleve->classe->note_max }}.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Note Finale Calculée -->
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="note_finale">
                                        <i class="fas fa-calculator"></i>
                                        Note Finale (Calculée automatiquement)
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="note_finale" 
                                           value="{{ $note->note_finale }}" 
                                           readonly
                                           style="background-color: #f8f9fa;">
                                    <small class="form-text text-muted">
                                        Formule : (Note Cours + (Note Composition × 2)) ÷ 3
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Coefficient -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="coefficient">
                                        <i class="fas fa-weight"></i>
                                        Coefficient
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" 
                                           class="form-control @error('coefficient') is-invalid @enderror" 
                                           id="coefficient" 
                                           name="coefficient" 
                                           value="{{ old('coefficient', $note->coefficient) }}"
                                           min="0.1" 
                                           max="10" 
                                           step="0.1"
                                           required>
                                    @error('coefficient')
                                        <div class="invalid-feedback">{{ $error }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Type d'évaluation -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="type_evaluation">
                                        <i class="fas fa-clipboard-list"></i>
                                        Type d'évaluation
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control @error('type_evaluation') is-invalid @enderror" 
                                            id="type_evaluation" 
                                            name="type_evaluation" 
                                            required>
                                        <option value="">Sélectionner...</option>
                                        <option value="devoir" {{ old('type_evaluation', $note->type_evaluation) == 'Composition' ? 'selected' : '' }}>
                                            Composition
                                        </option>
                                        <option value="controle" {{ old('type_evaluation', $note->type_evaluation) == 'controle' ? 'selected' : '' }}>
                                            Contrôle
                                        </option>
                                        <option value="examen" {{ old('type_evaluation', $note->type_evaluation) == 'examen' ? 'selected' : '' }}>
                                            Examen
                                        </option>
                                        <option value="oral" {{ old('type_evaluation', $note->type_evaluation) == 'oral' ? 'selected' : '' }}>
                                            Oral
                                        </option>
                                        <option value="tp" {{ old('type_evaluation', $note->type_evaluation) == 'tp' ? 'selected' : '' }}>
                                            Travaux Pratiques
                                        </option>
                                    </select>
                                    @error('type_evaluation')
                                        <div class="invalid-feedback">{{ $error }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Période -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="periode">
                                        <i class="fas fa-calendar-alt"></i>
                                        Période
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control @error('periode') is-invalid @enderror" 
                                            id="periode" 
                                            name="periode" 
                                            required>
                                        <option value="">Sélectionner...</option>
                                        <option value="trimestre1" {{ old('periode', $note->periode) == 'trimestre1' ? 'selected' : '' }}>
                                            Trimestre 1
                                        </option>
                                        <option value="trimestre2" {{ old('periode', $note->periode) == 'trimestre2' ? 'selected' : '' }}>
                                            Trimestre 2
                                        </option>
                                        <option value="trimestre3" {{ old('periode', $note->periode) == 'trimestre3' ? 'selected' : '' }}>
                                            Trimestre 3
                                        </option>
                                    </select>
                                    @error('periode')
                                        <div class="invalid-feedback">{{ $error }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Date d'évaluation -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="date_evaluation">
                                        <i class="fas fa-calendar"></i>
                                        Date d'évaluation
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" 
                                           class="form-control @error('date_evaluation') is-invalid @enderror" 
                                           id="date_evaluation" 
                                           name="date_evaluation" 
                                           value="{{ old('date_evaluation', $note->date_evaluation->format('Y-m-d')) }}"
                                           required>
                                    @error('date_evaluation')
                                        <div class="invalid-feedback">{{ $error }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Titre -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="titre">
                                        <i class="fas fa-tag"></i>
                                        Titre de l'évaluation
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('titre') is-invalid @enderror" 
                                           id="titre" 
                                           name="titre" 
                                           value="{{ old('titre', $note->titre) }}"
                                           placeholder="Ex: Devoir de Mathématiques">
                                    @error('titre')
                                        <div class="invalid-feedback">{{ $error }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Commentaire -->
                        <div class="form-group">
                            <label for="commentaire">
                                <i class="fas fa-comment"></i>
                                Commentaire
                            </label>
                            <textarea class="form-control @error('commentaire') is-invalid @enderror" 
                                      id="commentaire" 
                                      name="commentaire" 
                                      rows="3"
                                      placeholder="Commentaire sur la note...">{{ old('commentaire', $note->commentaire) }}</textarea>
                            @error('commentaire')
                                <div class="invalid-feedback">{{ $error }}</div>
                            @enderror
                        </div>

                        <!-- Boutons d'action -->
                        <div class="form-group">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i>
                                    Mettre à jour la note
                                </button>
                                <a href="{{ route('notes.eleve', $note->eleve_id) }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i>
                                    Annuler
                                </a>
                                @if(auth()->user()->hasPermission('notes.delete'))
                                    <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                                        <i class="fas fa-trash"></i>
                                        Supprimer
                                    </button>
                                @endif
                            </div>
                        </div>
                    </form>

                    <!-- Formulaire de suppression caché -->
                    <form id="delete-form" action="{{ route('notes.destroy', $note) }}" method="POST" style="display: none;">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Calcul automatique de la note finale
    function calculerNoteFinale() {
        const noteCours = parseFloat(document.getElementById('note_cours').value) || 0.0;
        const noteComposition = parseFloat(document.getElementById('note_composition').value) || 0.0;
        
        // Formule : (Note Cours + (Note Composition × 2)) ÷ 3
        const noteFinale = (noteCours + (noteComposition * 2)) / 3;
        
        document.getElementById('note_finale').value = noteFinale.toFixed(2);
    }

    // Écouter les changements sur les champs de notes
    document.getElementById('note_cours').addEventListener('input', calculerNoteFinale);
    document.getElementById('note_composition').addEventListener('input', calculerNoteFinale);

    // Calcul initial
    calculerNoteFinale();

    // Confirmation de suppression
    function confirmDelete() {
        if (confirm('Êtes-vous sûr de vouloir supprimer cette note ? Cette action est irréversible.')) {
            document.getElementById('delete-form').submit();
        }
    }
</script>
@endsection
