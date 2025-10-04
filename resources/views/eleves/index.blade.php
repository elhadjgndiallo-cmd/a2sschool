@extends('layouts.app')

@section('title', 'Liste des Élèves')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
<style>
    /* Styles spécifiques pour le tableau des élèves */
    @media (max-width: 768px) {
        /* Masquer certaines colonnes sur mobile */
        .table th:nth-child(2),
        .table td:nth-child(2) {
            display: none;
        }
        
        .table th:nth-child(6),
        .table td:nth-child(6) {
            display: none;
        }
        
        /* Ajuster les avatars */
        .avatar-sm img,
        .avatar-sm div {
            width: 30px !important;
            height: 30px !important;
            font-size: 12px !important;
            object-fit: cover !important;
        }
    }
    
    @media (max-width: 576px) {
        /* Masquer plus de colonnes sur très petit écran */
        .table th:nth-child(7),
        .table td:nth-child(7) {
            display: none;
        }
    }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-user-graduate me-2"></i>
                        Liste des Élèves
                    </h3>
                    <a href="{{ route('eleves.create') }}" class="btn btn-primary">
                        <i class="fas fa-user-plus me-1"></i>
                        <span class="d-none d-sm-inline">Nouvelle Inscription</span>
                        <span class="d-sm-none">Nouveau</span>
                    </a>
                </div>
                <div class="card-body">
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
</div>
@endif

                    <!-- Filtres simples -->
                    <div class="row mb-3 g-2">
                        <div class="col-12 col-sm-6 col-md-4">
                            <input type="text" class="form-control" placeholder="Rechercher par nom ou prénom..." id="searchInput">
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <select class="form-control" id="classeFilter">
                                <option value="">Toutes les classes</option>
                                @foreach($classes as $classe)
                                    <option value="{{ $classe->id }}">{{ $classe->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <select class="form-control" id="statutFilter">
                                <option value="">Tous les statuts</option>
                                <option value="actif">Actifs</option>
                                <option value="inactif">Inactifs</option>
                            </select>
                        </div>
                        <div class="col-12 col-sm-6 col-md-2">
                            <button class="btn btn-outline-secondary w-100" onclick="clearFilters()">
                                <i class="fas fa-times"></i> 
                                <span class="d-none d-sm-inline">Effacer</span>
                            </button>
                        </div>
                    </div>

                    <!-- Indicateur de résultats -->
                    <div class="mb-2">
                        <small class="text-muted">
                            <span id="resultsCount">Chargement...</span>
                        </small>
                    </div>

                    <!-- Tableau simplifié -->
                    <div class="table-responsive">
                        <table class="table table-striped" id="elevesTable">
                            <thead class="thead-dark">
                    <tr>
                                    <th>Profil</th>
                        <th>Matricule</th>
                                    <th>Nom</th>
                                    <th>Prénom</th>
                        <th>Classe</th>
                        <th>Statut</th>
                        <th>Frais</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($eleves as $eleve)
                                    <tr>
                        <td>
                            <x-profile-image 
                                :photo-path="$eleve->utilisateur->photo_profil ?? null"
                                :name="($eleve->utilisateur->prenom ?? '') . ' ' . ($eleve->utilisateur->nom ?? '')"
                                size="sm" />
                        </td>
                        <td>
                                            <span class="badge bg-info">{{ $eleve->numero_etudiant ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <strong>{{ $eleve->utilisateur->nom ?? 'N/A' }}</strong>
                        </td>
                        <td>
                                            <strong>{{ $eleve->utilisateur->prenom ?? 'N/A' }}</strong>
                        </td>
                        <td>
                                            <strong>{{ $eleve->classe->nom ?? 'N/A' }}</strong>
                            <br>
                                            <small class="text-muted">{{ $eleve->classe->niveau ?? '' }}</small>
                        </td>
                        <td>
                                            @if($eleve->actif)
                                                <span class="badge bg-success">Actif</span>
                                    @else
                                                <span class="badge bg-secondary">Inactif</span>
                            @endif
                        </td>
                        <td>
                            @if($eleve->exempte_frais)
                                <span class="badge bg-danger">
                                    <i class="fas fa-gift me-1"></i>
                                    NON
                                </span>
                                <br>
                                                <small class="text-muted">Exempté des frais</small>
                            @else
                                <span class="badge bg-success">
                                    <i class="fas fa-credit-card me-1"></i>
                                    OUI
                                </span>
                                                <br>
                                @if($eleve->paiement_annuel)
                                                    <small class="badge bg-info">Paiement annuel</small>
                                                @else
                                                    <small class="text-muted">Paiement mensuel</small>
                                @endif
                            @endif
                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('eleves.show', $eleve) }}" 
                                                   class="btn btn-sm btn-info" 
                                                   title="Voir détails"
                                                   onclick="return testButton('eleve', {{ $eleve->id }})">
                                    <i class="fas fa-eye"></i>
                                </a>
                                                <a href="{{ route('eleves.edit', $eleve) }}" 
                                                   class="btn btn-sm btn-warning" 
                                                   title="Modifier"
                                                   onclick="return testEditButton('eleve', {{ $eleve->id }})">
                                    <i class="fas fa-edit"></i>
                                </a>
                                                
                                                @if($eleve->actif)
                                                    <button type="button" class="btn btn-sm btn-danger" 
                                                            title="Désactiver l'élève"
                                                            onclick="confirmDeactivate({{ $eleve->id }}, '{{ $eleve->utilisateur->prenom }} {{ $eleve->utilisateur->nom }}')">
                                                        <i class="fas fa-pause"></i>
                                                    </button>
                                                @else
                                                    <form method="POST" action="{{ route('eleves.reactivate', $eleve) }}" class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn btn-sm btn-success" 
                                                                title="Réactiver l'élève">
                                                            <i class="fas fa-play"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                
                                    @if($eleve->exempte_frais)
                                        <button class="btn btn-sm btn-secondary" 
                                                title="Élève exempté des frais de scolarité" 
                                                disabled>
                                            <i class="fas fa-gift"></i>
                                        </button>
                                    @elseif($eleve->fraisScolarite->count() > 0)
                                        <button class="btn btn-sm btn-warning" 
                                                title="Frais de scolarité déjà créés pour cet élève" 
                                                disabled>
                                            <i class="fas fa-check-circle"></i>
                                        </button>
                                    @else
                                        <a href="{{ route('paiements.create') }}?eleve_id={{ $eleve->id }}" 
                                           class="btn btn-sm btn-success" 
                                           title="Créer des frais de scolarité">
                                            <i class="fas fa-credit-card"></i>
                                        </a>
                                    @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                                        <td colspan="8" class="text-center text-muted">
                                            <i class="fas fa-inbox fa-2x mb-2"></i>
                                            <br>
                                            Aucun élève trouvé
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
                    <!-- Pagination des élèves -->
                @if($eleves->hasPages())
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            <small class="text-muted">
                                Affichage de {{ $eleves->firstItem() ?? 0 }} à {{ $eleves->lastItem() ?? 0 }} sur {{ $eleves->total() }} élève{{ $eleves->total() > 1 ? 's' : '' }}
                            </small>
                        </div>
                        <div>
                            {{ $eleves->appends(request()->query())->links('vendor.pagination.custom') }}
                        </div>
                    </div>
                @endif
            </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmation de désactivation -->
<div class="modal fade" id="deactivateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">⚠️ Désactiver l'élève</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <h6><i class="fas fa-exclamation-triangle me-2"></i>ATTENTION : Action importante</h6>
                    <p class="mb-0">Êtes-vous sûr de vouloir désactiver l'élève <strong id="deactivate-eleve-name"></strong> ?</p>
                </div>
                <p class="text-warning"><i class="fas fa-exclamation-triangle me-2"></i>Cette action rendra l'élève inactif et il ne pourra plus accéder à son compte.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form id="deactivate-form" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Désactiver l'élève</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Filtres simples avec JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser le compteur au chargement
    updateResultsCount();
    
    // Ajouter les événements
    document.getElementById('searchInput').addEventListener('keyup', filterTable);
    document.getElementById('classeFilter').addEventListener('change', filterTable);
    document.getElementById('statutFilter').addEventListener('change', filterTable);
});

