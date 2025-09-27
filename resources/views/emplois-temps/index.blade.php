@extends('layouts.app')

@section('title', 'Gestion des Emplois du Temps')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-calendar-alt me-2"></i>
        Gestion des Emplois du Temps
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
                <div class="card classe-card" data-classe-id="{{ $classe->id }}" onclick="loadEmploiTemps({{ $classe->id }}, this)" style="cursor: pointer;">
                    <div class="card-body text-center">
                        <h5 class="card-title">{{ $classe->nom }}</h5>
                        <p class="card-text">{{ $classe->niveau }}</p>
                        <small class="text-muted">{{ $classe->eleves->count() }} élèves</small>
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
                        <th width="100">Heure</th>
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
        
        <div class="mt-3">
            <button type="button" class="btn btn-success" onclick="exportEmploiTemps()">
                <i class="fas fa-download me-2"></i>Exporter CSV
            </button>
        </div>
    </div>
</div>

<!-- Modal d'ajout de créneau -->
<div class="modal fade" id="addCreneauModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
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
                                    <option value="{{ $classe->id }}">{{ $classe->nom }} - {{ $classe->niveau }}</option>
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
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="modal_heure_debut" class="form-label">Heure Début *</label>
                                <input type="time" class="form-control" id="modal_heure_debut" name="heure_debut" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="modal_heure_fin" class="form-label">Heure Fin *</label>
                                <input type="time" class="form-control" id="modal_heure_fin" name="heure_fin" required>
                            </div>
                        </div>
                        <div class="col-md-4">
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
let currentClasseId = null;

function loadEmploiTemps(classeId, element) {
    currentClasseId = classeId;
    
    // Mettre en surbrillance la classe sélectionnée
    document.querySelectorAll('.classe-card').forEach(card => {
        card.classList.remove('border-primary');
    });
    
    // Mettre en surbrillance la carte cliquée
    if (element) {
        element.classList.add('border-primary');
    }
    
    // Charger l'emploi du temps
    fetch(`/emplois-temps/classe/${classeId}/data`, {
        credentials: 'same-origin', // Inclure les cookies de session
        headers: {
            'Accept': 'application/json'
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            document.getElementById('classe-name').textContent = data.classe.nom;
            generateEmploiTempsTable(data.emplois);
            document.getElementById('emploi-temps-container').style.display = 'block';
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur lors du chargement de l\'emploi du temps: ' + error.message);
        });
}

function generateEmploiTempsTable(emplois) {
    const tbody = document.getElementById('emploi-temps-body');
    const jours = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
    const heures = ['08:00', '10:10', '12:10', '14:30'];
    
    tbody.innerHTML = '';
    
    heures.forEach(heure => {
        const row = document.createElement('tr');
        
        // Colonne heure
        const heureCell = document.createElement('td');
        heureCell.innerHTML = `<strong>${heure}</strong>`;
        heureCell.className = 'table-secondary';
        row.appendChild(heureCell);
        
        // Colonnes jours
        jours.forEach(jour => {
            const cell = document.createElement('td');
            cell.className = 'creneau-cell';
            cell.style.minHeight = '60px';
            cell.style.cursor = 'pointer';
            cell.onclick = () => addCreneau(jour, heure);
            
            // Chercher un emploi pour ce créneau
            const emploi = emplois.find(e => {
                // Extraire l'heure de début et fin (format HH:MM:SS -> HH:MM)
                const heureDebut = e.heure_debut.substring(0, 5);
                const heureFin = e.heure_fin.substring(0, 5);
                
                return e.jour_semaine === jour && 
                       heureDebut <= heure && 
                       heureFin > heure;
            });
            
            if (emploi) {
                cell.innerHTML = `
                    <div class="creneau" style="background-color: ${emploi.matiere.couleur}; color: white; padding: 5px; border-radius: 3px; position: relative;">
                        <strong>${emploi.matiere.nom}</strong><br>
                        <small>${emploi.enseignant.utilisateur.name}</small>
                        <button type="button" class="btn btn-sm btn-outline-light position-absolute top-0 end-0" 
                                onclick="event.stopPropagation(); deleteCreneau(${emploi.id})" 
                                style="padding: 2px 6px; font-size: 10px;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
            } else {
                cell.innerHTML = '<div class="text-center text-muted" style="padding: 20px;">+</div>';
            }
            
            row.appendChild(cell);
        });
        
        tbody.appendChild(row);
    });
}

function showAddModal() {
    if (currentClasseId) {
        document.getElementById('modal_classe_id').value = currentClasseId;
    }
    new bootstrap.Modal(document.getElementById('addCreneauModal')).show();
}

function addCreneau(jour, heure) {
    if (!currentClasseId) {
        alert('Veuillez d\'abord sélectionner une classe');
        return;
    }
    
    document.getElementById('modal_classe_id').value = currentClasseId;
    document.getElementById('modal_jour').value = jour;
    document.getElementById('modal_heure_debut').value = heure;
    // Calculer heure de fin (2h plus tard)
    const [heures, minutes] = heure.split(':');
    const heureFin = String(parseInt(heures) + 2).padStart(2, '0') + ':' + minutes;
    document.getElementById('modal_heure_fin').value = heureFin;
    
    new bootstrap.Modal(document.getElementById('addCreneauModal')).show();
}

function saveCreneauModal() {
    const form = document.getElementById('addCreneauForm');
    const formData = new FormData(form);
    
    // Vérifier le token CSRF
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        alert('Erreur: Token CSRF manquant. Veuillez recharger la page.');
        return;
    }
    
    // Afficher un indicateur de chargement
    const saveButton = document.querySelector('#addCreneauModal .btn-primary');
    const originalText = saveButton.innerHTML;
    saveButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Enregistrement...';
    saveButton.disabled = true;
    
    fetch('/emplois-temps', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin', // Inclure les cookies de session
        headers: {
            'X-CSRF-TOKEN': csrfToken.content,
            'Accept': 'application/json'
        }
    })
    .then(response => {
        console.log('Réponse reçue:', response.status, response.statusText);
        
        // Vérifier si la réponse est OK
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        // Vérifier le type de contenu
        const contentType = response.headers.get('content-type');
        console.log('Type de contenu:', contentType);
        
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            return response.text().then(text => {
                console.log('Réponse non-JSON:', text);
                throw new Error('Réponse non-JSON reçue: ' + text.substring(0, 100));
            });
        }
    })
    .then(data => {
        console.log('Données reçues:', data);
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('addCreneauModal')).hide();
            loadEmploiTemps(currentClasseId);
            showToast(data.message || 'Créneau ajouté avec succès', 'success');
        } else {
            alert(data.message || 'Erreur lors de l\'ajout');
        }
    })
    .catch(error => {
        console.error('Erreur détaillée:', error);
        alert('Erreur lors de l\'ajout du créneau: ' + error.message);
    })
    .finally(() => {
        // Restaurer le bouton
        saveButton.innerHTML = originalText;
        saveButton.disabled = false;
    });
}

function deleteCreneau(emploiId) {
    if (confirm('Supprimer ce créneau ?')) {
        fetch(`/emplois-temps/${emploiId}`, {
            method: 'DELETE',
            credentials: 'same-origin', // Inclure les cookies de session
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                loadEmploiTemps(currentClasseId);
                showToast(data.message, 'success');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur lors de la suppression: ' + error.message);
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

function exportEmploiTemps() {
    if (!currentClasseId) {
        alert('Veuillez sélectionner une classe');
        return;
    }
    
    window.open(`/emplois-temps/classe/${currentClasseId}/export`, '_blank');
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














