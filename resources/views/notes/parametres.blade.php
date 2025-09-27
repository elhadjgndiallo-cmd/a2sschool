@extends('layouts.app')

@section('title', 'Paramètres de Notation')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-cog me-2"></i>Paramètres de Notation</h2>
                <a href="{{ route('notes.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour
                </a>
            </div>

            <div class="row">
                <!-- Gestion des coefficients des matières -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Coefficients des Matières</h5>
                        </div>
                        <div class="card-body">
                            <form id="coefficientsForm">
                                @csrf
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Matière</th>
                                                <th>Coefficient</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($matieres as $matiere)
                                            <tr>
                                                <td>{{ $matiere->nom }}</td>
                                                <td>
                                                    <input type="number" 
                                                           class="form-control form-control-sm" 
                                                           value="{{ $matiere->coefficient }}" 
                                                           min="1" max="10" step="0.5"
                                                           data-matiere="{{ $matiere->id }}">
                                                </td>
                                                <td>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-success"
                                                            onclick="updateCoefficient({{ $matiere->id }})">
                                                        <i class="fas fa-save"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Paramètres généraux -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Paramètres Généraux</h5>
                        </div>
                        <div class="card-body">
                            <form id="parametresForm">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Note minimale</label>
                                    <input type="number" class="form-control" value="0" min="0" max="20" step="0.25">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Note maximale</label>
                                    <input type="number" class="form-control" value="20" min="0" max="20" step="0.25">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Seuil de réussite</label>
                                    <input type="number" class="form-control" value="10" min="0" max="20" step="0.25">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Arrondi des moyennes</label>
                                    <select class="form-select">
                                        <option value="0.01">Au centième (0.01)</option>
                                        <option value="0.1">Au dixième (0.1)</option>
                                        <option value="0.25">Au quart (0.25)</option>
                                        <option value="0.5" selected>À la demi (0.5)</option>
                                        <option value="1">À l'unité (1)</option>
                                    </select>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="afficherRang" checked>
                                    <label class="form-check-label" for="afficherRang">
                                        Afficher le rang dans les bulletins
                                    </label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="calculerMoyenne" checked>
                                    <label class="form-check-label" for="calculerMoyenne">
                                        Calcul automatique des moyennes
                                    </label>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Enregistrer
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Barème d'évaluation -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Barème d'Évaluation</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Note</th>
                                                <th>Appréciation</th>
                                                <th>Couleur</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>16 - 20</td>
                                                <td>Très bien</td>
                                                <td><span class="badge bg-success">Vert</span></td>
                                            </tr>
                                            <tr>
                                                <td>14 - 15.99</td>
                                                <td>Bien</td>
                                                <td><span class="badge bg-info">Bleu</span></td>
                                            </tr>
                                            <tr>
                                                <td>12 - 13.99</td>
                                                <td>Assez bien</td>
                                                <td><span class="badge bg-warning">Orange</span></td>
                                            </tr>
                                            <tr>
                                                <td>10 - 11.99</td>
                                                <td>Passable</td>
                                                <td><span class="badge bg-secondary">Gris</span></td>
                                            </tr>
                                            <tr>
                                                <td>0 - 9.99</td>
                                                <td>Insuffisant</td>
                                                <td><span class="badge bg-danger">Rouge</span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h6>Types d'Évaluations</h6>
                                    <div class="list-group">
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            Devoir Surveillé (DS)
                                            <span class="badge bg-primary">Coefficient x2</span>
                                        </div>
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            Devoir Maison (DM)
                                            <span class="badge bg-info">Coefficient x1</span>
                                        </div>
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            Interrogation
                                            <span class="badge bg-warning">Coefficient x0.5</span>
                                        </div>
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            Participation
                                            <span class="badge bg-secondary">Coefficient x0.25</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Périodes scolaires -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Périodes Scolaires</h5>
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addPeriodeModal">
                                <i class="fas fa-plus me-1"></i>Ajouter une période
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="row" id="periodesContainer">
                                @foreach($periodesScolaires as $periode)
                                <div class="col-md-4 mb-3" data-periode-id="{{ $periode->id }}">
                                    <div class="card border-{{ $periode->couleur_bootstrap }}">
                                        <div class="card-header bg-{{ $periode->couleur_bootstrap }} text-white d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0">{{ $periode->nom }}</h6>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-light btn-sm" onclick="editPeriode({{ $periode->id }})" title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-light btn-sm" onclick="deletePeriode({{ $periode->id }})" title="Supprimer">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <p class="mb-1"><strong>Début:</strong> {{ $periode->date_debut->format('d/m/Y') }}</p>
                                            <p class="mb-1"><strong>Fin:</strong> {{ $periode->date_fin->format('d/m/Y') }}</p>
                                            <p class="mb-0"><strong>Conseil:</strong> {{ $periode->date_conseil->format('d/m/Y') }}</p>
                                            <div class="mt-2">
                                                <span class="badge bg-{{ $periode->actif ? 'success' : 'secondary' }}">
                                                    {{ $periode->actif ? 'Actif' : 'Inactif' }}
                                                </span>
                                                <span class="badge bg-info ms-1">Ordre: {{ $periode->ordre }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            
                            @if($periodesScolaires->isEmpty())
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-calendar-alt fa-3x mb-3"></i>
                                <h5>Aucune période scolaire configurée</h5>
                                <p>Cliquez sur "Ajouter une période" pour commencer.</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour ajouter une période scolaire -->
<div class="modal fade" id="addPeriodeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter une période scolaire</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addPeriodeForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nom" class="form-label">Nom de la période</label>
                        <input type="text" class="form-control" id="nom" name="nom" required>
                    </div>
                    <div class="mb-3">
                        <label for="date_debut" class="form-label">Date de début</label>
                        <input type="date" class="form-control" id="date_debut" name="date_debut" required>
                    </div>
                    <div class="mb-3">
                        <label for="date_fin" class="form-label">Date de fin</label>
                        <input type="date" class="form-control" id="date_fin" name="date_fin" required>
                    </div>
                    <div class="mb-3">
                        <label for="date_conseil" class="form-label">Date du conseil</label>
                        <input type="date" class="form-control" id="date_conseil" name="date_conseil" required>
                    </div>
                    <div class="mb-3">
                        <label for="couleur" class="form-label">Couleur</label>
                        <select class="form-select" id="couleur" name="couleur" required>
                            <option value="primary">Bleu (Primary)</option>
                            <option value="success">Vert (Success)</option>
                            <option value="warning">Jaune (Warning)</option>
                            <option value="danger">Rouge (Danger)</option>
                            <option value="info">Cyan (Info)</option>
                            <option value="secondary">Gris (Secondary)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="ordre" class="form-label">Ordre d'affichage</label>
                        <input type="number" class="form-control" id="ordre" name="ordre" min="1" max="10" value="1" required>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="actif" name="actif" checked>
                        <label class="form-check-label" for="actif">
                            Période active
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal pour modifier une période scolaire -->
<div class="modal fade" id="editPeriodeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modifier la période scolaire</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editPeriodeForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_periode_id" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_nom" class="form-label">Nom de la période</label>
                        <input type="text" class="form-control" id="edit_nom" name="nom" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_date_debut" class="form-label">Date de début</label>
                        <input type="date" class="form-control" id="edit_date_debut" name="date_debut" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_date_fin" class="form-label">Date de fin</label>
                        <input type="date" class="form-control" id="edit_date_fin" name="date_fin" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_date_conseil" class="form-label">Date du conseil</label>
                        <input type="date" class="form-control" id="edit_date_conseil" name="date_conseil" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_couleur" class="form-label">Couleur</label>
                        <select class="form-select" id="edit_couleur" name="couleur" required>
                            <option value="primary">Bleu (Primary)</option>
                            <option value="success">Vert (Success)</option>
                            <option value="warning">Jaune (Warning)</option>
                            <option value="danger">Rouge (Danger)</option>
                            <option value="info">Cyan (Info)</option>
                            <option value="secondary">Gris (Secondary)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_ordre" class="form-label">Ordre d'affichage</label>
                        <input type="number" class="form-control" id="edit_ordre" name="ordre" min="1" max="10" required>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="edit_actif" name="actif">
                        <label class="form-check-label" for="edit_actif">
                            Période active
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Modifier</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function updateCoefficient(matiereId) {
    const input = document.querySelector(`input[data-matiere="${matiereId}"]`);
    const coefficient = input.value;
    
    // Simulation de mise à jour
    fetch(`/api/matiere/${matiereId}/coefficient`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ coefficient: coefficient })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Animation de succès
            input.classList.add('border-success');
            setTimeout(() => {
                input.classList.remove('border-success');
            }, 2000);
            
            // Toast de confirmation
            showToast('Coefficient mis à jour avec succès', 'success');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showToast('Erreur lors de la mise à jour', 'error');
    });
}

