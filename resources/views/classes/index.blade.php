@extends('layouts.app')

@section('title', 'Gestion des Classes')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center pt-3 pb-2 mb-3 border-bottom gap-2">
    <h1 class="h2 mb-0">
        <i class="fas fa-school me-2"></i>
        Gestion des Classes
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('classes.create') }}" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i>
            <span class="d-none d-sm-inline">Ajouter une classe</span>
            <span class="d-sm-none">Ajouter</span>
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
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card">
    <div class="card-header bg-white">
        <div class="row align-items-center g-2">
            <div class="col-12 col-md-6">
                <h5 class="mb-0">Liste des Classes</h5>
            </div>
            <div class="col-12 col-md-6">
                <form action="{{ route('classes.index') }}" method="GET" class="d-flex">
                    <input type="text" name="search" class="form-control form-control-sm me-2" placeholder="Rechercher..." value="{{ request('search') }}">
                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-search"></i>
                        <span class="d-none d-sm-inline ms-1">Rechercher</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col">Nom</th>
                        <th scope="col">Niveau</th>
                        <th scope="col">Section</th>
                        <th scope="col">Effectif</th>
                        <th scope="col">Statut</th>
                        <th scope="col" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($classes as $classe)
                    <tr>
                        <td>{{ $classe->nom }}</td>
                        <td>{{ $classe->niveau }}</td>
                        <td>{{ $classe->section }}</td>
                        <td>{{ $classe->effectif_actuel }} / {{ $classe->effectif_max }}</td>
                        <td>
                            <span class="badge bg-{{ $classe->actif ? 'success' : 'danger' }}">
                                {{ $classe->actif ? 'Actif' : 'Inactif' }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('classes.show', $classe->id) }}" class="btn btn-outline-primary" title="Voir détails">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('classes.edit', $classe->id) }}" class="btn btn-outline-secondary" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('emplois-temps.show', $classe->id) }}" class="btn btn-outline-info" title="Emploi du temps">
                                    <i class="fas fa-calendar-alt"></i>
                                </a>
                                <button type="button" class="btn btn-outline-danger" onclick="confirmDelete({{ $classe->id }})" title="Désactiver">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <p class="text-muted mb-0">
                                <i class="fas fa-info-circle me-1"></i>
                                Aucune classe trouvée
                            </p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <small class="text-muted">Affichage de {{ $classes->firstItem() ?? 0 }} à {{ $classes->lastItem() ?? 0 }} sur {{ $classes->total() }} classe{{ $classes->total() > 1 ? 's' : '' }}</small>
            </div>
            <div>
                {{ $classes->appends(request()->query())->links('vendor.pagination.custom') }}
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmation de suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir désactiver cette classe ?</p>
                <p class="text-danger"><small>Cette action ne supprimera pas la classe mais la désactivera.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Désactiver</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function confirmDelete(id) {
        const form = document.getElementById('deleteForm');
        form.action = `/classes/${id}`;
        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();
    }
</script>
@endsection
