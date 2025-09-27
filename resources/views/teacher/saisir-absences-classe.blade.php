@extends('layouts.app')

@section('title', 'Saisir Absences - ' . $classe->nom)

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-user-times me-2"></i>
        Saisir Absences - {{ $classe->nom }}
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('teacher.absences') }}" class="btn btn-outline-secondary">
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
        <strong>Veuillez corriger les erreurs suivantes :</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form method="POST" action="{{ route('teacher.absences.store') }}" id="absencesForm">
    @csrf
    <input type="hidden" name="classe_id" value="{{ $classe->id }}">
    
    <!-- Paramètres généraux -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Paramètres de l'appel</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <label for="date_absence" class="form-label">Date de l'appel</label>
                    <input type="date" class="form-control" id="date_absence" name="date_absence" 
                           value="{{ old('date_absence', date('Y-m-d')) }}" required>
                </div>
                <div class="col-md-6">
                    <label for="heure_appel" class="form-label">Heure de l'appel</label>
                    <input type="time" class="form-control" id="heure_appel" name="heure_appel" 
                           value="{{ old('heure_appel', date('H:i')) }}">
                </div>
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-magic me-2"></i>
                Actions rapides
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <button type="button" class="btn btn-success btn-block" id="marquer-tous-presents">
                        <i class="fas fa-check-circle me-2"></i>
                        Marquer tous présents
                    </button>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-danger btn-block" id="marquer-tous-absents">
                        <i class="fas fa-times-circle me-2"></i>
                        Marquer tous absents
                    </button>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-warning btn-block" id="justifier-tous">
                        <i class="fas fa-check me-2"></i>
                        Justifier tous les absents
                    </button>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-outline-secondary btn-block" id="reset-form">
                        <i class="fas fa-undo me-2"></i>
                        Réinitialiser
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau de saisie des absences -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Tableau de saisie des absences</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="absencesTable">
                    <thead class="table-dark">
                        <tr>
                            <th width="8%">Photo</th>
                            <th width="12%">Matricule</th>
                            <th width="15%">Nom</th>
                            <th width="15%">Prénom</th>
                            <th width="10%">Présent</th>
                            <th width="10%">Justifié</th>
                            <th width="30%">Motif d'absence</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($classe->eleves as $index => $eleve)
                        <tr data-eleve-id="{{ $eleve->id }}">
                            <td class="text-center">
                                @if($eleve->utilisateur->photo_profil)
                                    <img src="{{ Storage::url($eleve->utilisateur->photo_profil) }}" 
                                         alt="Photo de {{ $eleve->utilisateur->prenom }} {{ $eleve->utilisateur->nom }}" 
                                         class="rounded-circle" 
                                         style="width: 40px; height: 40px; object-fit: cover;">
                                @else
                                    <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center" 
                                         style="width: 40px; height: 40px;">
                                        <i class="fas fa-user text-muted"></i>
                                    </div>
                                @endif
                                <input type="hidden" name="absences[{{ $index }}][eleve_id]" value="{{ $eleve->id }}">
                            </td>
                            <td>
                                <strong>{{ $eleve->numero_etudiant }}</strong>
                            </td>
                            <td>
                                <strong>{{ $eleve->utilisateur->nom }}</strong>
                            </td>
                            <td>
                                <strong>{{ $eleve->utilisateur->prenom }}</strong>
                            </td>
                            <td class="text-center">
                                <select class="form-select present-select" 
                                        name="absences[{{ $index }}][present]" 
                                        data-index="{{ $index }}"
                                        style="min-width: 100px;">
                                    <option value="1" selected>Présent</option>
                                    <option value="0">Absent</option>
                                </select>
                            </td>
                            <td class="text-center">
                                <div class="form-check form-switch">
                                    <input class="form-check-input justifie-checkbox" 
                                           type="checkbox" 
                                           name="absences[{{ $index }}][justifie]" 
                                           value="1" 
                                           id="justifie_{{ $index }}"
                                           data-index="{{ $index }}"
                                           disabled>
                                    <label class="form-check-label" for="justifie_{{ $index }}">
                                        <span class="justifie-label">Justifié</span>
                                    </label>
                                </div>
                            </td>
                            <td>
                                <textarea class="form-control motif-textarea" 
                                          name="absences[{{ $index }}][motif]" 
                                          rows="2" 
                                          placeholder="Motif de l'absence..."
                                          data-index="{{ $index }}"
                                          disabled></textarea>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Résumé -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">Résumé de l'appel</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="text-center">
                        <h4 class="text-success" id="total-presents">{{ $classe->eleves->count() }}</h4>
                        <small>Présents</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h4 class="text-danger" id="total-absents">0</h4>
                        <small>Absents</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h4 class="text-warning" id="total-justifies">0</h4>
                        <small>Justifiés</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h4 class="text-info" id="total-eleves">{{ $classe->eleves->count() }}</h4>
                        <small>Total élèves</small>
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
                    <button type="button" class="btn btn-info" id="calculer-resume">
                        <i class="fas fa-calculator me-2"></i>
                        Calculer le résumé
                    </button>
                </div>
                <div class="col-md-6 text-end">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fas fa-save me-2"></i>
                        Enregistrer les absences
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion des select "Présent/Absent"
    document.querySelectorAll('.present-select').forEach(function(select) {
        select.addEventListener('change', function() {
            const index = this.dataset.index;
            const justifieCheckbox = document.querySelector(`#justifie_${index}`);
            const motifTextarea = document.querySelector(`.motif-textarea[data-index="${index}"]`);
            
            if (this.value === '1') {
                // Élève présent
                justifieCheckbox.disabled = true;
                justifieCheckbox.checked = false;
                motifTextarea.disabled = true;
                motifTextarea.value = '';
            } else {
                // Élève absent
                justifieCheckbox.disabled = false;
                motifTextarea.disabled = false;
            }
            
            calculerResume();
        });
    });

    // Debug: afficher les valeurs qui seront envoyées
    document.getElementById('absencesForm').addEventListener('submit', function(e) {
        console.log('Valeurs qui seront envoyées:');
        document.querySelectorAll('select[name*="[present]"]').forEach(function(select) {
            console.log(select.name + ' = ' + select.value);
        });
    });

    // Gestion des cases à cocher "Justifié"
    document.querySelectorAll('.justifie-checkbox').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            calculerResume();
        });
    });

    // Fonction pour calculer le résumé
    function calculerResume() {
        const totalEleves = document.querySelectorAll('.present-select').length;
        let presents = 0;
        let absents = 0;
        
        document.querySelectorAll('.present-select').forEach(function(select) {
            if (select.value === '1') {
                presents++;
            } else {
                absents++;
            }
        });
        
        const justifies = document.querySelectorAll('.justifie-checkbox:checked').length;

        document.getElementById('total-presents').textContent = presents;
        document.getElementById('total-absents').textContent = absents;
        document.getElementById('total-justifies').textContent = justifies;
        document.getElementById('total-eleves').textContent = totalEleves;
    }

    // Marquer tous présents
    document.getElementById('marquer-tous-presents').addEventListener('click', function() {
        document.querySelectorAll('.present-select').forEach(function(select) {
            select.value = '1';
            select.dispatchEvent(new Event('change'));
        });
        alert('Tous les élèves ont été marqués comme présents');
    });

    // Marquer tous absents
    document.getElementById('marquer-tous-absents').addEventListener('click', function() {
        document.querySelectorAll('.present-select').forEach(function(select) {
            select.value = '0';
            select.dispatchEvent(new Event('change'));
        });
        alert('Tous les élèves ont été marqués comme absents');
    });

    // Justifier tous les absents
    document.getElementById('justifier-tous').addEventListener('click', function() {
        document.querySelectorAll('.justifie-checkbox:not([disabled])').forEach(function(checkbox) {
            checkbox.checked = true;
        });
        calculerResume();
        alert('Tous les absents ont été justifiés');
    });

    // Réinitialiser le formulaire
    document.getElementById('reset-form').addEventListener('click', function() {
        if (confirm('Êtes-vous sûr de vouloir réinitialiser toutes les données ?')) {
            document.getElementById('absencesForm').reset();
            document.querySelectorAll('.present-select').forEach(function(select) {
                select.value = '1';
                select.dispatchEvent(new Event('change'));
            });
            calculerResume();
        }
    });

    // Calculer le résumé
    document.getElementById('calculer-resume').addEventListener('click', function() {
        calculerResume();
        alert('Résumé calculé');
    });

    // Calcul initial
    calculerResume();
});
</script>
@endpush

@push('styles')
<style>
.btn-block { width: 100%; }
.present-select {
    border: 2px solid #dee2e6;
    transition: all 0.3s ease;
}
.present-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}
.present-select option[value="1"] {
    background-color: #d1e7dd;
    color: #0f5132;
}
.present-select option[value="0"] {
    background-color: #f8d7da;
    color: #842029;
}
.justifie-checkbox:checked + .form-check-label .justifie-label {
    color: #ffc107;
    font-weight: bold;
}
</style>
@endpush
@endsection