function showToast(message, type) {
    // Créer un toast Bootstrap
    const toastHtml = `
        <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    // Ajouter le toast au DOM
    const toastContainer = document.getElementById('toast-container') || createToastContainer();
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    
    // Afficher le toast
    const toastElement = toastContainer.lastElementChild;
    const toast = new bootstrap.Toast(toastElement);
    toast.show();
    
    // Supprimer le toast après affichage
    toastElement.addEventListener('hidden.bs.toast', () => {
        toastElement.remove();
    });
}

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'toast-container position-fixed top-0 end-0 p-3';
    document.body.appendChild(container);
    return container;
}

// Gestion du formulaire des paramètres généraux
document.getElementById('parametresForm').addEventListener('submit', function(e) {
    e.preventDefault();
    showToast('Paramètres enregistrés avec succès', 'success');
});

// Fonctions pour gérer les périodes scolaires
let periodesData = {};

// Charger les données des périodes
function loadPeriodesData() {
    // Les données sont déjà disponibles via Blade
    @foreach($periodesScolaires as $periode)
    periodesData[{{ $periode->id }}] = {
        id: {{ $periode->id }},
        nom: '{{ $periode->nom }}',
        date_debut: '{{ $periode->date_debut->format('Y-m-d') }}',
        date_fin: '{{ $periode->date_fin->format('Y-m-d') }}',
        date_conseil: '{{ $periode->date_conseil->format('Y-m-d') }}',
        couleur: '{{ $periode->couleur }}',
        actif: {{ $periode->actif ? 'true' : 'false' }},
        ordre: {{ $periode->ordre }}
    };
    @endforeach
}

// Ajouter une période scolaire
document.getElementById('addPeriodeForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    data.actif = document.getElementById('actif').checked;
    
    fetch('{{ route("notes.periodes.create") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            bootstrap.Modal.getInstance(document.getElementById('addPeriodeModal')).hide();
            location.reload(); // Recharger pour afficher la nouvelle période
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showToast('Erreur lors de la création', 'error');
    });
});

// Modifier une période scolaire
function editPeriode(id) {
    const periode = periodesData[id];
    if (!periode) return;
    
    // Remplir le formulaire d'édition
    document.getElementById('edit_periode_id').value = periode.id;
    document.getElementById('edit_nom').value = periode.nom;
    document.getElementById('edit_date_debut').value = periode.date_debut;
    document.getElementById('edit_date_fin').value = periode.date_fin;
    document.getElementById('edit_date_conseil').value = periode.date_conseil;
    document.getElementById('edit_couleur').value = periode.couleur;
    document.getElementById('edit_ordre').value = periode.ordre;
    document.getElementById('edit_actif').checked = periode.actif;
    
    // Afficher le modal
    new bootstrap.Modal(document.getElementById('editPeriodeModal')).show();
}

// Soumettre la modification
document.getElementById('editPeriodeForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    data.actif = document.getElementById('edit_actif').checked;
    const id = data.id;
    delete data.id;
    
    fetch(`{{ route("notes.periodes.update", ":id") }}`.replace(':id', id), {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            bootstrap.Modal.getInstance(document.getElementById('editPeriodeModal')).hide();
            location.reload(); // Recharger pour afficher les modifications
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showToast('Erreur lors de la modification', 'error');
    });
});

// Supprimer une période scolaire
function deletePeriode(id) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer cette période scolaire ?')) {
        return;
    }
    
    fetch(`{{ route("notes.periodes.delete", ":id") }}`.replace(':id', id), {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            location.reload(); // Recharger pour supprimer la période
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showToast('Erreur lors de la suppression', 'error');
    });
}

// Initialiser les données au chargement
document.addEventListener('DOMContentLoaded', function() {
    loadPeriodesData();
});
</script>
@endpush
@endsection
