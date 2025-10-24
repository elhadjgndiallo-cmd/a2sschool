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
                                    <img src="{{ asset('storage/' . $enseignant->utilisateur->photo_profil) }}" 
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
                                   class="btn btn-outline-info" title="Voir détails"
                                   onclick="return testButton('enseignant', {{ $enseignant->id }})">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('enseignants.edit-simple', $enseignant->id) }}" 
                                   class="btn btn-outline-warning" title="Modifier"
                                   onclick="return testSimpleEditButton('enseignant', {{ $enseignant->id }})">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" 
                                        class="btn btn-outline-secondary" 
                                        onclick="resetPassword({{ $enseignant->id }})"
                                        title="Réinitialiser mot de passe">
                                    <i class="fas fa-key"></i>
                                </button>
                                @if($enseignant->actif)
                                    <form method="POST" action="{{ route('enseignants.destroy', $enseignant) }}" class="d-inline" 
                                          onsubmit="return confirm('Êtes-vous sûr de vouloir désactiver l\'enseignant {{ $enseignant->utilisateur->prenom }} {{ $enseignant->utilisateur->nom }} ?\n\nCette action rendra l\'enseignant inactif et il ne pourra plus accéder à son compte.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="btn btn-outline-danger" 
                                                title="Désactiver">
                                            <i class="fas fa-pause"></i>
                                        </button>
                                    </form>
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
                                
                                <!-- Bouton de suppression définitive -->
                                <form method="POST" action="{{ route('enseignants.delete-permanent', $enseignant) }}" class="d-inline" 
                                      onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer définitivement l\'enseignant {{ $enseignant->utilisateur->prenom }} {{ $enseignant->utilisateur->nom }} ?\n\nCette action supprimera :\n- L\'enseignant et son compte utilisateur\n- Tous ses salaires\n- Toutes ses cartes\n- Sa photo de profil\n- Toutes les relations avec les classes\n\nCette action est irréversible !')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="btn btn-outline-danger" 
                                            title="Supprimer définitivement">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
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
// Fonctions JavaScript complexes supprimées - utilisation de confirm() simple

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

@push('scripts')
<script>
function testButton(type, id) {
    console.log(`Test du bouton ${type} avec ID: ${id}`);
    
    // Afficher un message de test
    const message = `Test du bouton "Voir ${type}" pour l'ID: ${id}`;
    console.log(message);
    
    // Optionnel: Afficher une alerte pour confirmer que le bouton fonctionne
    // alert(message);
    
    // Retourner true pour permettre la navigation normale
    return true;
}

function testEditButton(type, id) {
    console.log(`Test du bouton modifier ${type} avec ID: ${id}`);
    
    // Afficher un message de test
    const message = `Test du bouton "Modifier ${type}" pour l'ID: ${id}`;
    console.log(message);
    
    // Vérifier les permissions avant la navigation
    checkEditPermissions(type, id);
    
    // Retourner true pour permettre la navigation normale
    return true;
}

function testSimpleEditButton(type, id) {
    console.log(`Test du bouton modification simple ${type} avec ID: ${id}`);
    
    // Afficher un message de test
    const message = `Test du bouton "Modification Simple ${type}" pour l'ID: ${id}`;
    console.log(message);
    
    // Vérifier les permissions avant la navigation
    checkEditPermissions(type, id);
    
    // Retourner true pour permettre la navigation normale
    return true;
}

function checkEditPermissions(type, id) {
    const permission = type === 'enseignant' ? 'enseignants.edit' : 'eleves.edit';
    
    fetch('/test-permissions', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log('Permissions actuelles:', data);
        
        if (data.permissions && data.permissions[permission]) {
            console.log(`✅ Permission ${permission} accordée`);
        } else {
            console.log(`❌ Permission ${permission} refusée`);
            alert(`Vous n'avez pas la permission de modifier les ${type}s. Contactez l'administrateur.`);
        }
    })
    .catch(error => {
        console.error('Erreur lors de la vérification des permissions:', error);
    });
}

// Fonction pour tester les permissions
function testPermissions() {
    fetch('/test-permissions', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log('Permissions testées:', data);
    })
    .catch(error => {
        console.error('Erreur lors du test des permissions:', error);
    });
}

// Tester les permissions au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page chargée, test des boutons "Voir" activé');
    testPermissions();
});
</script>
@endpush