function filterTable() {
    const searchInput = document.getElementById('searchInput').value.toLowerCase();
    const classeFilter = document.getElementById('classeFilter');
    const statutFilter = document.getElementById('statutFilter');
    const table = document.getElementById('elevesTable');
    const rows = table.getElementsByTagName('tr');

    for (let i = 1; i < rows.length; i++) {
        const row = rows[i];
        const nom = row.cells[2].textContent.toLowerCase(); // Colonne Nom (après Profil et Matricule)
        const prenom = row.cells[3].textContent.toLowerCase(); // Colonne Prénom
        const classe = row.cells[4].textContent.toLowerCase(); // Colonne Classe
        const statut = row.cells[5].textContent.toLowerCase(); // Colonne Statut

        let show = true;

        // Recherche dans le nom ET le prénom
        if (searchInput && !nom.includes(searchInput) && !prenom.includes(searchInput)) {
            show = false;
        }

        // Filtre par classe - comparer avec le nom de la classe sélectionnée
        if (classeFilter.value) {
            const selectedClasseText = classeFilter.options[classeFilter.selectedIndex].text.toLowerCase();
            if (!classe.includes(selectedClasseText)) {
                show = false;
            }
        }

        // Filtre par statut
        if (statutFilter.value) {
            if (statutFilter.value === 'actif' && !statut.includes('actif')) {
                show = false;
            } else if (statutFilter.value === 'inactif' && !statut.includes('inactif')) {
                show = false;
            }
        }

        row.style.display = show ? '' : 'none';
    }
    
    // Mettre à jour le nombre de résultats
    updateResultsCount();
}

function clearFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('classeFilter').value = '';
    document.getElementById('statutFilter').value = '';
    filterTable();
}

// Fonction pour afficher le nombre de résultats filtrés
function updateResultsCount() {
    const table = document.getElementById('elevesTable');
    const rows = table.getElementsByTagName('tr');
    let visibleCount = 0;
    let totalCount = rows.length - 1; // -1 pour exclure l'en-tête
    
    for (let i = 1; i < rows.length; i++) {
        if (rows[i].style.display !== 'none') {
            visibleCount++;
        }
    }
    
    // Afficher le nombre de résultats dans l'interface
    const resultsElement = document.getElementById('resultsCount');
    if (resultsElement) {
        if (visibleCount === totalCount) {
            resultsElement.textContent = `${totalCount} élève(s) au total`;
        } else {
            resultsElement.textContent = `${visibleCount} élève(s) affiché(s) sur ${totalCount}`;
        }
    }
}
</script>
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

// Fonction pour confirmer la désactivation d'un élève
function confirmDeactivate(eleveId, eleveName) {
    document.getElementById('deactivate-eleve-name').textContent = eleveName;
    document.getElementById('deactivate-form').action = `/eleves/${eleveId}`;
    new bootstrap.Modal(document.getElementById('deactivateModal')).show();
}
</script>
@endpush