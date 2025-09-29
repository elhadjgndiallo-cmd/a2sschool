@extends('layouts.app')

@section('title', 'Gestion des Enseignants')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
<style>
    /* Styles responsive pour le tableau des enseignants */
    @media (max-width: 768px) {
        /* Masquer certaines colonnes sur mobile */
        .table th:nth-child(1),
        .table td:nth-child(1) {
            display: none;
        }
        
        .table th:nth-child(4),
        .table td:nth-child(4) {
            display: none;
        }
        
        .table th:nth-child(5),
        .table td:nth-child(5) {
            display: none;
        }
        
        .table th:nth-child(8),
        .table td:nth-child(8) {
            display: none;
        }
    }
    
    @media (max-width: 576px) {
        /* Masquer plus de colonnes sur très petit écran */
        .table th:nth-child(6),
        .table td:nth-child(6) {
            display: none;
        }
    }
</style>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center pt-3 pb-2 mb-3 border-bottom gap-2">
    <h1 class="h2 mb-0">
        <i class="fas fa-chalkboard-teacher me-2"></i>
        Gestion des Enseignants
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('enseignants.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>
            <span class="d-none d-sm-inline">Ajouter Enseignant</span>
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

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Liste des Enseignants</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Photo</th>
                        <th>Nom Complet</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Spécialité</th>
                        <th>Statut</th>
                        <th>Date Embauche</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($enseignants as $enseignant)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <div class="avatar-sm">
                                @if($enseignant->utilisateur && $enseignant->utilisateur->photo_profil && Storage::disk('public')->exists($enseignant->utilisateur->photo_profil))
                                    <img src="{{ asset('images/profile_images/' . basename($enseignant->utilisateur->photo_profi)) }}" 
                                         alt="Photo de {{ $enseignant->utilisateur->nom }} {{ $enseignant->utilisateur->prenom }}" 
                                         class="rounded-circle" 
                                         style="width: 40px; height: 40px; object-fit: cover;">
                                @else
                                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 40px; height: 40px;">
                                        {{ substr($enseignant->utilisateur->prenom, 0, 1) }}{{ substr($enseignant->utilisateur->nom, 0, 1) }}
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td>
                            <strong>{{ $enseignant->utilisateur->nom }} {{ $enseignant->utilisateur->prenom }}</strong>
                            <br>
                            <small class="text-muted">{{ $enseignant->utilisateur->sexe == 'M' ? 'Masculin' : 'Féminin' }}</small>
                        </td>
                        <td>{{ $enseignant->utilisateur->email }}</td>
                        <td>{{ $enseignant->utilisateur->telephone }}</td>
                        <td>{{ $enseignant->specialite }}</td>
                        <td>
                            <span class="badge bg-{{ $enseignant->actif ? 'success' : 'danger' }}">
                                {{ $enseignant->actif ? 'Actif' : 'Inactif' }}
                            </span>
                            <br>
                            <small class="badge bg-info">{{ ucfirst($enseignant->statut) }}</small>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($enseignant->date_embauche)->format('d/m/Y') }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('enseignants.show', $enseignant->id) }}" 
                                   class="btn btn-outline-info" title="Voir détails">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('enseignants.edit', $enseignant->id) }}" 
                                   class="btn btn-outline-warning" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" 
                                        class="btn btn-outline-secondary" 
                                        onclick="resetPassword({{ $enseignant->id }})"
                                        title="Réinitialiser mot de passe">
                                    <i class="fas fa-key"></i>
                                </button>
                                @if($enseignant->actif)
                                    <button type="button" 
                                            class="btn btn-outline-danger" 
                                            onclick="confirmDelete({{ $enseignant->id }})"
                                            title="Désactiver">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @else
                                    <form action="{{ route('enseignants.reactivate', $enseignant->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" 
                                                class="btn btn-outline-success" 
                                                title="Réactiver"
                                                onclick="return confirm('Êtes-vous sûr de vouloir réactiver cet enseignant ?')">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted">
                            <i class="fas fa-info-circle fa-2x mb-3"></i>
                            <p>Aucun enseignant trouvé.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <small class="text-muted">
                    Affichage de {{ $enseignants->firstItem() ?? 0 }} à {{ $enseignants->lastItem() ?? 0 }} sur {{ $enseignants->total() }} enseignant{{ $enseignants->total() > 1 ? 's' : '' }}
                </small>
            </div>
            <div>
                {{ $enseignants->appends(request()->query())->links('vendor.pagination.custom') }}
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmation de suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmer la désactivation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Êtes-vous sûr de vouloir désactiver cet enseignant ?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Désactiver</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function confirmDelete(id) {
    const form = document.getElementById('deleteForm');
    form.action = `{{ url('/enseignants') }}/${id}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

function resetPassword(id) {
    if (confirm('Êtes-vous sûr de vouloir réinitialiser le mot de passe de cet enseignant ?')) {
        // Créer un formulaire pour soumettre la requête POST avec CSRF token
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ url('/enseignants') }}/${id}/reset-password`;
        form.style.display = 'none';
        
        // Ajouter le token CSRF
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        form.appendChild(csrfToken);
        
        // Ajouter le formulaire au document et le soumettre
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush
@endsection
