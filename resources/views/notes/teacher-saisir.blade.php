@extends('layouts.app')

@section('title', 'Saisie des Notes - ' . $classe->nom)

@php
// Debug dans la vue
\Log::error('=== DEBUG VUE TEACHER SAISIR ===');
\Log::error('Matieres reçues dans la vue: ' . $matieres->count());
\Log::error('Matieres dans la vue: ' . $matieres->pluck('nom')->implode(', '));
\Log::error('=== FIN DEBUG VUE ===')
@endphp

@section('content')
<!-- Select d'enseignant caché pour le JavaScript (méthode mensuel/saisir) -->
<input type="hidden" id="enseignant_id" value="{{ $enseignants->first()->id }}" 
       data-matieres="{{ $enseignants->first()->matieres_classe ?? [] }}">

<script>
// Variable contenant les matières filtrées (comme dans mensuel/saisir)
const allMatieres = @json($matieres);
</script>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-edit me-2"></i>
        Saisie des Notes - {{ $classe->nom }}
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
                    <select class="form-select" id="enseignant_id" name="enseignant_id">
                        @foreach($enseignants as $enseignant)
                        <option value="{{ $enseignant->id }}" selected>{{ $enseignant->utilisateur->prenom }} {{ $enseignant->utilisateur->nom }}</option>
                        @endforeach
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
                        <!-- Les options seront ajoutées par JavaScript -->
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

    <!-- Tableau de saisie des notes avec les colonnes demandées -->
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
                            <th width="15%">MATIERES</th>
                            <th width="8%">COEFFICIENT</th>
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
                                <strong>{{ $eleve->utilisateur->prenom }}</strong>
                            </td>
                            <td>
                                <strong>{{ $eleve->utilisateur->nom }}</strong>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fonction pour filtrer les matières selon l'enseignant
    function filtrerMatieres(matieresEnseignant) {
        // Filtrer le select global de matière (Application rapide)
        const matiereGlobale = document.getElementById('matiere_globale');
        if (matiereGlobale) {
            const currentValue = matiereGlobale.value;
            matiereGlobale.innerHTML = '<option value="">Choisir une matière</option>';
            
            // Pour les enseignants, n'afficher que leurs matières
            if (matieresEnseignant && matieresEnseignant.length > 0) {
                allMatieres.forEach(matiere => {
                    if (matieresEnseignant.includes(matiere.id)) {
                        const option = document.createElement('option');
                        option.value = matiere.id;
                        option.textContent = matiere.nom;
                        option.dataset.coefficient = matiere.coefficient;
                        matiereGlobale.appendChild(option);
                    }
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
            
            // Pour les enseignants, n'afficher que leurs matières
            if (matieresEnseignant && matieresEnseignant.length > 0) {
                allMatieres.forEach(matiere => {
                    if (matieresEnseignant.includes(matiere.id)) {
                        const option = document.createElement('option');
                        option.value = matiere.id;
                        option.textContent = matiere.nom;
                        option.dataset.coefficient = matiere.coefficient;
                        select.appendChild(option);
                    }
                });
            }
            
            // Restaurer la valeur précédente si elle existe toujours
            if (currentValue && matieresEnseignant && matieresEnseignant.includes(parseInt(currentValue))) {
                select.value = currentValue;
            }
        });
    }
    
    // Appliquer le filtrage avec les matières de l'enseignant connecté (méthode admin + mensuel)
    const enseignantSelect = document.getElementById('enseignant_id');
    if (enseignantSelect) {
        // Déclencher le changement si un enseignant est déjà sélectionné (logique admin)
        if (enseignantSelect.value) {
            enseignantSelect.dispatchEvent(new Event('change'));
        }
    }
    
    // Écouter les changements sur l'enseignant (comme dans admin + mensuel)
    if (enseignantSelect) {
        enseignantSelect.addEventListener('change', function() {
            const enseignantId = this.value;
            
            if (enseignantId) {
                // Récupérer les matières de l'enseignant sélectionné
                const selectedOption = this.options[this.selectedIndex] || this;
                const matieresEnseignant = JSON.parse(selectedOption.dataset.matieres || '[]');
                
                console.log('=== DEBUG ADMIN + MENSUEL ===');
                console.log('Enseignant ID:', enseignantId);
                console.log('Matières enseignant:', matieresEnseignant);
                console.log('=== FIN DEBUG ===');
                
                // Filtrer toutes les matières (tableau + application rapide)
                filtrerMatieres(matieresEnseignant);
            } else {
                // Si aucun enseignant sélectionné, afficher toutes les matières
                filtrerMatieres(null);
            }
        });
    }
    
    // Gestion du changement de matière
    document.querySelectorAll('.matiere-select').forEach(function(select) {
        select.addEventListener('change', function() {
            const index = this.dataset.index;
            const selectedOption = this.options[this.selectedIndex];
            const coefficient = selectedOption.dataset.coefficient || 1;
            
            // Pré-remplir le coefficient
            document.querySelector(`.coefficient-input[data-index="${index}"]`).value = coefficient;
        });
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

    // Écouter les changements de notes cours et composition
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('note-cours-input') || e.target.classList.contains('note-composition-input')) {
            const index = e.target.dataset.index;
            calculerNoteFinaleLigne(index);
        }
    });
    
    // Pour primaire, écouter aussi les changements de note composition seule
    @if($classe->isPrimaire())
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('note-composition-input')) {
            const index = e.target.dataset.index;
            calculerNoteFinaleLigne(index);
        }
    });
    @endif

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

    // Calculer toutes les moyennes
    document.getElementById('calculer-tout').addEventListener('click', function() {
        document.querySelectorAll('.note-finale-display').forEach(function(input) {
            const index = input.dataset.index;
            calculerNoteFinaleLigne(index);
        });
        alert('Toutes les moyennes ont été calculées');
    });

    // Réinitialiser le formulaire
    document.getElementById('reset-form').addEventListener('click', function() {
        if (confirm('Êtes-vous sûr de vouloir réinitialiser toutes les notes ?')) {
            document.getElementById('notesForm').reset();
            calculerStatistiques();
        }
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
});
</script>
@endpush
@endsection





