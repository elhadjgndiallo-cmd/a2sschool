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

            <!-- Barème d'évaluation -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card mb-4">
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
            <div class="row">
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
                        <input type="text" class="form-control" id="nom" name="nom" placeholder="Ex. Trimestre 1 / Semestre 1" required>
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
                        <input type="number" class="form-control" id="ordre" name="ordre" min="1" max="10" value="{{ $periodesScolaires->count() + 1 }}" required>
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
const periodesListe = @json($periodesJson->values());
const periodesParId = Object.fromEntries(periodesListe.map((periode) => [String(periode.id), periode]));
const updatePeriodeUrlTemplate = @json(route('notes.periodes.update', ['id' => '__ID__']));
const deletePeriodeUrlTemplate = @json(route('notes.periodes.delete', ['id' => '__ID__']));

const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

function showToast(message, type) {
    const toastContainer = document.getElementById('toast-container') || createToastContainer();
    const toastElement = document.createElement('div');
    toastElement.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0`;
    toastElement.setAttribute('role', 'alert');
    toastElement.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    toastContainer.appendChild(toastElement);
    const toast = new bootstrap.Toast(toastElement);
    toast.show();
    toastElement.addEventListener('hidden.bs.toast', () => toastElement.remove());
}

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'toast-container position-fixed top-0 end-0 p-3';
    document.body.appendChild(container);
    return container;
}

async function parseJsonResponse(response) {
    const data = await response.json().catch(() => ({}));

    if (!response.ok) {
        let message = data.message || 'Une erreur est survenue';

        if (data.errors) {
            message = Object.values(data.errors).flat().join(' ');
        }

        throw new Error(message);
    }

    return data;
}

function buildPeriodeFormData(form, actifCheckboxId) {
    const formData = new FormData(form);
    formData.set('actif', document.getElementById(actifCheckboxId).checked ? '1' : '0');

    return formData;
}

document.getElementById('addPeriodeForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    try {
        const response = await fetch('{{ route("notes.periodes.create") }}', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: buildPeriodeFormData(this, 'actif'),
        });

        const data = await parseJsonResponse(response);

        if (data.success) {
            showToast(data.message, 'success');
            const modal = bootstrap.Modal.getInstance(document.getElementById('addPeriodeModal'))
                || bootstrap.Modal.getOrCreateInstance(document.getElementById('addPeriodeModal'));
            modal.hide();
            window.location.reload();
        }
    } catch (error) {
        showToast(error.message, 'error');
    }
});

function editPeriode(id) {
    const periode = periodesParId[String(id)];
    if (!periode) {
        showToast('Période introuvable', 'error');
        return;
    }

    document.getElementById('edit_periode_id').value = periode.id;
    document.getElementById('edit_nom').value = periode.nom;
    document.getElementById('edit_date_debut').value = periode.date_debut;
    document.getElementById('edit_date_fin').value = periode.date_fin;
    document.getElementById('edit_date_conseil').value = periode.date_conseil;
    document.getElementById('edit_couleur').value = periode.couleur;
    document.getElementById('edit_ordre').value = periode.ordre;
    document.getElementById('edit_actif').checked = !!periode.actif;

    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('editPeriodeModal'));
    modal.show();
}

document.getElementById('editPeriodeForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const id = document.getElementById('edit_periode_id').value;
    const formData = buildPeriodeFormData(this, 'edit_actif');
    formData.append('_method', 'PUT');

    try {
        const response = await fetch(updatePeriodeUrlTemplate.replace('__ID__', id), {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: formData,
        });

        const data = await parseJsonResponse(response);

        if (data.success) {
            showToast(data.message, 'success');
            const modal = bootstrap.Modal.getInstance(document.getElementById('editPeriodeModal'))
                || bootstrap.Modal.getOrCreateInstance(document.getElementById('editPeriodeModal'));
            modal.hide();
            window.location.reload();
        }
    } catch (error) {
        showToast(error.message, 'error');
    }
});

async function deletePeriode(id) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer cette période scolaire ?')) {
        return;
    }

    const formData = new FormData();
    formData.append('_method', 'DELETE');
    formData.append('_token', csrfToken);

    try {
        const response = await fetch(deletePeriodeUrlTemplate.replace('__ID__', id), {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: formData,
        });

        const data = await parseJsonResponse(response);

        if (data.success) {
            showToast(data.message, 'success');
            window.location.reload();
        }
    } catch (error) {
        showToast(error.message, 'error');
    }
}
</script>
@endpush
@endsection
