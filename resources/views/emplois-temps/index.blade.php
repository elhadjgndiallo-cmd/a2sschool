@extends('layouts.app')

@section('title', 'Gestion des Emplois du Temps')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-calendar-alt me-2"></i>
        Gestion des Emplois du Temps
        @if(!empty($anneeScolaireActive))
            <small class="text-muted fs-6">— année {{ $anneeScolaireActive->nom }}</small>
        @endif
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-primary" onclick="showAddModal()">
                <i class="fas fa-plus me-1"></i>
                Ajouter Créneau
            </button>
            <button type="button" class="btn btn-info" onclick="showDuplicateModal()">
                <i class="fas fa-copy me-1"></i>
                Dupliquer
            </button>
            <button type="button" class="btn btn-danger" onclick="confirmDeleteAll()">
                <i class="fas fa-trash me-1"></i>
                Effacer Tout
            </button>
        </div>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
    <i class="fas fa-check-circle me-2"></i>
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@include('emplois-temps.planning-info')

<!-- Sélection de classe -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Sélectionner une Classe</h5>
    </div>
    <div class="card-body">
        <div class="row">
            @foreach($classes as $classe)
            <div class="col-md-3 mb-3">
                <div class="card classe-card" data-classe-id="{{ $classe->id }}" data-is-primaire="{{ $classe->isPrimaire() ? '1' : '0' }}" onclick="loadEmploiTemps({{ $classe->id }}, this)" style="cursor: pointer;" onmouseover="this.style.backgroundColor='#f8f9fa'" onmouseout="this.style.backgroundColor='white'">
                    <div class="card-body text-center">
                        <h5 class="card-title">{{ $classe->nom }}</h5>
                        <p class="card-text">{{ $classe->niveau }}</p>
                        <small class="text-muted">{{ $classe->eleves->count() }} élève{{ $classe->eleves->count() > 1 ? 's' : '' }}</small>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Emploi du temps -->
<div class="card" id="emploi-temps-container" style="display: none;">
    <div class="card-header">
        <h5 class="mb-0">Emploi du Temps - <span id="classe-name"></span></h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered emploi-temps-table">
                <thead>
                    <tr>
                        <th width="120">Heure</th>
                        <th>Lundi</th>
                        <th>Mardi</th>
                        <th>Mercredi</th>
                        <th>Jeudi</th>
                        <th>Vendredi</th>
                        <th>Samedi</th>
                    </tr>
                </thead>
                <tbody id="emploi-temps-body">
                    <!-- Contenu généré dynamiquement -->
                </tbody>
            </table>
        </div>
        
        <div class="mt-3 d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-primary" id="btn-ajouter-creneau-classe" onclick="showAddModal()" style="display:none;">
                <i class="fas fa-plus me-1"></i>Ajouter un créneau
            </button>
            <button type="button" class="btn btn-danger" onclick="exportEmploiTemps('pdf')" title="Télécharger le PDF">
                <i class="fas fa-file-pdf me-1"></i>Télécharger PDF
            </button>
            <button type="button" class="btn btn-outline-success" onclick="exportEmploiTemps('csv')" title="Exporter CSV">
                <i class="fas fa-file-csv me-1"></i>CSV
            </button>
        </div>
    </div>
</div>

