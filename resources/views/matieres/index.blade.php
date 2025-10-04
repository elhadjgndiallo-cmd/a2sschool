@extends('layouts.app')

@section('title', 'Gestion des Matières')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-book me-2"></i>
        Gestion des Matières
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('matieres.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>
                Ajouter Matière
            </a>
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

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show">
    <i class="fas fa-exclamation-circle me-2"></i>
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- Statistiques -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $matieres->total() }}</h4>
                        <p class="mb-0">Total Matières</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-book fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $matieres->where('actif', true)->count() }}</h4>
                        <p class="mb-0">Matières Actives</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $matieres->where('actif', false)->count() }}</h4>
                        <p class="mb-0">Matières Inactives</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-pause-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ number_format($matieres->avg('coefficient'), 1) }}</h4>
                        <p class="mb-0">Coefficient Moyen</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-calculator fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Liste des matières -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Liste des Matières</h5>
    </div>
    <div class="card-body">
        @if($matieres->count() > 0)
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Nom</th>
                        <th>Coefficient</th>
                        <th>Enseignants</th>
                        <th>Couleur</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($matieres as $matiere)
                    <tr>
                        <td>
                            <span class="badge" style="background-color: {{ $matiere->couleur }}; color: white;">
                                {{ $matiere->code }}
                            </span>
                        </td>
                        <td>
                            <strong>{{ $matiere->nom }}</strong>
                            @if($matiere->description)
                            <br><small class="text-muted">{{ Str::limit($matiere->description, 50) }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-secondary fs-6">{{ $matiere->coefficient }}</span>
                        </td>
                        <td>
                            @if($matiere->enseignants->count() > 0)
                                @foreach($matiere->enseignants->take(2) as $enseignant)
                                <span class="badge bg-info me-1">{{ $enseignant->nom }}</span>
                                @endforeach
                                @if($matiere->enseignants->count() > 2)
                                <span class="badge bg-light text-dark">+{{ $matiere->enseignants->count() - 2 }}</span>
                                @endif
                            @else
                                <span class="text-muted">Aucun enseignant</span>
                            @endif
                        </td>
                        <td>
                            <div class="color-preview" style="width: 30px; height: 20px; background-color: {{ $matiere->couleur }}; border: 1px solid #ddd; border-radius: 3px;"></div>
                        </td>
                        <td>
                            @if($matiere->actif)
                            <span class="badge bg-success">Actif</span>
                            @else
                            <span class="badge bg-secondary">Inactif</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('matieres.show', $matiere) }}" 
                                   class="btn btn-outline-info" title="Voir détails">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('matieres.edit', $matiere) }}" 
                                   class="btn btn-outline-primary" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if($matiere->actif)
                                <button type="button" class="btn btn-outline-warning" 
                                        onclick="confirmDeactivate({{ $matiere->id }}, '{{ $matiere->nom }}')" 
                                        title="Désactiver">
                                    <i class="fas fa-pause"></i>
                                </button>
                                @else
                                <form method="POST" action="{{ route('matieres.reactivate', $matiere) }}" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-outline-success" title="Réactiver">
                                        <i class="fas fa-play"></i>
                                    </button>
                                </form>
                                @endif
                                <button type="button" class="btn btn-outline-danger" 
                                        onclick="confirmDelete({{ $matiere->id }}, '{{ $matiere->nom }}')" 
                                        title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <small class="text-muted">
                    Affichage de {{ $matieres->firstItem() ?? 0 }} à {{ $matieres->lastItem() ?? 0 }} sur {{ $matieres->total() }} matière{{ $matieres->total() > 1 ? 's' : '' }}
                </small>
            </div>
            <div>
                {{ $matieres->appends(request()->query())->links('vendor.pagination.custom') }}
            </div>
        </div>
        @else
        <div class="text-center py-5">
            <i class="fas fa-book fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">Aucune matière trouvée</h5>
            <p class="text-muted">Commencez par ajouter des matières à votre système</p>
            <a href="{{ route('matieres.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Ajouter la première matière
            </a>
        </div>
        @endif
    </div>
</div>

<!-- Modal de confirmation de désactivation -->
<div class="modal fade" id="deactivateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmer la désactivation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir désactiver la matière <strong id="deactivate-matiere-name"></strong> ?</p>
                <p class="text-warning"><i class="fas fa-exclamation-triangle me-2"></i>Cette action rendra la matière indisponible pour les nouvelles notes.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form id="deactivate-form" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-warning">Désactiver</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmation de suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">⚠️ Supprimer la matière</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <h6><i class="fas fa-exclamation-triangle me-2"></i>ATTENTION : Action irréversible</h6>
                    <p class="mb-0">Êtes-vous sûr de vouloir supprimer définitivement la matière <strong id="delete-matiere-name"></strong> ?</p>
                </div>
                <p class="text-warning"><i class="fas fa-exclamation-triangle me-2"></i>Cette action supprimera définitivement la matière et toutes ses données associées.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form id="delete-form" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Supprimer définitivement</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmation de suppression totale -->
<div class="modal fade" id="deleteAllModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">⚠️ Suppression Totale</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <h6><i class="fas fa-exclamation-triangle me-2"></i>ATTENTION : Action irréversible</h6>
                    <p class="mb-0">Cette action va supprimer :</p>
                    <ul class="mt-2">
                        <li>Toutes les matières</li>
                        <li>Tous les emplois du temps</li>
                        <li>Toutes les notes</li>
                        <li>Toutes les associations enseignant-matière</li>
                    </ul>
                </div>
                <p>Tapez <strong>"SUPPRIMER"</strong> pour confirmer :</p>
                <input type="text" id="confirmText" class="form-control" placeholder="Tapez SUPPRIMER">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteAll" disabled>
                    <i class="fas fa-trash me-2"></i>Supprimer Tout
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function confirmDeactivate(matiereId, matiereName) {
    document.getElementById('deactivate-matiere-name').textContent = matiereName;
    document.getElementById('deactivate-form').action = `/matieres/${matiereId}`;
    new bootstrap.Modal(document.getElementById('deactivateModal')).show();
}

function confirmDelete(matiereId, matiereName) {
    document.getElementById('delete-matiere-name').textContent = matiereName;
    document.getElementById('delete-form').action = `/matieres/${matiereId}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

function confirmDeleteAll() {
    new bootstrap.Modal(document.getElementById('deleteAllModal')).show();
}

// Gestion de la confirmation de suppression totale
document.getElementById('confirmText').addEventListener('input', function() {
    const confirmBtn = document.getElementById('confirmDeleteAll');
    if (this.value === 'SUPPRIMER') {
        confirmBtn.disabled = false;
        confirmBtn.classList.remove('btn-danger');
        confirmBtn.classList.add('btn-outline-danger');
    } else {
        confirmBtn.disabled = true;
        confirmBtn.classList.remove('btn-outline-danger');
        confirmBtn.classList.add('btn-danger');
    }
});

document.getElementById('confirmDeleteAll').addEventListener('click', function() {
    // Créer un formulaire de suppression totale
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/matieres/delete-all';
    
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = document.querySelector('meta[name="csrf-token"]').content;
    
    form.appendChild(csrfToken);
    document.body.appendChild(form);
    form.submit();
});
</script>
@endpush
@endsection

