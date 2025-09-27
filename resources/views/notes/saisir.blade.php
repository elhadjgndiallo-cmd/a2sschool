@extends('layouts.app')

@section('title', 'Saisir Notes - ' . $classe->nom)

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-edit me-2"></i>
        Saisir Notes - {{ $classe->nom }}
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('notes.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Retour
        </a>
    </div>
</div>

<!-- Messages de session -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Veuillez remplir tous les champs obligatoires</strong>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form method="POST" action="{{ route('notes.store') }}" id="notesForm">
    @csrf
    <input type="hidden" name="classe_id" value="{{ $classe->id }}">
    
    <!-- Paramètres généraux -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Paramètres de l'évaluation</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <label for="type_evaluation" class="form-label">Type d'évaluation</label>
                    <select class="form-select" id="type_evaluation" name="type_evaluation">
                        <option value="devoir">Devoir</option>
                        <option value="controle">Contrôle</option>
                        <option value="examen">Examen</option>
                        <option value="oral">Oral</option>
                        <option value="tp">TP</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="periode" class="form-label">Période</label>
                    <select class="form-select" id="periode" name="periode">
                        <option value="trimestre1">Trimestre 1</option>
                        <option value="trimestre2">Trimestre 2</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="date_evaluation" class="form-label">Date d'évaluation</label>
                    <input type="date" class="form-control" id="date_evaluation" name="date_evaluation" value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <label for="enseignant_id" class="form-label">Enseignant</label>
                    <select class="form-select" id="enseignant_id" name="enseignant_id">
                        @if(auth()->user()->isAdmin())
                            <option value="">Sélectionner un enseignant</option>
                            @foreach($enseignants as $enseignant)
                            <option value="{{ $enseignant->id }}">{{ $enseignant->nom_complet }}</option>
                            @endforeach
                        @else
                            @foreach($enseignants as $enseignant)
                            <option value="{{ $enseignant->id }}" selected>{{ $enseignant->nom_complet }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Sélection globale de matière -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-magic me-2"></i>
                Application rapide
            </h5>
        </div>
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-4">
                    <label for="matiere_globale" class="form-label">Matière pour tous les élèves</label>
                    <select class="form-select" id="matiere_globale">
                        <option value="">Choisir une matière</option>
                        @foreach($matieres as $matiere)
                        <option value="{{ $matiere->id }}" data-coefficient="{{ $matiere->coefficient }}">
                            {{ $matiere->nom }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="coefficient_global" class="form-label">Coefficient</label>
                    <input type="number" class="form-control" id="coefficient_global" min="1" max="10" step="1" placeholder="Coeff.">
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-primary" id="appliquer-matiere-tous">
                        <i class="fas fa-magic me-2"></i>
                        Appliquer à tous
                    </button>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-outline-secondary" id="vider-tous">
                        <i class="fas fa-eraser me-2"></i>
                        Vider tous
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau de saisie des notes -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Tableau de saisie des notes</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="notesTable">
                    <thead class="table-dark">
                        <tr>
                            <th width="5%">#</th>
                            <th width="20%">Élève</th>
                            <th width="15%">Matière</th>
                            <th width="8%">
                                Coefficient
                                <i class="fas fa-info-circle text-info ms-1" 
                                   data-bs-toggle="tooltip" 
                                   title="Vous pouvez personnaliser le coefficient pour chaque matière"></i>
                            </th>
                            <th width="12%">Note Cours (/20)</th>
                            <th width="12%">Note Composition (/20)</th>
                            <th width="10%">Note Finale</th>
                            <th width="10%">Commentaire</th>
                            <th width="8%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($classe->eleves as $index => $eleve)
                        <tr data-eleve-id="{{ $eleve->id }}">
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $eleve->nom_complet }}</strong>
                                <br>
                                <small class="text-muted">{{ $eleve->numero_etudiant }}</small>
                                <input type="hidden" name="notes[{{ $index }}][eleve_id]" value="{{ $eleve->id }}">
                            </td>
                            <td>
                                <select class="form-select matiere-select" name="notes[{{ $index }}][matiere_id]" data-index="{{ $index }}">
                                    <option value="">Choisir une matière</option>
                                    @foreach($matieres as $matiere)
                                    <option value="{{ $matiere->id }}" data-coefficient="{{ $matiere->coefficient }}">
                                        {{ $matiere->nom }}
                                    </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="number" 
                                       class="form-control coefficient-input" 
                                       name="notes[{{ $index }}][coefficient]" 
                                       data-index="{{ $index }}"
                                       min="1" 
                                       max="10" 
                                       step="1"
                                       placeholder="Coeff."
                                       style="width: 80px;">
                            </td>
                            <td>
                                <input type="number" 
                                       class="form-control note-cours-input" 
                                       name="notes[{{ $index }}][note_cours]" 
                                       min="0" 
                                       max="20" 
                                       step="0.25"
                                       placeholder="0.00"
                                       data-index="{{ $index }}">
                            </td>
                            <td>
                                <input type="number" 
                                       class="form-control note-composition-input" 
                                       name="notes[{{ $index }}][note_composition]" 
                                       min="0" 
                                       max="20" 
                                       step="0.25"
                                       placeholder="0.00"
                                       data-index="{{ $index }}">
                            </td>
                            <td>
                                <input type="number" 
                                       class="form-control note-finale-display" 
                                       readonly
                                       placeholder="Calculée"
                                       data-index="{{ $index }}"
                                       style="background-color: #f8f9fa;">
                            </td>
                            <td>
                                <input type="text" 
                                       class="form-control" 
                                       name="notes[{{ $index }}][commentaire]" 
                                       placeholder="Commentaire..."
                                       style="font-size: 0.85rem;">
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-success calculer-note-finale" data-index="{{ $index }}">
                                    <i class="fas fa-calculator"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Résumé et moyennes -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">Résumé et Moyennes de Classe</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="text-center">
                        <h4 class="text-primary" id="moyenne-classe">-</h4>
                        <small>Moyenne de classe</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h4 class="text-success" id="note-max">-</h4>
                        <small>Note maximale</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h4 class="text-danger" id="note-min">-</h4>
                        <small>Note minimale</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h4 class="text-info" id="nb-notes">0</h4>
                        <small>Notes saisies</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Boutons d'action -->
    <div class="card mt-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <button type="button" class="btn btn-info" id="calculer-tout">
                        <i class="fas fa-calculator me-2"></i>
                        Calculer Toutes les Moyennes
                    </button>
                    <button type="button" class="btn btn-warning" id="reset-form">
                        <i class="fas fa-undo me-2"></i>
                        Réinitialiser
                    </button>
                </div>
                <div class="col-md-6 text-end">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fas fa-save me-2"></i>
                        Enregistrer les Notes
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion du changement de matière
    document.querySelectorAll('.matiere-select').forEach(function(select) {
        select.addEventListener('change', function() {
            const index = this.dataset.index;
            const selectedOption = this.options[this.selectedIndex];
            const coefficient = selectedOption.dataset.coefficient || 1;
            
            // Pré-remplir le coefficient (l'admin peut le modifier)
            document.querySelector(`.coefficient-input[data-index="${index}"]`).value = coefficient;
        });
    });

    // Activer les tooltips Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Fonction pour calculer la note finale selon la formule
    function calculerNoteFinale(noteCours, noteComposition) {
        if (noteCours === null && noteComposition === null) {
            return null;
        }
        
        if (noteCours === null) {
            return noteComposition;
        } else if (noteComposition === null) {
            return noteCours;
        } else {
            // Formule: (NotesCours + (NotesComposition * 2) / 3)
            return (noteCours + (noteComposition * 2)) / 3;
        }
    }

    // Calcul automatique de la note finale
    function calculerNoteFinaleLigne(index) {
        const noteCoursInput = document.querySelector(`.note-cours-input[data-index="${index}"]`);
        const noteCompositionInput = document.querySelector(`.note-composition-input[data-index="${index}"]`);
        const noteFinaleDisplay = document.querySelector(`.note-finale-display[data-index="${index}"]`);
        
        const noteCours = noteCoursInput.value ? parseFloat(noteCoursInput.value) : null;
        const noteComposition = noteCompositionInput.value ? parseFloat(noteCompositionInput.value) : null;
        
        const noteFinale = calculerNoteFinale(noteCours, noteComposition);
        
        if (noteFinale !== null) {
            noteFinaleDisplay.value = noteFinale.toFixed(2);
        } else {
            noteFinaleDisplay.value = '';
        }
        
        calculerStatistiques();
    }

    // Écouter les changements de notes cours et composition (délégation d'événements)
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('note-cours-input') || e.target.classList.contains('note-composition-input')) {
            const index = e.target.dataset.index;
            calculerNoteFinaleLigne(index);
        }
    });

    // Calcul automatique des statistiques
    function calculerStatistiques() {
        const notesFinales = [];
        document.querySelectorAll('.note-finale-display').forEach(function(input) {
            if (input.value && !isNaN(input.value)) {
                notesFinales.push(parseFloat(input.value));
            }
        });

        if (notesFinales.length > 0) {
            const moyenne = (notesFinales.reduce((a, b) => a + b, 0) / notesFinales.length).toFixed(2);
            const noteMax = Math.max(...notesFinales).toFixed(2);
            const noteMin = Math.min(...notesFinales).toFixed(2);

            document.getElementById('moyenne-classe').textContent = moyenne;
            document.getElementById('note-max').textContent = noteMax;
            document.getElementById('note-min').textContent = noteMin;
            document.getElementById('nb-notes').textContent = notesFinales.length;
        } else {
            document.getElementById('moyenne-classe').textContent = '-';
            document.getElementById('note-max').textContent = '-';
            document.getElementById('note-min').textContent = '-';
            document.getElementById('nb-notes').textContent = '0';
        }
    }

    // Gestion des boutons de calcul individuel (délégation d'événements)
    document.addEventListener('click', function(e) {
        if (e.target.closest('.calculer-note-finale')) {
            const button = e.target.closest('.calculer-note-finale');
            const index = button.dataset.index;
            calculerNoteFinaleLigne(index);
        }
    });

    // Appliquer les paramètres globaux
    document.getElementById('calculer-tout').addEventListener('click', function() {
        const typeEval = document.getElementById('type_evaluation').value;
        const periode = document.getElementById('periode').value;
        const dateEval = document.getElementById('date_evaluation').value;
        const enseignantId = document.getElementById('enseignant_id').value;

        // Appliquer à toutes les lignes
        document.querySelectorAll('tbody tr').forEach(function(row, index) {
            row.querySelector(`input[name="notes[${index}][type_evaluation]"]`)?.setAttribute('value', typeEval);
            row.querySelector(`input[name="notes[${index}][periode]"]`)?.setAttribute('value', periode);
            row.querySelector(`input[name="notes[${index}][date_evaluation]"]`)?.setAttribute('value', dateEval);
            row.querySelector(`input[name="notes[${index}][enseignant_id]"]`)?.setAttribute('value', enseignantId);
        });

        // Recalculer toutes les notes finales
        document.querySelectorAll('.note-finale-display').forEach(function(input) {
            const index = input.dataset.index;
            calculerNoteFinaleLigne(index);
        });

        alert('Paramètres appliqués à toutes les lignes et notes finales recalculées');
    });

    // Réinitialiser le formulaire
    document.getElementById('reset-form').addEventListener('click', function() {
        if (confirm('Êtes-vous sûr de vouloir réinitialiser toutes les notes ?')) {
            document.getElementById('notesForm').reset();
            calculerStatistiques();
        }
    });

    // Ajouter les champs cachés pour les paramètres globaux
    document.getElementById('notesForm').addEventListener('submit', function(e) {
        const typeEval = document.getElementById('type_evaluation').value;
        const periode = document.getElementById('periode').value;
        const dateEval = document.getElementById('date_evaluation').value;
        const enseignantId = document.getElementById('enseignant_id').value;

        document.querySelectorAll('tbody tr').forEach(function(row, index) {
            // Ajouter les champs cachés si ils n'existent pas
            if (!row.querySelector(`input[name="notes[${index}][type_evaluation]"]`)) {
                const typeInput = document.createElement('input');
                typeInput.type = 'hidden';
                typeInput.name = `notes[${index}][type_evaluation]`;
                typeInput.value = typeEval;
                row.appendChild(typeInput);
            }

            if (!row.querySelector(`input[name="notes[${index}][periode]"]`)) {
                const periodeInput = document.createElement('input');
                periodeInput.type = 'hidden';
                periodeInput.name = `notes[${index}][periode]`;
                periodeInput.value = periode;
                row.appendChild(periodeInput);
            }

            if (!row.querySelector(`input[name="notes[${index}][date_evaluation]"]`)) {
                const dateInput = document.createElement('input');
                dateInput.type = 'hidden';
                dateInput.name = `notes[${index}][date_evaluation]`;
                dateInput.value = dateEval;
                row.appendChild(dateInput);
            }

            if (!row.querySelector(`input[name="notes[${index}][enseignant_id]"]`)) {
                const enseignantInput = document.createElement('input');
                enseignantInput.type = 'hidden';
                enseignantInput.name = `notes[${index}][enseignant_id]`;
                enseignantInput.value = enseignantId;
                row.appendChild(enseignantInput);
            }

            // Ajouter le champ titre si nécessaire
            if (!row.querySelector(`input[name="notes[${index}][titre]"]`)) {
                const titreInput = document.createElement('input');
                titreInput.type = 'hidden';
                titreInput.name = `notes[${index}][titre]`;
                titreInput.value = '';
                row.appendChild(titreInput);
            }
        });
    });

    // Application rapide de matière à tous les élèves
    document.getElementById('appliquer-matiere-tous').addEventListener('click', function() {
        const matiereGlobale = document.getElementById('matiere_globale');
        const coefficientGlobal = document.getElementById('coefficient_global');
        
        if (!matiereGlobale.value) {
            alert('Veuillez sélectionner une matière');
            return;
        }
        
        if (!coefficientGlobal.value || coefficientGlobal.value < 1) {
            alert('Veuillez saisir un coefficient valide (minimum 1)');
            return;
        }
        
        // Appliquer la matière et le coefficient à tous les élèves
        document.querySelectorAll('.matiere-select').forEach(function(select) {
            select.value = matiereGlobale.value;
            
            // Déclencher l'événement change pour mettre à jour le coefficient
            const event = new Event('change');
            select.dispatchEvent(event);
        });
        
        // Appliquer le coefficient global
        document.querySelectorAll('.coefficient-input').forEach(function(input) {
            input.value = coefficientGlobal.value;
        });
        
        alert(`Matière "${matiereGlobale.options[matiereGlobale.selectedIndex].text}" appliquée à tous les élèves avec le coefficient ${coefficientGlobal.value}`);
    });
    
    // Vider tous les champs
    document.getElementById('vider-tous').addEventListener('click', function() {
        if (confirm('Êtes-vous sûr de vouloir vider tous les champs de saisie ?')) {
            // Vider les sélecteurs de matière
            document.querySelectorAll('.matiere-select').forEach(function(select) {
                select.value = '';
            });
            
            // Vider les coefficients
            document.querySelectorAll('.coefficient-input').forEach(function(input) {
                input.value = '';
            });
            
            // Vider les notes
            document.querySelectorAll('.note-cours-input').forEach(function(input) {
                input.value = '';
            });
            
            document.querySelectorAll('.note-composition-input').forEach(function(input) {
                input.value = '';
            });
            
            // Vider les notes finales
            document.querySelectorAll('.note-finale-display').forEach(function(input) {
                input.value = '';
            });
            
            // Vider les commentaires
            document.querySelectorAll('.commentaire-input').forEach(function(input) {
                input.value = '';
            });
            
            // Recalculer les statistiques
            calculerStatistiques();
            
            alert('Tous les champs ont été vidés');
        }
    });
    
    // Mise à jour automatique du coefficient global quand on change la matière globale
    document.getElementById('matiere_globale').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const coefficient = selectedOption.dataset.coefficient || 1;
        document.getElementById('coefficient_global').value = coefficient;
    });
});
</script>
@endpush
@endsection
