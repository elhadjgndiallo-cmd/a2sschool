@extends('layouts.app')

@section('title', 'Saisir Tests Mensuels - ' . $classe->nom)

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-edit me-2"></i>
        Saisir Tests Mensuels - {{ $classe->nom }}
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        @if(auth()->user()->isTeacher())
            <a href="{{ route('teacher.classes') }}" 
               class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Retour aux Classes
            </a>
        @else
            <a href="{{ route('notes.mensuel.classe', $classe->id) }}?mois={{ $mois }}&annee={{ $annee }}" 
               class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Retour
            </a>
        @endif
    </div>
</div>

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- Filtres -->
<div class="card mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">
            <i class="fas fa-filter me-2"></i>
            Période
        </h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('notes.mensuel.saisir', $classe->id) }}">
            <div class="row">
                <div class="col-md-4">
                    <label for="mois" class="form-label">Mois</label>
                    <select name="mois" id="mois" class="form-select">
                        @foreach($moisListe as $num => $nom)
                        <option value="{{ $num }}" {{ $mois == $num ? 'selected' : '' }}>
                            {{ $nom }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="annee" class="form-label">Année</label>
                    <select name="annee" id="annee" class="form-select">
                        @for($i = date('Y') - 2; $i <= date('Y') + 2; $i++)
                        <option value="{{ $i }}" {{ $annee == $i ? 'selected' : '' }}>
                            {{ $i }}
                        </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i>
                        Changer
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Formulaire de saisie -->
<div class="card">
    <div class="card-header bg-white">
        <h5 class="mb-0">
            <i class="fas fa-edit me-2"></i>
            Saisie des notes - {{ $moisListe[$mois] }} {{ $annee }}
        </h5>
    </div>
    <div class="card-body">
        @if($eleves->count() > 0 && $matieres->count() > 0)
        <form method="POST" action="{{ route('notes.mensuel.store') }}" id="formSaisie">
            @csrf
            <input type="hidden" name="classe_id" value="{{ $classe->id }}">
            <input type="hidden" name="mois" value="{{ $mois }}">
            <input type="hidden" name="annee" value="{{ $annee }}">
            
            <!-- Sélection de l'enseignant et de la matière -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="enseignant_id" class="form-label">Enseignant</label>
                    <select name="enseignant_id" id="enseignant_id" class="form-select" required {{ auth()->user()->isTeacher() ? 'disabled' : '' }}>
                        <option value="">Sélectionner un enseignant</option>
                        @foreach($enseignants as $enseignant)
                        <option value="{{ $enseignant->id }}" 
                                data-matieres="{{ $enseignant->emploisTemps->where('classe_id', $classe->id)->where('actif', true)->pluck('matiere_id')->unique()->toJson() }}"
                                {{ auth()->user()->isTeacher() && auth()->user()->enseignant && auth()->user()->enseignant->id == $enseignant->id ? 'selected' : '' }}>
                            {{ $enseignant->utilisateur->nom }} {{ $enseignant->utilisateur->prenom }}
                        </option>
                        @endforeach
                    </select>
                    @if(auth()->user()->isTeacher())
                        <input type="hidden" name="enseignant_id" value="{{ auth()->user()->enseignant->id }}">
                    @endif
                </div>
                <div class="col-md-4">
                    <label for="matiere_id" class="form-label">Matière</label>
                    <select name="matiere_id" id="matiere_id" class="form-select" required disabled>
                        <option value="">Sélectionner d'abord un enseignant</option>
                        @foreach($matieres as $matiere)
                        <option value="{{ $matiere->id }}" data-coefficient="{{ $matiere->coefficient }}">
                            {{ $matiere->nom }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="coefficient_global" class="form-label">Coefficient</label>
                    <input type="number" 
                           id="coefficient_global" 
                           class="form-control" 
                           min="1" 
                           max="10" 
                           value="1"
                           placeholder="1">
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th class="text-center">Matricule</th>
                            <th class="text-center">Nom</th>
                            <th class="text-center">Prénom</th>
                            <th class="text-center">Matière</th>
                            <th class="text-center">Note</th>
                            <th class="text-center">Coefficient</th>
                        </tr>
                    </thead>
                    <tbody id="eleves-table">
                        @foreach($eleves as $eleve)
                        <tr data-eleve-id="{{ $eleve->id }}">
                            <td class="fw-bold text-center">{{ $eleve->matricule }}</td>
                            <td>{{ $eleve->nom }}</td>
                            <td>{{ $eleve->prenom }}</td>
                            <td class="matiere-nom">-</td>
                            <td>
                                <input type="number" 
                                       name="notes[{{ $eleve->id }}][note]" 
                                       class="form-control form-control-sm text-center note-input" 
                                       min="0" 
                                       max="20" 
                                       step="0.01"
                                       placeholder="0.00"
                                       disabled>
                                <input type="hidden" 
                                       name="notes[{{ $eleve->id }}][eleve_id]" 
                                       value="{{ $eleve->id }}">
                            </td>
                            <td>
                                <input type="number" 
                                       name="notes[{{ $eleve->id }}][coefficient]" 
                                       class="form-control form-control-sm text-center coefficient-input" 
                                       min="1" 
                                       max="10" 
                                       value="1"
                                       placeholder="1"
                                       disabled>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-between mt-4">
                <div class="alert alert-info mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Note :</strong> Une seule note par matière par mois. Les notes existantes seront mises à jour.
                </div>
                <div>
                    <button type="button" class="btn btn-outline-secondary me-2" onclick="resetForm()">
                        <i class="fas fa-undo me-1"></i>
                        Réinitialiser
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i>
                        Enregistrer
                    </button>
                </div>
            </div>
        </form>
        @else
        <div class="text-center py-5">
            <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
            <h5 class="text-muted">Impossible de saisir les notes</h5>
            @if($eleves->count() == 0)
            <p class="text-muted">Aucun élève n'est inscrit dans cette classe.</p>
            @elseif($matieres->count() == 0)
            <p class="text-muted">Aucune matière n'est assignée à cette classe.</p>
            @endif
        </div>
        @endif
    </div>
</div>

<script>
// Initialisation pour les enseignants
document.addEventListener('DOMContentLoaded', function() {
    const enseignantSelect = document.getElementById('enseignant_id');
    if (enseignantSelect.disabled && enseignantSelect.value) {
        // Si l'enseignant est pré-sélectionné (cas des enseignants), déclencher le changement
        enseignantSelect.dispatchEvent(new Event('change'));
    }
});

// Gestion de la sélection d'enseignant
document.getElementById('enseignant_id').addEventListener('change', function() {
    const enseignantId = this.value;
    const matiereSelect = document.getElementById('matiere_id');
    
    if (enseignantId) {
        // Récupérer les matières de l'enseignant sélectionné
        const selectedOption = this.options[this.selectedIndex];
        const matieresEnseignant = JSON.parse(selectedOption.dataset.matieres || '[]');
        
        // Réinitialiser le select des matières
        matiereSelect.innerHTML = '<option value="">Sélectionner une matière</option>';
        
        // Ajouter seulement les matières enseignées par cet enseignant
        const allMatieres = @json($matieres);
        allMatieres.forEach(matiere => {
            if (matieresEnseignant.includes(matiere.id)) {
                const option = document.createElement('option');
                option.value = matiere.id;
                option.textContent = matiere.nom;
                option.dataset.coefficient = matiere.coefficient;
                matiereSelect.appendChild(option);
            }
        });
        
        // Activer le select des matières
        matiereSelect.disabled = false;
    } else {
        // Désactiver le select des matières
        matiereSelect.disabled = true;
        matiereSelect.innerHTML = '<option value="">Sélectionner d\'abord un enseignant</option>';
        
        // Désactiver tous les champs
        const noteInputs = document.querySelectorAll('.note-input');
        const coefficientInputs = document.querySelectorAll('.coefficient-input');
        const matiereNoms = document.querySelectorAll('.matiere-nom');
        
        noteInputs.forEach(input => {
            input.disabled = true;
            input.value = '';
        });
        coefficientInputs.forEach(input => {
            input.disabled = true;
            input.value = '1';
        });
        matiereNoms.forEach(cell => cell.textContent = '-');
    }
});

// Gestion de la sélection de matière
document.getElementById('matiere_id').addEventListener('change', function() {
    const matiereId = this.value;
    const matiereNom = this.options[this.selectedIndex].text;
    const coefficient = this.options[this.selectedIndex].dataset.coefficient || 1;
    
    // Activer/désactiver les champs selon la sélection
    const noteInputs = document.querySelectorAll('.note-input');
    const coefficientInputs = document.querySelectorAll('.coefficient-input');
    const matiereNoms = document.querySelectorAll('.matiere-nom');
    
    if (matiereId) {
        // Activer les champs
        noteInputs.forEach(input => input.disabled = false);
        coefficientInputs.forEach(input => {
            input.disabled = false;
            input.value = coefficient;
        });
        matiereNoms.forEach(cell => cell.textContent = matiereNom);
        
        // Mettre à jour le coefficient global
        document.getElementById('coefficient_global').value = coefficient;
    } else {
        // Désactiver les champs
        noteInputs.forEach(input => {
            input.disabled = true;
            input.value = '';
        });
        coefficientInputs.forEach(input => {
            input.disabled = true;
            input.value = '1';
        });
        matiereNoms.forEach(cell => cell.textContent = '-');
    }
});

// Gestion du coefficient global
document.getElementById('coefficient_global').addEventListener('input', function() {
    const coefficient = this.value;
    const coefficientInputs = document.querySelectorAll('.coefficient-input');
    coefficientInputs.forEach(input => {
        if (!input.disabled) {
            input.value = coefficient;
        }
    });
});

function resetForm() {
    if (confirm('Êtes-vous sûr de vouloir réinitialiser tous les champs ?')) {
        document.getElementById('formSaisie').reset();
        document.getElementById('enseignant_id').dispatchEvent(new Event('change'));
        document.getElementById('matiere_id').dispatchEvent(new Event('change'));
    }
}

// Validation des notes
document.getElementById('formSaisie').addEventListener('submit', function(e) {
    const enseignantId = document.getElementById('enseignant_id').value;
    const matiereId = document.getElementById('matiere_id').value;
    
    if (!enseignantId) {
        e.preventDefault();
        alert('Veuillez sélectionner un enseignant.');
        return;
    }
    
    if (!matiereId) {
        e.preventDefault();
        alert('Veuillez sélectionner une matière.');
        return;
    }
    
    const inputs = document.querySelectorAll('input[name*="[note]"]:not([disabled])');
    let hasError = false;
    let hasNote = false;
    
    inputs.forEach(input => {
        const value = parseFloat(input.value);
        if (input.value !== '') {
            hasNote = true;
            if (isNaN(value) || value < 0 || value > 20) {
                input.classList.add('is-invalid');
                hasError = true;
            } else {
                input.classList.remove('is-invalid');
            }
        } else {
            input.classList.remove('is-invalid');
        }
    });
    
    if (!hasNote) {
        e.preventDefault();
        alert('Veuillez saisir au moins une note.');
        return;
    }
    
    if (hasError) {
        e.preventDefault();
        alert('Veuillez corriger les erreurs dans les notes (0-20).');
    }
});
</script>
@endsection
