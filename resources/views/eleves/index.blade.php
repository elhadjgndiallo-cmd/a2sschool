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
                    <div class="d-flex gap-2">
                        <a href="{{ route('eleves.print', request()->query()) }}" class="btn btn-success" target="_blank">
                            <i class="fas fa-print me-1"></i>
                            <span class="d-none d-sm-inline">Imprimer</span>
                            <span class="d-sm-none">Print</span>
                        </a>
                        <a href="{{ route('eleves.create') }}" class="btn btn-primary">
                            <i class="fas fa-user-plus me-1"></i>
                            <span class="d-none d-sm-inline">Nouvelle Inscription</span>
                            <span class="d-sm-none">Nouveau</span>
                        </a>
                    </div>
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

                    <!-- Filtres côté serveur -->
                    <form method="GET" action="{{ route('eleves.index') }}" class="mb-3">
                        <div class="row g-2">
                            <div class="col-12 col-sm-6 col-md-3">
                                <input type="text" 
                                       class="form-control" 
                                       name="search" 
                                       value="{{ request('search') }}"
                                       placeholder="Rechercher par nom, prénom ou matricule...">
                            </div>
                            <div class="col-12 col-sm-6 col-md-2">
                                <select class="form-control" name="classe_id">
                                    <option value="">Toutes les classes</option>
                                    @foreach($classes as $classe)
                                        <option value="{{ $classe->id }}" 
                                                {{ request('classe_id') == $classe->id ? 'selected' : '' }}>
                                            {{ $classe->nom }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 col-md-2">
                                <select class="form-control" name="actif">
                                    <option value="">Tous les statuts</option>
                                    <option value="1" {{ request('actif') === '1' ? 'selected' : '' }}>Actifs</option>
                                    <option value="0" {{ request('actif') === '0' ? 'selected' : '' }}>Inactifs</option>
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 col-md-2">
                                <div class="d-flex gap-1">
                                    @if(request('sort') === 'name_asc')
                                        <a href="{{ route('eleves.index', array_merge(request()->except('sort'), ['sort' => 'default'])) }}" 
                                           class="btn btn-success flex-fill" 
                                           title="Tri A-Z actif - Cliquez pour revenir au tri par défaut">
                                            <i class="fas fa-sort-alpha-down"></i>
                                            <span class="d-none d-sm-inline">A-Z</span>
                                        </a>
                                    @else
                                        <a href="{{ route('eleves.index', array_merge(request()->all(), ['sort' => 'name_asc'])) }}" 
                                           class="btn btn-outline-success flex-fill" 
                                           title="Trier par ordre alphabétique (A-Z)">
                                            <i class="fas fa-sort-alpha-down"></i>
                                            <span class="d-none d-sm-inline">Trier A-Z</span>
                                        </a>
                                    @endif
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-md-3">
                                <div class="d-flex gap-1">
                                    <button type="submit" class="btn btn-primary flex-fill">
                                        <i class="fas fa-search"></i>
                                        <span class="d-none d-sm-inline">Filtrer</span>
                                    </button>
                                    <a href="{{ route('eleves.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Filtres avancés (cachés par défaut) -->
                        <div class="row mt-2" id="advancedFilters" style="display: none;">
                            <div class="col-12 col-sm-6 col-md-4">
                                <select class="form-control" name="annee_scolaire_id">
                                    <option value="">Toutes les années</option>
                                    @foreach($anneesScolarires as $annee)
                                        <option value="{{ $annee->id }}" 
                                                {{ request('annee_scolaire_id') == $annee->id ? 'selected' : '' }}>
                                            {{ $annee->nom }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 col-md-4">
                                <select class="form-control" name="statut">
                                    <option value="">Tous les statuts</option>
                                    @foreach($statutsEleves as $statut)
                                        <option value="{{ $statut }}" 
                                                {{ request('statut') == $statut ? 'selected' : '' }}>
                                            {{ ucfirst(str_replace('_', ' ', $statut)) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 col-md-4">
                                <select class="form-control" name="per_page">
                                    <option value="20" {{ request('per_page') == '20' ? 'selected' : '' }}>20 par page</option>
                                    <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50 par page</option>
                                    <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100 par page</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mt-2">
                            <button type="button" class="btn btn-sm btn-outline-info" onclick="toggleAdvancedFilters()">
                                <i class="fas fa-cog"></i> Filtres avancés
                            </button>
                        </div>
                    </form>

                    <!-- Indicateur de résultats -->
                    <div class="mb-2">
                        <small class="text-muted">
                            @if(request()->hasAny(['search', 'classe_id', 'actif', 'annee_scolaire_id', 'statut']))
                                <i class="fas fa-filter me-1"></i>
                                Filtres actifs - {{ $eleves->total() }} résultat(s) trouvé(s)
                                <a href="{{ route('eleves.index') }}" class="text-danger ms-2">
                                    <i class="fas fa-times"></i> Effacer les filtres
                                </a>
                            @else
                                {{ $eleves->total() }} élève(s) au total
                            @endif
                        </small>
                    </div>

                    <!-- Tableau simplifié -->
                    <div class="table-responsive">
                        <table class="table table-striped" id="elevesTable">
                            <thead class="thead-dark">
                    <tr>
                                    <th>Profil</th>
                        <th>Matricule</th>
                                    <th>Prénom</th>
                                    <th>Nom</th>
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
                                            <strong>{{ $eleve->utilisateur->prenom ?? 'N/A' }}</strong>
                        </td>
                        <td>
                                            <strong>{{ $eleve->utilisateur->nom ?? 'N/A' }}</strong>
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
                                                    <form method="POST" action="{{ route('eleves.deactivate', $eleve) }}" class="d-inline" 
                                                          onsubmit="return confirm('Êtes-vous sûr de vouloir désactiver l\'élève {{ $eleve->utilisateur->prenom }} {{ $eleve->utilisateur->nom }} ?\n\nCette action rendra l\'élève inactif et il ne pourra plus accéder à son compte.')">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn btn-sm btn-danger" 
                                                                title="Désactiver l'élève">
                                                            <i class="fas fa-pause"></i>
                                                        </button>
                                                    </form>
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
                                                
                                                <!-- Bouton de suppression définitive -->
                                                <form method="POST" action="{{ route('eleves.delete-permanent', $eleve) }}" class="d-inline" 
                                                      onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer définitivement l\'élève {{ $eleve->utilisateur->prenom }} {{ $eleve->utilisateur->nom }} ?\n\nCette action supprimera :\n- L\'élève et son compte utilisateur\n- Tous ses frais de scolarité\n- Toutes ses notes\n- Toutes ses absences\n- Ses cartes scolaires\n- Sa photo de profil\n- Toutes les relations avec les parents\n\nCette action est irréversible !')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                            title="Supprimer définitivement">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                                
                                    @if($eleve->exempte_frais)
                                        <button class="btn btn-sm btn-secondary" 
                                                title="Élève exempté des frais de scolarité" 
                                                disabled>
                                            <i class="fas fa-gift"></i>
                                        </button>
                                    @elseif($eleve->fraisScolarite->where('type_frais', 'scolarite')->count() > 0)
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
                                    
                                    @if($eleve->cartesScolaires->where('statut', 'active')->count() > 0)
                                        <button class="btn btn-sm btn-info" 
                                                title="Carte scolaire active ({{ $eleve->cartesScolaires->where('statut', 'active')->first()->numero_carte }})" 
                                                disabled>
                                            <i class="fas fa-id-card"></i>
                                        </button>
                                    @else
                                        <a href="{{ route('cartes-scolaires.create') }}?eleve_id={{ $eleve->id }}" 
                                           class="btn btn-sm btn-primary" 
                                           title="Créer une carte scolaire">
                                            <i class="fas fa-id-card"></i>
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

<!-- Modals supprimés - utilisation de confirm() simple -->

<script>
// Méthodes simplifiées - utilisation de confirm() natif

// JavaScript pour les filtres côté serveur
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM chargé');
    
    // Auto-submit du formulaire quand on change les filtres principaux
    const searchInput = document.querySelector('input[name="search"]');
    const classeSelect = document.querySelector('select[name="classe_id"]');
    const actifSelect = document.querySelector('select[name="actif"]');
    
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (this.value.length >= 3 || this.value.length === 0) {
                    this.form.submit();
                }
            }, 500);
        });
    }
    
    if (classeSelect) {
        classeSelect.addEventListener('change', function() {
            this.form.submit();
        });
    }
    
    if (actifSelect) {
        actifSelect.addEventListener('change', function() {
            this.form.submit();
        });
    }
    
    // Vérifier que la fonction est disponible après le chargement du DOM
    console.log('Après DOM chargé, fonction confirmPermanentDelete:', typeof confirmPermanentDelete);
});

function toggleAdvancedFilters() {
    const advancedFilters = document.getElementById('advancedFilters');
    const button = event.target;
    
    if (advancedFilters.style.display === 'none') {
        advancedFilters.style.display = 'block';
        button.innerHTML = '<i class="fas fa-cog"></i> Masquer les filtres avancés';
    } else {
        advancedFilters.style.display = 'none';
        button.innerHTML = '<i class="fas fa-cog"></i> Filtres avancés';
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

// Fonctions complexes supprimées - utilisation de confirm() simple dans les formulaires
</script>
@endpush