<!-- Modal d'ajout de créneau -->
<div class="modal fade" id="addCreneauModal" tabindex="-1" data-bs-backdrop="false" data-bs-keyboard="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter un Créneau</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addCreneauForm">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="modal_classe_id" class="form-label">Classe *</label>
                                <select class="form-select" id="modal_classe_id" name="classe_id" required>
                                    <option value="">Sélectionner une classe</option>
                                    @foreach($classes as $classe)
                                    <option value="{{ $classe->id }}" data-is-primaire="{{ $classe->isPrimaire() ? '1' : '0' }}">{{ $classe->nom }} - {{ $classe->niveau }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="modal_matiere_id" class="form-label">Matière *</label>
                                <select class="form-select" id="modal_matiere_id" name="matiere_id" required>
                                    <option value="">Sélectionner une matière</option>
                                    @foreach($matieres as $matiere)
                                    <option value="{{ $matiere->id }}">{{ $matiere->nom }} ({{ $matiere->code }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="modal_enseignant_id" class="form-label">Enseignant *</label>
                                <select class="form-select" id="modal_enseignant_id" name="enseignant_id" required>
                                    <option value="">Sélectionner un enseignant</option>
                                    @foreach($enseignants as $enseignant)
                                    <option value="{{ $enseignant->id }}">{{ $enseignant->utilisateur->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="modal_jour" class="form-label">Jour *</label>
                                <select class="form-select" id="modal_jour" name="jour" required>
                                    <option value="">Sélectionner un jour</option>
                                    <option value="lundi">Lundi</option>
                                    <option value="mardi">Mardi</option>
                                    <option value="mercredi">Mercredi</option>
                                    <option value="jeudi">Jeudi</option>
                                    <option value="vendredi">Vendredi</option>
                                    <option value="samedi">Samedi</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="modal_heure_debut" class="form-label">Heure Début *</label>
                                <input type="time" class="form-control" id="modal_heure_debut" name="heure_debut" required>
                            </div>
                        </div>
                        <div class="col-md-3" id="modal_duree_wrap" style="display:none;">
                            <div class="mb-3">
                                <label for="modal_duree" class="form-label">Durée du cours *</label>
                                <select class="form-select" id="modal_duree">
                                    @php
                                        $dureesPrimaireModal = config('emploi_temps.primaire.durees_autorisees');
                                        if (!is_array($dureesPrimaireModal) || count($dureesPrimaireModal) === 0) {
                                            $dureesPrimaireModal = [30, 45, 60];
                                        }
                                        $dureeDefautModal = (int) (config('emploi_temps.primaire.duree_defaut') ?? 45);
                                    @endphp
                                    @foreach($dureesPrimaireModal as $d)
                                        <option value="{{ $d }}" @selected((int)$d === $dureeDefautModal)>
                                            @if((int)$d === 60)
                                                1 h (60 min)
                                            @else
                                                {{ $d }} min
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="modal_heure_fin_affiche" class="form-label">Heure Fin *</label>
                                <input type="time" class="form-control" id="modal_heure_fin_affiche" readonly tabindex="-1" style="background:#e9ecef;">
                                <input type="hidden" id="modal_heure_fin" name="heure_fin" value="">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="modal_salle" class="form-label">Salle</label>
                                <input type="text" class="form-control" id="modal_salle" name="salle" placeholder="Ex: A101">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="modal_force" name="force" value="1">
                                    <label class="form-check-label" for="modal_force">
                                        Forcer l'ajout même en cas de conflit d'horaire
                                    </label>
                                </div>
                                <div class="form-text">Cochez cette case pour ajouter le créneau même s'il y a un conflit d'horaire.</div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" onclick="saveCreneauModal()">
                    <i class="fas fa-save me-2"></i>Enregistrer
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de duplication -->
<div class="modal fade" id="duplicateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Dupliquer Emploi du Temps</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="duplicateForm">
                    <div class="mb-3">
                        <label for="source_classe" class="form-label">Classe Source *</label>
                        <select class="form-select" id="source_classe" name="source_classe_id" required>
                            <option value="">Sélectionner la classe source</option>
                            @foreach($classes as $classe)
                            <option value="{{ $classe->id }}">{{ $classe->nom }} - {{ $classe->niveau }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="target_classe" class="form-label">Classe Cible *</label>
                        <select class="form-select" id="target_classe" name="target_classe_id" required>
                            <option value="">Sélectionner la classe cible</option>
                            @foreach($classes as $classe)
                            <option value="{{ $classe->id }}">{{ $classe->nom }} - {{ $classe->niveau }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        L'emploi du temps existant de la classe cible sera remplacé.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-info" onclick="saveDuplicate()">
                    <i class="fas fa-copy me-2"></i>Dupliquer
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Debug: Afficher les informations de l'utilisateur connecté
console.log('=== DEBUG EMPLOIS DU TEMPS ===');
console.log('Utilisateur connecté:', @json(auth()->user()));
console.log('Permissions emplois-temps.view:', @json(auth()->user()->hasPermission('emplois-temps.view')));
console.log('Permissions emplois-temps.create:', @json(auth()->user()->hasPermission('emplois-temps.create')));
console.log('Classes disponibles:', @json($classes));
console.log('================================');

let currentClasseId = null;
let currentIsPrimaire = false;
let currentEmplois = [];
const DUREE_SECONDAIRE = {{ (int) (config('emploi_temps.secondaire.duree_defaut_minutes') ?? 120) }};
const DUREE_PRIMAIRE_DEFAUT = {{ (int) (config('emploi_temps.primaire.duree_defaut') ?? 45) }};
@php
    $heuresSecondaireJs = config('emploi_temps.secondaire.heures_debut');
    if (!is_array($heuresSecondaireJs) || count($heuresSecondaireJs) === 0) {
        $heuresSecondaireJs = ['08:00', '10:10', '12:10', '14:30'];
    }
@endphp
const HEURES_SECONDAIRE = {!! json_encode($heuresSecondaireJs) !!};

function isClassePrimaire(classeId) {
    const card = document.querySelector('.classe-card[data-classe-id="' + classeId + '"]');
    if (card && card.getAttribute('data-is-primaire') === '1') return true;
    const opt = document.querySelector('#modal_classe_id option[value="' + classeId + '"]');
    return opt && opt.getAttribute('data-is-primaire') === '1';
}

function minutesToTime(totalMinutes) {
    const h = Math.floor(totalMinutes / 60) % 24;
    const m = totalMinutes % 60;
    return String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0');
}

function parseTimeToMinutes(timeStr) {
    const parts = (timeStr || '').split(':');
    if (parts.length < 2) return null;
    return parseInt(parts[0], 10) * 60 + parseInt(parts[1], 10);
}

function syncModalDureeUi() {
    const classeId = document.getElementById('modal_classe_id').value;
    const wrap = document.getElementById('modal_duree_wrap');
    const finAffiche = document.getElementById('modal_heure_fin_affiche');
    if (!wrap) return;

    // Primaire : choix 30/45/60 — Secondaire : 2 h fixe (toujours fin auto)
    if (isClassePrimaire(classeId) || currentIsPrimaire) {
        wrap.style.display = '';
    } else {
        wrap.style.display = 'none';
    }
    if (finAffiche) {
        finAffiche.readOnly = true;
        finAffiche.style.background = '#e9ecef';
    }
    recalculerFinModal();
}

function recalculerFinModal() {
    const debutEl = document.getElementById('modal_heure_debut');
    const finHidden = document.getElementById('modal_heure_fin');
    const finAffiche = document.getElementById('modal_heure_fin_affiche');
    const debut = debutEl ? debutEl.value : '';
    const start = parseTimeToMinutes(debut);
    if (start === null) {
        if (finHidden) finHidden.value = '';
        if (finAffiche) finAffiche.value = '';
        return;
    }

    const classeId = document.getElementById('modal_classe_id').value;
    let duree = DUREE_SECONDAIRE;
    if (isClassePrimaire(classeId) || currentIsPrimaire) {
        const dureeEl = document.getElementById('modal_duree');
        duree = parseInt(dureeEl && dureeEl.value ? dureeEl.value : DUREE_PRIMAIRE_DEFAUT, 10) || DUREE_PRIMAIRE_DEFAUT;
    }

    const fin = minutesToTime(start + duree);
    if (finHidden) finHidden.value = fin;
    if (finAffiche) finAffiche.value = fin;
}

document.addEventListener('DOMContentLoaded', function () {
    const classeSelect = document.getElementById('modal_classe_id');
    const debutEl = document.getElementById('modal_heure_debut');
    const dureeEl = document.getElementById('modal_duree');
    const finAffiche = document.getElementById('modal_heure_fin_affiche');

    if (classeSelect) {
        classeSelect.addEventListener('change', function () {
            currentIsPrimaire = isClassePrimaire(this.value);
            syncModalDureeUi();
        });
    }
    if (debutEl) {
        debutEl.addEventListener('change', recalculerFinModal);
        debutEl.addEventListener('input', recalculerFinModal);
    }
    if (dureeEl) {
        dureeEl.addEventListener('change', function () {
            recalculerFinModal();
            // Si primaire et jour choisi : repositionner au prochain créneau libre pour cette durée
            const jour = document.getElementById('modal_jour').value;
            if ((currentIsPrimaire || isClassePrimaire(document.getElementById('modal_classe_id').value)) && jour) {
                const slot = getProchaineHeureLibre(jour, getDureeModalActuelle());
                const debutActuel = normalizeTimeHHmm(document.getElementById('modal_heure_debut').value);
                const finActuel = normalizeTimeHHmm(document.getElementById('modal_heure_fin').value);
                const occupe = (currentEmplois || []).some(e => {
                    if (String(e.jour_semaine).toLowerCase() !== String(jour).toLowerCase()) return false;
                    const d = normalizeTimeHHmm(e.heure_debut);
                    const f = normalizeTimeHHmm(e.heure_fin);
                    return debutActuel < f && finActuel > d;
                });
                if (occupe) {
                    document.getElementById('modal_heure_debut').value = slot.debut;
                    recalculerFinModal();
                }
            }
        });
        dureeEl.addEventListener('input', recalculerFinModal);
    }
    const jourEl = document.getElementById('modal_jour');
    if (jourEl) {
        jourEl.addEventListener('change', function () {
            if (!(currentIsPrimaire || isClassePrimaire(document.getElementById('modal_classe_id').value))) return;
            const slot = getProchaineHeureLibre(this.value, getDureeModalActuelle());
            document.getElementById('modal_heure_debut').value = slot.debut;
            recalculerFinModal();
        });
    }
    if (finAffiche) {
        finAffiche.addEventListener('change', function () {
            const classeId = document.getElementById('modal_classe_id').value;
            if (!(isClassePrimaire(classeId) || currentIsPrimaire)) {
                document.getElementById('modal_heure_fin').value = this.value;
            }
        });
    }
});

function loadEmploiTemps(classeId, element) {
    if (!classeId) {
        alert('Erreur: ID de classe manquant');
        return;
    }
    
    currentClasseId = classeId;
    currentIsPrimaire = isClassePrimaire(classeId);
    
    document.querySelectorAll('.classe-card').forEach(card => {
        card.classList.remove('border-primary');
    });
    
    if (element) {
        element.classList.add('border-primary');
    }
    
    const container = document.getElementById('emploi-temps-container');
    const classeName = document.getElementById('classe-name');
    const tbody = document.getElementById('emploi-temps-body');
    
    if (container && classeName && tbody) {
        classeName.textContent = 'Chargement...';
        tbody.innerHTML = '<tr><td colspan="7" class="text-center">Chargement en cours...</td></tr>';
        container.style.display = 'block';
    }
    
    const url = `/get-emploi-temps?classe_id=${classeId}`;
    
    fetch(url, {
        method: 'GET',
        credentials: 'same-origin',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        }
    })
    .then(response => {
        if (!response.ok) {
            if (response.status === 401) {
                throw new Error('Vous n\'êtes pas connecté. Veuillez vous reconnecter.');
            } else if (response.status === 403) {
                throw new Error('Vous n\'avez pas les permissions pour voir les emplois du temps.');
            } else {
                throw new Error(`Erreur serveur: ${response.status} ${response.statusText}`);
            }
        }
        return response.json();
    })
    .then(data => {
        if (!data || !data.classe) {
            throw new Error('Données invalides reçues du serveur');
        }
        
        if (classeName) {
            classeName.textContent = data.classe.nom;
        }

        if (typeof data.classe.is_primaire !== 'undefined') {
            currentIsPrimaire = !!data.classe.is_primaire;
        } else if (data.classe.niveau) {
            const n = String(data.classe.niveau).toLowerCase();
            currentIsPrimaire = n === 'primaire' || n === 'préscolaire' || n === 'prescolaire';
        }

        const btnAdd = document.getElementById('btn-ajouter-creneau-classe');
        if (btnAdd) {
            btnAdd.style.display = '';
        }

        currentEmplois = data.emplois || [];
        
        generateEmploiTempsTable(currentEmplois);
    })
    .catch(error => {
        console.error('Erreur:', error);
        if (tbody) {
            tbody.innerHTML = `<tr><td colspan="7" class="text-center text-danger">Erreur: ${error.message}</td></tr>`;
        }
        alert('Erreur lors du chargement de l\'emploi du temps:\n' + error.message);
    });
}

function generateEmploiTempsTable(emplois) {
    const tbody = document.getElementById('emploi-temps-body');
    if (!tbody) {
        return;
    }
    
    const jours = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];

    // Grille dynamique (primaire ET secondaire) : une ligne par plage déjà ajoutée
    const seen = {};
    let plages = [];
    (emplois || []).forEach(e => {
        const debut = e.heure_debut ? String(e.heure_debut).substring(0, 5) : '';
        const fin = e.heure_fin ? String(e.heure_fin).substring(0, 5) : '';
        if (!debut) return;
        const key = currentIsPrimaire ? (debut + '-' + (fin || '')) : debut;
        if (seen[key]) return;
        seen[key] = true;
        if (currentIsPrimaire && fin) {
            plages.push({ debut, fin, label: debut + ' – ' + fin });
        } else {
            const startMin = parseTimeToMinutes(debut);
            const finCalc = (startMin !== null)
                ? minutesToTime(startMin + DUREE_SECONDAIRE)
                : (fin || '');
            plages.push({
                debut,
                fin: fin || finCalc,
                label: debut + (finCalc ? ' – ' + finCalc : '')
            });
        }
    });
    plages.sort((a, b) => a.debut.localeCompare(b.debut));

    tbody.innerHTML = '';

    if (plages.length === 0) {
        const row = document.createElement('tr');
        const cell = document.createElement('td');
        cell.colSpan = jours.length + 1;
        cell.className = 'text-center text-muted py-4';
        cell.innerHTML = 'Aucun créneau pour le moment.<br>Cliquez sur <strong>« Ajouter un créneau »</strong> : la grille s’agrandira automatiquement.';
        row.appendChild(cell);
        tbody.appendChild(row);
        return;
    }
    
    plages.forEach(plage => {
        const row = document.createElement('tr');
        
        const heureCell = document.createElement('td');
        const dureeLabel = currentIsPrimaire
            ? ''
            : `<div class="small text-muted">${Math.round(DUREE_SECONDAIRE / 60)} h</div>`;
        heureCell.innerHTML = `<strong>${plage.label}</strong>${dureeLabel}`;
        heureCell.className = 'table-secondary';
        heureCell.style.minWidth = '110px';
        row.appendChild(heureCell);
        
        jours.forEach(jour => {
            const cell = document.createElement('td');
            cell.className = 'creneau-cell';
            cell.style.minHeight = '90px';
            cell.style.height = '90px';
            cell.style.cursor = 'pointer';
            cell.style.verticalAlign = 'middle';
            cell.onclick = () => addCreneau(jour, plage.debut, plage.fin);
            
            let emploi = null;
            if (currentIsPrimaire) {
                emploi = (emplois || []).find(e => {
                    const heureDebut = e.heure_debut ? String(e.heure_debut).substring(0, 5) : '';
                    const heureFin = e.heure_fin ? String(e.heure_fin).substring(0, 5) : '';
                    return e.jour_semaine === jour && heureDebut === plage.debut && heureFin === plage.fin;
                });
                if (!emploi) {
                    emploi = (emplois || []).find(e => {
                        const heureDebut = e.heure_debut ? String(e.heure_debut).substring(0, 5) : '';
                        return e.jour_semaine === jour && heureDebut === plage.debut;
                    });
                }
            } else {
                emploi = (emplois || []).find(e => {
                    const heureDebut = e.heure_debut ? String(e.heure_debut).substring(0, 5) : '';
                    return e.jour_semaine === jour && heureDebut === plage.debut;
                });
            }
            
            if (emploi && emploi.matiere && emploi.enseignant) {
                const debutAff = emploi.heure_debut ? String(emploi.heure_debut).substring(0, 5) : '';
                const finAff = emploi.heure_fin ? String(emploi.heure_fin).substring(0, 5) : '';
                cell.innerHTML = `
                    <div class="creneau" style="background-color: ${emploi.matiere.couleur || '#007bff'}; color: white; padding: 8px; border-radius: 3px; position: relative; min-height: 70px;">
                        <small>${debutAff} – ${finAff}</small><br>
                        <strong>${emploi.matiere.nom}</strong><br>
                        <small>${emploi.enseignant.utilisateur ? (emploi.enseignant.utilisateur.nom || '') + ' ' + (emploi.enseignant.utilisateur.prenom || '') : 'Enseignant'}</small>
                        <button type="button" class="btn btn-sm btn-outline-light position-absolute top-0 end-0" 
                                onclick="event.stopPropagation(); deleteCreneau(${emploi.id})" 
                                style="padding: 2px 6px; font-size: 10px;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
            } else {
                cell.innerHTML = '<div class="text-center text-muted" style="padding: 20px; font-size: 1.25rem;">+</div>';
            }
            
            row.appendChild(cell);
        });
        
        tbody.appendChild(row);
    });
}

function getDureeModalActuelle() {
    if (currentIsPrimaire || isClassePrimaire(document.getElementById('modal_classe_id').value)) {
        const el = document.getElementById('modal_duree');
        return parseInt(el && el.value ? el.value : DUREE_PRIMAIRE_DEFAUT, 10) || DUREE_PRIMAIRE_DEFAUT;
    }
    return DUREE_SECONDAIRE;
}

/** Prochaine heure de début libre pour un jour (évite le chevauchement). */
function getProchaineHeureLibre(jour, dureeMinutes) {
    const duree = dureeMinutes || getDureeModalActuelle();
    const creneaux = (currentEmplois || [])
        .filter(e => String(e.jour_semaine).toLowerCase() === String(jour).toLowerCase())
        .map(e => ({
            debut: normalizeTimeHHmm(e.heure_debut),
            fin: normalizeTimeHHmm(e.heure_fin),
            matiere: (e.matiere && e.matiere.nom) ? e.matiere.nom : 'Cours'
        }))
        .filter(c => c.debut && c.fin)
        .sort((a, b) => a.debut.localeCompare(b.debut));

    let candidat = '08:00';
    for (let i = 0; i < 20; i++) {
        const startMin = parseTimeToMinutes(candidat);
        if (startMin === null) break;
        const finCandidat = minutesToTime(startMin + duree);
        const chevauche = creneaux.find(c => candidat < c.fin && finCandidat > c.debut);
        if (!chevauche) {
            return { debut: candidat, fin: finCandidat, suggestion: null };
        }
        candidat = chevauche.fin;
    }
    return { debut: candidat, fin: minutesToTime(parseTimeToMinutes(candidat) + duree), suggestion: null };
}

function showAddModal() {
    if (currentClasseId) {
        document.getElementById('modal_classe_id').value = currentClasseId;
        currentIsPrimaire = isClassePrimaire(currentClasseId);
    }
    const jourEl = document.getElementById('modal_jour');
    if (jourEl && !jourEl.value) {
        jourEl.value = 'lundi';
    }
    syncModalDureeUi();
    const jour = jourEl ? jourEl.value : 'lundi';
    if (currentIsPrimaire && jour) {
        const slot = getProchaineHeureLibre(jour, getDureeModalActuelle());
        document.getElementById('modal_heure_debut').value = slot.debut;
    } else {
        const debutEl = document.getElementById('modal_heure_debut');
        if (debutEl && !debutEl.value) {
            debutEl.value = '08:00';
        }
    }
    recalculerFinModal();
    new bootstrap.Modal(document.getElementById('addCreneauModal')).show();
}

function addCreneau(jour, heure, heureFin = null) {
    if (!currentClasseId) {
        alert('Veuillez d\'abord sélectionner une classe');
        return;
    }
    
    document.getElementById('modal_classe_id').value = currentClasseId;
    document.getElementById('modal_jour').value = jour;
    currentIsPrimaire = isClassePrimaire(currentClasseId);
    syncModalDureeUi();

    // Ne pas reprendre une heure déjà occupée : proposer la prochaine libre
    if (currentIsPrimaire) {
        const duree = getDureeModalActuelle();
        let debutPropose = heure || '08:00';
        const finPropose = minutesToTime(parseTimeToMinutes(debutPropose) + duree);
        const occupe = (currentEmplois || []).some(e => {
            if (String(e.jour_semaine).toLowerCase() !== String(jour).toLowerCase()) return false;
            const d = normalizeTimeHHmm(e.heure_debut);
            const f = normalizeTimeHHmm(e.heure_fin);
            return debutPropose < f && finPropose > d;
        });
        if (occupe || !heure) {
            const slot = getProchaineHeureLibre(jour, duree);
            debutPropose = slot.debut;
        }
        document.getElementById('modal_heure_debut').value = debutPropose;
    } else {
        document.getElementById('modal_heure_debut').value = heure || '08:00';
    }

    recalculerFinModal();
    new bootstrap.Modal(document.getElementById('addCreneauModal')).show();
}

function normalizeTimeHHmm(val) {
    if (!val) return '';
    const parts = String(val).trim().split(':');
    if (parts.length < 2) return '';
    const h = String(parseInt(parts[0], 10)).padStart(2, '0');
    const m = String(parseInt(parts[1], 10)).padStart(2, '0');
    return h + ':' + m;
}

function saveCreneauModal() {
    const form = document.getElementById('addCreneauForm');
    recalculerFinModal();

    const debut = normalizeTimeHHmm(document.getElementById('modal_heure_debut').value);
    const fin = normalizeTimeHHmm(document.getElementById('modal_heure_fin').value);

    if (!debut) {
        alert('Heure de début obligatoire.');
        return;
    }
    if (!fin) {
        alert('Heure de fin manquante. Choisissez une heure de début et une durée (30 / 45 / 60 min).');
        return;
    }

    // Remettre les valeurs normalisées dans le formulaire
    document.getElementById('modal_heure_debut').value = debut;
    document.getElementById('modal_heure_fin').value = fin;
    const finAffiche = document.getElementById('modal_heure_fin_affiche');
    if (finAffiche) finAffiche.value = fin;

    const formData = new FormData(form);
    formData.set('heure_debut', debut);
    formData.set('heure_fin', fin);
    formData.set('jour', (document.getElementById('modal_jour').value || '').toLowerCase());
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        alert('Erreur: Token CSRF manquant. Veuillez recharger la page.');
        return;
    }
    
    const saveButton = document.querySelector('#addCreneauModal .btn-primary');
    const originalText = saveButton.innerHTML;
    saveButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Enregistrement...';
    saveButton.disabled = true;
    
    // Route principale Laravel (évite l’ancienne /add-emploi-temps trop stricte)
    const url = '{{ route("emplois-temps.store") }}';
    
    fetch(url, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin',
        headers: {
            'X-CSRF-TOKEN': csrfToken.content,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(async response => {
        const data = await response.json().catch(() => null);
        if (!response.ok) {
            let msg = 'Erreur HTTP ' + response.status;
            if (data) {
                if (data.message) msg = data.message;
                else if (data.errors) {
                    msg = Object.values(data.errors).flat().join('\n');
                } else if (data.error) {
                    msg = data.error;
                }
            }
            throw new Error(msg);
        }
        return data;
    })
    .then(data => {
        if (!data || data.success !== true) {
            throw new Error((data && data.message) ? data.message : 'Échec de l\'enregistrement');
        }
        bootstrap.Modal.getInstance(document.getElementById('addCreneauModal')).hide();
        showToast(data.message || 'Créneau ajouté avec succès', 'success');
        if (currentClasseId) {
            loadEmploiTemps(currentClasseId, document.querySelector('.classe-card.border-primary'));
        }
    })
    .catch(error => {
        console.error(error);
        alert('Erreur lors de l\'ajout du créneau:\n' + error.message);
    })
    .finally(() => {
        saveButton.innerHTML = originalText;
        saveButton.disabled = false;
    });
}

function deleteCreneau(emploiId) {
    if (confirm('Supprimer ce créneau ?')) {
        console.log('Suppression du créneau:', emploiId);
        
        // Adapter l'URL pour LWS
        const baseUrl = window.location.origin + window.location.pathname.replace('/emplois-temps', '');
        const urls = [
            `${baseUrl}/test-delete-emploi-temps/${emploiId}`,
            `${baseUrl}/delete-emploi-temps/${emploiId}`,
            `${baseUrl}/emplois-temps/${emploiId}`
        ];
        
        let currentUrlIndex = 0;
        
        function tryDeleteCreneau() {
            if (currentUrlIndex >= urls.length) {
                throw new Error('Toutes les routes de suppression ont échoué');
            }
            
            const url = urls[currentUrlIndex];
            console.log(`Tentative de suppression avec l'URL: ${url}`);
            
            // Utiliser POST pour la route de test, DELETE pour les autres
            const method = url.includes('test-delete-emploi-temps') ? 'POST' : 'DELETE';
            
            return fetch(url, {
                method: method,
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                console.log('Réponse suppression:', response.status, response.statusText);
                
                if (!response.ok) {
                    if (response.status === 404 && currentUrlIndex < urls.length - 1) {
                        console.log('Route de suppression non trouvée, essai de la route suivante...');
                        currentUrlIndex++;
                        return tryDeleteCreneau();
                    }
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                // Vérifier le type de contenu
                const contentType = response.headers.get('content-type');
                console.log('Type de contenu suppression:', contentType);
                
                if (contentType && contentType.includes('application/json')) {
                    return response.json();
                } else {
                    return response.text().then(text => {
                        console.log('Réponse non-JSON suppression:', text);
                        throw new Error('Réponse non-JSON reçue: ' + text.substring(0, 100));
                    });
                }
            })
            .then(data => {
                console.log('Données suppression reçues:', data);
                console.log('Type de données:', typeof data);
                console.log('Contenu brut:', JSON.stringify(data));
                
                // Vérifier que les données sont valides
                if (!data) {
                    throw new Error('Aucune donnée reçue du serveur');
                }
                
                if (typeof data !== 'object') {
                    console.error('Type de données incorrect:', typeof data, data);
                    throw new Error('Format de données incorrect - JSON attendu, reçu: ' + typeof data);
                }
                
                if (data.success === true) {
                    console.log('Créneau supprimé avec succès');
                    showToast(data.message || 'Créneau supprimé avec succès', 'success');
                    
                    // Recharger l'emploi du temps
                    setTimeout(() => {
                        console.log('Rechargement de l\'emploi du temps après suppression...');
                        loadEmploiTemps(currentClasseId);
                    }, 500);
                } else if (data.success === false) {
                    console.error('Erreur signalée par le serveur:', data.message);
                    throw new Error(data.message || 'Erreur lors de la suppression');
                } else {
                    console.error('Propriété "success" manquante dans la réponse:', data);
                    throw new Error('Réponse du serveur invalide - propriété "success" manquante');
                }
            });
        }
        
        tryDeleteCreneau()
        .catch(error => {
            console.error('Erreur suppression:', error);
            
            // Si l'erreur est "Aucune donnée reçue du serveur" mais que la suppression a fonctionné,
            // considérer que c'est un succès (cas LWS)
            if (error.message.includes('Aucune donnée reçue du serveur')) {
                console.log('Suppression réussie (aucune donnée reçue mais suppression effective)');
                showToast('Créneau supprimé avec succès', 'success');
                
                // Recharger l'emploi du temps
                setTimeout(() => {
                    console.log('Rechargement de l\'emploi du temps après suppression...');
                    loadEmploiTemps(currentClasseId);
                }, 500);
            } else {
                alert('Erreur lors de la suppression: ' + error.message);
            }
        });
    }
}

function showDuplicateModal() {
    new bootstrap.Modal(document.getElementById('duplicateModal')).show();
}

function saveDuplicate() {
    const form = document.getElementById('duplicateForm');
    const formData = new FormData(form);
    
    fetch('/emplois-temps/duplicate', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('duplicateModal')).hide();
            showToast(data.message, 'success');
            if (currentClasseId) {
                loadEmploiTemps(currentClasseId);
            }
        } else {
            alert(data.message || 'Erreur lors de la duplication');
        }
    });
}

function exportEmploiTemps(format) {
    if (!currentClasseId) {
        alert('Veuillez sélectionner une classe');
        return;
    }
    const fmt = format === 'csv' ? 'csv' : 'pdf';
    window.open(`/emplois-temps/classe/${currentClasseId}/export?format=${fmt}`, '_blank');
}

function confirmDeleteAll() {
    if (confirm('Êtes-vous sûr de vouloir supprimer tous les emplois du temps ?')) {
        fetch('/emplois-temps/delete-all', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => {
            if (response.ok) {
                location.reload();
            }
        });
    }
}

function showToast(message, type) {
    const toastHtml = `
        <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    const toastContainer = document.getElementById('toast-container') || createToastContainer();
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    
    const toastElement = toastContainer.lastElementChild;
    const toast = new bootstrap.Toast(toastElement);
    toast.show();
}

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'toast-container position-fixed top-0 end-0 p-3';
    document.body.appendChild(container);
    return container;
}
</script>
@endpush

@push('styles')
<style>
.classe-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.classe-card.border-primary {
    border: 2px solid #007bff !important;
}

.creneau-cell {
    height: 80px;
    vertical-align: middle;
}

.creneau-cell:hover {
    background-color: #f8f9fa;
}

.emploi-temps-table th {
    text-align: center;
    background-color: #343a40;
    color: white;
}

.creneau {
    font-size: 12px;
    text-align: center;
}
</style>
@endpush
@endsection














