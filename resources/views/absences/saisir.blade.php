@extends('layouts.app')

@section('title', 'Saisir Absences - ' . $classe->nom)

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-user-times me-2"></i>
        Saisir Absences - {{ $classe->nom }}
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('absences.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Retour
        </a>
    </div>
</div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif


@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Erreur:</strong> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Erreurs de validation:</strong>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form method="POST" action="{{ route('absences.store') }}" id="absencesForm">
    @csrf
    <input type="hidden" name="classe_id" value="{{ $classe->id }}">
    
    <!-- Paramètres généraux -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Paramètres de saisie</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <label for="date_absence" class="form-label">Date</label>
                    <input type="date" class="form-control" id="date_absence" name="date_absence_global" value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <label for="matiere_id" class="form-label">Matière (optionnel)</label>
                    <select class="form-select" id="matiere_id" name="matiere_id_global">
                        <option value="">Toutes les matières</option>
                        @foreach($matieres as $matiere)
                        <option value="{{ $matiere->id }}">{{ $matiere->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="heure_debut" class="form-label">Heure début</label>
                    <input type="time" class="form-control" id="heure_debut" name="heure_debut_global" value="08:00">
                </div>
                <div class="col-md-3">
                    <label for="heure_fin" class="form-label">Heure fin</label>
                    <input type="time" class="form-control" id="heure_fin" name="heure_fin_global" value="14:15">
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau de saisie des absences -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Liste des élèves</h5>
            <div>
                <button type="button" class="btn btn-sm btn-success" id="marquer-tous-presents">
                    <i class="fas fa-check me-1"></i>
                    Tous présents
                </button>
                <button type="button" class="btn btn-sm btn-warning" id="marquer-tous-absents">
                    <i class="fas fa-times me-1"></i>
                    Tous absents
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="absencesTable">
                    <thead class="table-dark">
                        <tr>
                            <th width="5%">#</th>
                            <th width="25%">Élève</th>
                            <th width="15%">Statut</th>
                            <th width="15%">Type d'absence</th>
                            <th width="20%">Motif</th>
                            <th width="20%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($classe->eleves as $index => $eleve)
                        <tr data-eleve-id="{{ $eleve->id }}">
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-2">
                                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 40px; height: 40px;">
                                            {{ substr($eleve->utilisateur->prenom, 0, 1) }}{{ substr($eleve->utilisateur->nom, 0, 1) }}
                                        </div>
                                    </div>
                                    <div>
                                        <strong>{{ $eleve->utilisateur->nom }} {{ $eleve->utilisateur->prenom }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $eleve->numero_etudiant }}</small>
                                    </div>
                                </div>
                                <input type="hidden" name="absences[{{ $index }}][eleve_id]" value="{{ $eleve->id }}">
                                <input type="hidden" name="absences[{{ $index }}][date_absence]" value="{{ date('Y-m-d') }}" class="date-input">
                                <input type="hidden" name="absences[{{ $index }}][heure_debut]" value="08:00" class="heure-debut-input">
                                <input type="hidden" name="absences[{{ $index }}][heure_fin]" value="14:15" class="heure-fin-input">
                            </td>
                            <td>
                                <div class="form-check form-switch">
                                    <!-- Champ caché simple qui sera mis à jour par JavaScript -->
                                    <input type="hidden" name="absences[{{ $index }}][present]" value="1" class="present-hidden" data-index="{{ $index }}">
                                    <input class="form-check-input presence-toggle" 
                                           type="checkbox" 
                                           id="present_{{ $index }}" 
                                           value="1" 
                                           checked
                                           data-index="{{ $index }}">
                                    <label class="form-check-label" for="present_{{ $index }}">
                                        <span class="badge bg-success present-label">Présent</span>
                                        <span class="badge bg-danger absent-label d-none">Absent</span>
                                    </label>
                                </div>
                            </td>
                            <td>
                                <select class="form-select form-select-sm type-absence" 
                                        name="absences[{{ $index }}][type]" 
                                        data-index="{{ $index }}" 
                                        disabled>
                                    <option value="absence">Absence</option>
                                    <option value="retard">Retard</option>
                                    <option value="sortie_anticipee">Sortie anticipée</option>
                                </select>
                            </td>
                            <td>
                                <input type="text" 
                                       class="form-control form-control-sm motif-input" 
                                       name="absences[{{ $index }}][motif]" 
                                       placeholder="Motif (optionnel)..."
                                       data-index="{{ $index }}"
                                       disabled>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" 
                                            class="btn btn-outline-info btn-sm historique-btn" 
                                            data-eleve-id="{{ $eleve->id }}"
                                            title="Voir l'historique">
                                        <i class="fas fa-history"></i>
                                    </button>
                                    <button type="button" 
                                            class="btn btn-outline-warning btn-sm notifier-btn" 
                                            data-eleve-id="{{ $eleve->id }}"
                                            title="Notifier les parents"
                                            disabled>
                                        <i class="fas fa-bell"></i>
                                    </button>
                                </div>
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
            <h5 class="mb-0">Résumé</h5>
        </div>
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-3">
                    <h4 class="text-success" id="nb-presents">{{ $classe->eleves->count() }}</h4>
                    <small>Présents</small>
                </div>
                <div class="col-md-3">
                    <h4 class="text-danger" id="nb-absents">0</h4>
                    <small>Absents</small>
                </div>
                <div class="col-md-3">
                    <h4 class="text-warning" id="nb-retards">0</h4>
                    <small>Retards</small>
                </div>
                <div class="col-md-3">
                    <h4 class="text-info" id="nb-sorties">0</h4>
                    <small>Sorties anticipées</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Boutons d'action -->
    <div class="card mt-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <button type="button" class="btn btn-info" id="appliquer-parametres">
                        <i class="fas fa-sync me-2"></i>
                        Appliquer Paramètres
                    </button>
                </div>
                <div class="col-md-6 text-end">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fas fa-save me-2"></i>
                        Enregistrer les Absences
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Absences déjà saisies aujourd'hui -->
@if($absencesAujourdhui->count() > 0)
<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0">Absences déjà saisies aujourd'hui</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Élève</th>
                        <th>Matière</th>
                        <th>Type</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($absencesAujourdhui as $absence)
                    <tr>
                        <td>{{ $absence->eleve->utilisateur->nom }} {{ $absence->eleve->utilisateur->prenom }}</td>
                        <td>{{ $absence->matiere ? $absence->matiere->nom : 'Toutes' }}</td>
                        <td>
                            <span class="badge bg-warning">{{ ucfirst($absence->type) }}</span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $absence->statut == 'justifiee' ? 'success' : 'danger' }}">
                                {{ ucfirst(str_replace('_', ' ', $absence->statut)) }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('absences.eleve', $absence->eleve_id) }}" class="btn btn-sm btn-outline-info">
                                Voir détails
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion des toggles de présence
    document.querySelectorAll('.presence-toggle').forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            const index = this.dataset.index;
            const row = this.closest('tr');
            const typeSelect = row.querySelector('.type-absence');
            const motifInput = row.querySelector('.motif-input');
            const notifierBtn = row.querySelector('.notifier-btn');
            const presentLabel = row.querySelector('.present-label');
            const absentLabel = row.querySelector('.absent-label');
            
            // Trouver le champ caché par son data-index (plusieurs méthodes)
            let presentHidden = document.querySelector(`input.present-hidden[data-index="${index}"]`);
            
            // Si pas trouvé, essayer de le trouver dans la même ligne
            if (!presentHidden) {
                presentHidden = row.querySelector(`input[name="absences[${index}][present]"]`);
            }
            
            // Si toujours pas trouvé, essayer par nom
            if (!presentHidden) {
                presentHidden = document.querySelector(`input[name="absences[${index}][present]"]`);
            }
            

            if (this.checked) {
                // Présent
                typeSelect.disabled = true;
                motifInput.disabled = true;
                notifierBtn.disabled = true;
                presentLabel.classList.remove('d-none');
                absentLabel.classList.add('d-none');
                // Mettre la valeur à 1 quand présent
                if (presentHidden) {
                    presentHidden.value = '1';
                }
                row.classList.remove('table-warning');
            } else {
                // Absent
                typeSelect.disabled = false;
                motifInput.disabled = false;
                notifierBtn.disabled = false;
                presentLabel.classList.add('d-none');
                absentLabel.classList.remove('d-none');
                // Mettre la valeur à 0 quand absent
                if (presentHidden) {
                    presentHidden.value = '0';
                }
                row.classList.add('table-warning');
            }
            
            // S'assurer que le champ type est toujours envoyé (même si disabled)
            // Créer un champ caché pour le type si nécessaire
            let typeHidden = row.querySelector(`input[name="absences[${index}][type_hidden]"]`);
            if (!typeHidden) {
                typeHidden = document.createElement('input');
                typeHidden.type = 'hidden';
                typeHidden.name = `absences[${index}][type_hidden]`;
                typeHidden.value = typeSelect.value || 'absence';
                row.appendChild(typeHidden);
            }
            typeHidden.value = typeSelect.value || 'absence';
            
            calculerResume();
        });
    });

    // Marquer tous présents
    document.getElementById('marquer-tous-presents').addEventListener('click', function() {
        document.querySelectorAll('.presence-toggle').forEach(function(toggle) {
            toggle.checked = true;
            toggle.dispatchEvent(new Event('change'));
        });
    });

    // Marquer tous absents
    document.getElementById('marquer-tous-absents').addEventListener('click', function() {
        if (confirm('Êtes-vous sûr de marquer tous les élèves comme absents ?')) {
            document.querySelectorAll('.presence-toggle').forEach(function(toggle) {
                toggle.checked = false;
                toggle.dispatchEvent(new Event('change'));
            });
        }
    });

    // Appliquer les paramètres globaux
    document.getElementById('appliquer-parametres').addEventListener('click', function() {
        const dateAbsence = document.getElementById('date_absence').value;
        const matiereId = document.getElementById('matiere_id').value;
        const heureDebut = document.getElementById('heure_debut').value;
        const heureFin = document.getElementById('heure_fin').value;

        // Appliquer à toutes les lignes
        document.querySelectorAll('.date-input').forEach(function(input) {
            input.value = dateAbsence;
        });

        // Appliquer les heures à toutes les lignes
        document.querySelectorAll('.heure-debut-input').forEach(function(input) {
            input.value = heureDebut;
        });

        document.querySelectorAll('.heure-fin-input').forEach(function(input) {
            input.value = heureFin;
        });

        alert('Paramètres appliqués à toutes les lignes');
    });


    // Calculer le résumé
    function calculerResume() {
        let presents = 0;
        let absents = 0;
        let retards = 0;
        let sorties = 0;

        document.querySelectorAll('.presence-toggle').forEach(function(toggle) {
            const row = toggle.closest('tr');
            const typeSelect = row.querySelector('.type-absence');

            if (toggle.checked) {
                presents++;
            } else {
                absents++;
                if (typeSelect.value === 'retard') {
                    retards++;
                } else if (typeSelect.value === 'sortie_anticipee') {
                    sorties++;
                }
            }
        });

        document.getElementById('nb-presents').textContent = presents;
        document.getElementById('nb-absents').textContent = absents;
        document.getElementById('nb-retards').textContent = retards;
        document.getElementById('nb-sorties').textContent = sorties;
    }

    // Écouter les changements de type d'absence
    document.querySelectorAll('.type-absence').forEach(function(select) {
        select.addEventListener('change', calculerResume);
    });

    // Historique des absences
    document.querySelectorAll('.historique-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const eleveId = this.dataset.eleveId;
            window.open(`/absences/eleve/${eleveId}`, '_blank');
        });
    });

    // Notifier les parents
    document.querySelectorAll('.notifier-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const eleveId = this.dataset.eleveId;
            
            if (confirm('Êtes-vous sûr de vouloir notifier les parents de cet élève ?')) {
                // Ici vous pouvez implémenter la logique de notification
                // Pour l'instant, on simule une notification
                alert('Notification envoyée aux parents de l\'élève ID: ' + eleveId);
                
                // Marquer le bouton comme utilisé
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-check"></i>';
                this.classList.remove('btn-outline-warning');
                this.classList.add('btn-success');
            }
        });
    });



    // Calculer le résumé initial
    calculerResume();
});
</script>
@endpush
@endsection
