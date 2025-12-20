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
                        <option value="trimestre3">Trimestre 3</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="date_evaluation" class="form-label">Date d'évaluation</label>
                    <input type="date" class="form-control" id="date_evaluation" name="date_evaluation" value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <label for="enseignant_id" class="form-label">Enseignant</label>
                    <select class="form-select" id="enseignant_id" name="enseignant_id" required>
                        @if(auth()->user()->isAdmin() || auth()->user()->role === 'personnel_admin')
                            <option value="">Sélectionner un enseignant</option>
                            @foreach($enseignants as $enseignant)
                            <option value="{{ $enseignant->id }}" 
                                    data-matieres="{{ json_encode($enseignant->matieres_classe ?? []) }}">
                                {{ $enseignant->nom_complet }}
                            </option>
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
                            <th width="10%">MATRICULE</th>
                            <th width="15%">PRENOMS</th>
                            <th width="15%">NOM</th>
                            <th width="15%">MATIERE</th>
                            <th width="10%">COEFFICIENT</th>
                            @if(!$classe->isPrimaire())
                            <th width="12%">NOTE COURS</th>
                            @endif
                            <th width="12%">NOTE COMPO</th>
                            <th width="13%">NOTE FINALE</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($classe->eleves as $index => $eleve)
                        <tr data-eleve-id="{{ $eleve->id }}">
                            <td>
                                <strong>{{ $eleve->numero_etudiant }}</strong>
                                <input type="hidden" name="notes[{{ $index }}][eleve_id]" value="{{ $eleve->id }}">
                            </td>
                            <td>
                                <strong>{{ $eleve->utilisateur->prenom ?? '' }}</strong>
                            </td>
                            <td>
                                <strong>{{ $eleve->utilisateur->nom ?? '' }}</strong>
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
                            @if(!$classe->isPrimaire())
                            <td>
                                <input type="number" 
                                       class="form-control note-cours-input" 
                                       name="notes[{{ $index }}][note_cours]" 
                                       min="0" 
                                       max="{{ $classe->note_max }}" 
                                       step="0.25"
                                       placeholder="0.00"
                                       data-index="{{ $index }}">
                            </td>
                            @else
                            <input type="hidden" name="notes[{{ $index }}][note_cours]" value="">
                            @endif
                            <td>
                                <input type="number" 
                                       class="form-control note-composition-input" 
                                       name="notes[{{ $index }}][note_composition]" 
                                       min="0" 
                                       max="{{ $classe->note_max }}" 
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

