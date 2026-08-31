@extends('layouts.app')

@section('title', 'Liste des Élèves')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
<style>
    @media (max-width: 768px) {
        .avatar-sm img,
        .avatar-sm div {
            width: 30px !important;
            height: 30px !important;
            font-size: 12px !important;
            object-fit: cover !important;
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
                        @if(auth()->user()->hasPermission('eleves.create'))
                            <a href="{{ route('eleves.import.create') }}" class="btn btn-outline-primary">
                                <i class="fas fa-file-import me-1"></i>
                                <span class="d-none d-sm-inline">Importer Excel</span>
                                <span class="d-sm-none">Import</span>
                            </a>
                        @endif
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
                                    <th class="hide-sm">Profil</th>
                        <th class="hide-mobile">Matricule</th>
                                    <th>Prénom</th>
                                    <th class="col-sticky">Nom</th>
                        <th>Classe</th>
                        <th class="hide-sm">Statut</th>
                        <th class="hide-mobile">Frais</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($eleves as $eleve)
                                    <tr class="table-row-clickable" data-href="{{ route('eleves.show', $eleve) }}" role="button" tabindex="0">
                        <td class="hide-sm">
                            <x-profile-image 
                                :photo-path="$eleve->utilisateur->photo_profil ?? null"
                                :name="($eleve->utilisateur->prenom ?? '') . ' ' . ($eleve->utilisateur->nom ?? '')"
                                size="sm" />
                        </td>
                        <td class="hide-mobile">
                                            <span class="badge bg-info">{{ $eleve->numero_etudiant ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <strong>{{ $eleve->utilisateur->prenom ?? 'N/A' }}</strong>
                        </td>
                        <td class="col-sticky">
                                            <strong>{{ $eleve->utilisateur->nom ?? 'N/A' }}</strong>
                        </td>
                        <td>
                                            <strong>{{ $eleve->classe->nom ?? 'N/A' }}</strong>
                        </td>
                        <td class="hide-sm">
                                            @if($eleve->actif)
                                                <span class="badge bg-success">Actif</span>
                                    @else
                                                <span class="badge bg-secondary">Inactif</span>
                            @endif
                        </td>
                        <td class="hide-mobile">
                            @if($eleve->exempte_frais)
                                <span class="badge bg-danger">NON</span>
                            @else
                                <span class="badge bg-success">OUI</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                                        <td colspan="7" class="text-center text-muted">
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