<!-- Modal pour ajouter une note pour un élève -->
<div class="modal fade" id="addNoteModal" tabindex="-1" aria-labelledby="addNoteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addNoteModalLabel">
                    <i class="fas fa-plus-circle me-2"></i>
                    Ajouter une note pour un élève
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="eleve_select" class="form-label">Sélectionner un élève <span class="text-danger">*</span></label>
                    <select class="form-select" id="eleve_select" required>
                        <option value="">Choisir un élève</option>
                        @foreach($classe->eleves as $eleve)
                            <option value="{{ $eleve->id }}" data-url="{{ route('notes.eleve.create', $eleve->id) }}">
                                {{ $eleve->numero_etudiant }} - {{ $eleve->utilisateur->prenom }} {{ $eleve->utilisateur->nom }}
                            </option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback" id="eleve_select_error" style="display: none;">
                        Veuillez sélectionner un élève
                    </div>
                </div>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Vous serez redirigé vers la page de saisie de note pour l'élève sélectionné.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-success" id="goToAddNote">
                    <i class="fas fa-arrow-right me-1"></i>
                    Continuer
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Récupérer toutes les matières pour le filtrage
    const allMatieres = @json($matieres);
    
    // Fonction pour filtrer les matières selon l'enseignant
    function filtrerMatieres(matieresEnseignant) {
        // Filtrer le select global de matière (Application rapide)
        const matiereGlobale = document.getElementById('matiere_globale');
        if (matiereGlobale) {
            const currentValue = matiereGlobale.value;
            matiereGlobale.innerHTML = '<option value="">Choisir une matière</option>';
            
            if (matieresEnseignant && matieresEnseignant.length > 0) {
                // Ajouter seulement les matières enseignées par cet enseignant
                allMatieres.forEach(matiere => {
                    if (matieresEnseignant.includes(matiere.id)) {
                        const option = document.createElement('option');
                        option.value = matiere.id;
                        option.textContent = matiere.nom;
                        option.dataset.coefficient = matiere.coefficient;
                        matiereGlobale.appendChild(option);
                    }
                });
            } else {
                // Si aucune matière, afficher toutes
                allMatieres.forEach(matiere => {
                    const option = document.createElement('option');
                    option.value = matiere.id;
                    option.textContent = matiere.nom;
                    option.dataset.coefficient = matiere.coefficient;
                    matiereGlobale.appendChild(option);
                });
            }
            
            // Restaurer la valeur précédente si elle existe toujours
            if (currentValue && matieresEnseignant && matieresEnseignant.includes(parseInt(currentValue))) {
                matiereGlobale.value = currentValue;
            }
        }
        
        // Filtrer les matières dans tous les selects de matière du tableau
        document.querySelectorAll('.matiere-select').forEach(function(select) {
            const currentValue = select.value;
            select.innerHTML = '<option value="">Choisir une matière</option>';
            
            if (matieresEnseignant && matieresEnseignant.length > 0) {
                // Ajouter seulement les matières enseignées par cet enseignant
                allMatieres.forEach(matiere => {
                    if (matieresEnseignant.includes(matiere.id)) {
                        const option = document.createElement('option');
                        option.value = matiere.id;
                        option.textContent = matiere.nom;
                        option.dataset.coefficient = matiere.coefficient;
                        select.appendChild(option);
                    }
                });
            } else {
                // Si aucune matière, afficher toutes
                allMatieres.forEach(matiere => {
                    const option = document.createElement('option');
                    option.value = matiere.id;
                    option.textContent = matiere.nom;
                    option.dataset.coefficient = matiere.coefficient;
                    select.appendChild(option);
                });
            }
            
            // Restaurer la valeur précédente si elle existe toujours
            if (currentValue && matieresEnseignant && matieresEnseignant.includes(parseInt(currentValue))) {
                select.value = currentValue;
            }
        });
    }
    
    // Gestion de la sélection d'enseignant
    const enseignantSelect = document.getElementById('enseignant_id');
    if (enseignantSelect) {
        enseignantSelect.addEventListener('change', function() {
            const enseignantId = this.value;
            
            if (enseignantId) {
                // Récupérer les matières de l'enseignant sélectionné
                const selectedOption = this.options[this.selectedIndex];
                const matieresEnseignant = JSON.parse(selectedOption.dataset.matieres || '[]');
                
                // Filtrer toutes les matières (tableau + application rapide)
                filtrerMatieres(matieresEnseignant);
            } else {
                // Si aucun enseignant sélectionné, afficher toutes les matières
                filtrerMatieres(null);
            }
        });
        
        // Déclencher le changement si un enseignant est déjà sélectionné
        if (enseignantSelect.value) {
            enseignantSelect.dispatchEvent(new Event('change'));
        }
    }
    
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
        const isPrimaire = {{ $classe->isPrimaire() ? 'true' : 'false' }};
        
        if (noteComposition === null) {
            return null;
        }
        
        // Pour primaire : note finale = note composition
        if (isPrimaire) {
            return noteComposition;
        }
        
        // Pour collège/lycée : (Note Cours + (Note Composition * 2)) / 3
        if (noteCours === null) {
            return noteComposition;
        } else {
            return (noteCours + (noteComposition * 2)) / 3;
        }
    }

    // Calcul automatique de la note finale
    function calculerNoteFinaleLigne(index) {
        const isPrimaire = {{ $classe->isPrimaire() ? 'true' : 'false' }};
        const noteCoursInput = document.querySelector(`.note-cours-input[data-index="${index}"]`);
        const noteCompositionInput = document.querySelector(`.note-composition-input[data-index="${index}"]`);
        const noteFinaleDisplay = document.querySelector(`.note-finale-display[data-index="${index}"]`);
        
        const noteCours = (noteCoursInput && noteCoursInput.value) ? parseFloat(noteCoursInput.value) : null;
        const noteComposition = noteCompositionInput.value ? parseFloat(noteCompositionInput.value) : null;
        
        const noteFinale = calculerNoteFinale(noteCours, noteComposition);
        
        if (noteFinale !== null) {
            noteFinaleDisplay.value = noteFinale.toFixed(2);
        } else {
            noteFinaleDisplay.value = '';
        }
        
        calculerStatistiques();
    }

    // Validation des notes selon le niveau
    const NOTE_MAX_PRIMAIRE_PRESCOLAIRE = 10;
    const NOTE_MAX_COLLEGE_LYCEE = 20;
    const noteMax = isPrimaire ? NOTE_MAX_PRIMAIRE_PRESCOLAIRE : NOTE_MAX_COLLEGE_LYCEE;
    const niveauTexte = isPrimaire ? 'primaire/préscolaire' : 'collège/lycée';
    
    function validerNote(input) {
        const valeur = parseFloat(input.value);
        if (!isNaN(valeur) && valeur > noteMax) {
            input.classList.add('is-invalid');
            const message = `⚠️ Vous avez une note supérieure à ${noteMax}. La note maximale pour le ${niveauTexte} est ${noteMax}.`;
            
            // Afficher le message d'avertissement
            let alertDiv = input.parentElement.querySelector('.note-warning');
            if (!alertDiv) {
                alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-warning alert-sm note-warning mt-1 mb-0';
                alertDiv.style.fontSize = '0.85rem';
                alertDiv.style.padding = '0.25rem 0.5rem';
                input.parentElement.appendChild(alertDiv);
            }
            alertDiv.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i>' + message;
            
            return false;
        } else {
            input.classList.remove('is-invalid');
            const alertDiv = input.parentElement.querySelector('.note-warning');
            if (alertDiv) {
                alertDiv.remove();
            }
            return true;
        }
    }

    // Écouter les changements de notes cours et composition (délégation d'événements)
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('note-cours-input') || e.target.classList.contains('note-composition-input')) {
            const index = e.target.dataset.index;
            
            // Valider la note avant de calculer
            validerNote(e.target);
            
            calculerNoteFinaleLigne(index);
        }
    });
    
    // Validation avant soumission du formulaire
    document.getElementById('notesForm').addEventListener('submit', function(e) {
        let hasError = false;
        const errors = [];
        
        document.querySelectorAll('.note-cours-input, .note-composition-input').forEach(function(input) {
            const valeur = parseFloat(input.value);
            if (!isNaN(valeur) && valeur > noteMax) {
                hasError = true;
                input.classList.add('is-invalid');
                const row = input.closest('tr');
                const eleveNom = row.querySelector('td:nth-child(2)').textContent.trim() + ' ' + row.querySelector('td:nth-child(3)').textContent.trim();
                errors.push(`L'élève ${eleveNom} a une note supérieure à ${noteMax} (${valeur})`);
            }
        });
        
        if (hasError) {
            e.preventDefault();
            let errorMessage = `⚠️ Erreur : Vous avez des notes supérieures à ${noteMax}. La note maximale pour le ${niveauTexte} est ${noteMax}.\n\n`;
            errorMessage += errors.join('\n');
            alert(errorMessage);
            return false;
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
    
    // Gestion de la modal pour ajouter une note pour un élève
    const eleveSelect = document.getElementById('eleve_select');
    const goToAddNoteBtn = document.getElementById('goToAddNote');
    const errorDiv = document.getElementById('eleve_select_error');
    
    // Fonction pour valider et rediriger
    function handleGoToAddNote() {
        const selectedValue = eleveSelect.value;
        
        if (!selectedValue || selectedValue === '') {
            eleveSelect.classList.add('is-invalid');
            if (errorDiv) errorDiv.style.display = 'block';
            eleveSelect.focus();
            return false;
        }
        
        // Trouver l'option sélectionnée
        const selectedOption = eleveSelect.options[eleveSelect.selectedIndex];
        
        if (!selectedOption) {
            alert('Erreur : option non trouvée');
            return false;
        }
        
        // Récupérer l'URL depuis l'attribut data-url
        const url = selectedOption.getAttribute('data-url');
        
        if (!url || url === '') {
            alert('Erreur : URL non trouvée pour cet élève. Veuillez contacter l\'administrateur.');
            console.error('URL manquante pour l\'élève:', selectedValue);
            return false;
        }
        
        // Rediriger vers la page de création de note
        window.location.href = url;
        return true;
    }
    
    if (goToAddNoteBtn && eleveSelect) {
        // Gestion du clic sur le bouton Continuer
        goToAddNoteBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            handleGoToAddNote();
        });
        
        // Gestion de la touche Entrée dans le select
        eleveSelect.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                handleGoToAddNote();
            }
        });
        
        // Réinitialiser l'erreur quand on change de sélection
        eleveSelect.addEventListener('change', function() {
            eleveSelect.classList.remove('is-invalid');
            if (errorDiv) errorDiv.style.display = 'none';
        });
    } else {
        console.error('Éléments de la modal non trouvés:', {
            eleveSelect: !!eleveSelect,
            goToAddNoteBtn: !!goToAddNoteBtn
        });
    }
});
</script>
@endpush
@endsection
