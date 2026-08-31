@extends('layouts.app')

@section('title', 'Gestion des Enseignants')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
<style>
    /* Amélioration de l'affichage des numéros de téléphone sur mobile/tablette */
    .phone-link {
        display: inline-flex;
        align-items: center;
        padding: 4px 8px;
        border-radius: 4px;
        transition: all 0.3s ease;
    }
    
    .phone-link:hover {
        background-color: #f0f0f0;
        transform: scale(1.05);
    }
    
    .phone-number {
        font-weight: 600;
        letter-spacing: 0.5px;
    }
    
    /* Sur mobile et tablette */
    @media (max-width: 768px) {
        .phone-number {
            font-size: 16px !important;
            font-weight: 700 !important;
            color: #0d6efd !important;
            letter-spacing: 1px;
        }
        
        .phone-link {
            padding: 4px 8px;
            background-color: #e7f3ff;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
        }
        
        .phone-link i {
            font-size: 18px;
            color: #0d6efd;
            margin-right: 8px;
        }
        
        .phone-link:active {
            background-color: #b3d9ff;
            transform: scale(0.98);
        }
        
    }
    
    @media (max-width: 576px) {
        .phone-number {
            font-size: 18px !important;
            font-weight: 700 !important;
        }
        
        .phone-link {
            padding: 6px 10px;
            width: auto;
            justify-content: flex-start;
        }
    }
</style>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center pt-3 pb-2 mb-3 border-bottom gap-2">
    <h1 class="h2 mb-0">
        <i class="fas fa-chalkboard-teacher me-2"></i>
        Gestion des Enseignants
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0 gap-2">
        <a href="{{ route('enseignants.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>
            <span class="d-none d-sm-inline">Ajouter Enseignant</span>
            <span class="d-sm-none">Ajouter</span>
        </a>
        @if(auth()->user()->hasPermission('enseignants.create'))
        <a href="{{ route('enseignants.reinscription') }}" class="btn btn-success">
            <i class="fas fa-user-check me-1"></i>
            <span class="d-none d-sm-inline">Réinscription</span>
            <span class="d-sm-none">Réinscrire</span>
        </a>
        @endif
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
        <form method="GET" action="{{ route('enseignants.index') }}" class="mb-3">
            <div class="row g-2">
                <div class="col-12 col-sm-6 col-md-3">
                    <input type="text"
                           class="form-control"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Nom, prénom, email, tél., n° employé...">
                </div>
                <div class="col-12 col-sm-6 col-md-2">
                    <select class="form-select" name="specialite" title="Spécialité">
                        <option value="">Toutes les spécialités</option>
                        @foreach($specialites as $specialite)
                            <option value="{{ $specialite }}" {{ request('specialite') == $specialite ? 'selected' : '' }}>
                                {{ $specialite }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-md-2">
                    <select class="form-select" name="statut" title="Statut">
                        <option value="">Tous les contrats</option>
                        <option value="titulaire" {{ request('statut') == 'titulaire' ? 'selected' : '' }}>Titulaire</option>
                        <option value="contractuel" {{ request('statut') == 'contractuel' ? 'selected' : '' }}>Contractuel</option>
                        <option value="vacataire" {{ request('statut') == 'vacataire' ? 'selected' : '' }}>Vacataire</option>
                    </select>
                </div>
                <div class="col-6 col-sm-4 col-md-2">
                    <select class="form-select" name="actif" title="Actif">
                        <option value="">Actifs / Inactifs</option>
                        <option value="1" {{ request('actif') === '1' ? 'selected' : '' }}>Actifs</option>
                        <option value="0" {{ request('actif') === '0' ? 'selected' : '' }}>Inactifs</option>
                    </select>
                </div>
                <div class="col-6 col-sm-4 col-md-1">
                    <select class="form-select" name="sexe" title="Sexe">
                        <option value="">Sexe</option>
                        <option value="M" {{ request('sexe') == 'M' ? 'selected' : '' }}>M</option>
                        <option value="F" {{ request('sexe') == 'F' ? 'selected' : '' }}>F</option>
                    </select>
                </div>
                <div class="col-12 col-sm-8 col-md-2">
                    <div class="d-flex gap-1">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="fas fa-search"></i>
                            <span class="d-none d-sm-inline">Filtrer</span>
                        </button>
                        <a href="{{ route('enseignants.index') }}" class="btn btn-outline-secondary" title="Effacer">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="row g-2 mt-1">
                <div class="col-12 col-sm-6 col-md-3">
                    <select class="form-select" name="annee_scolaire_id" title="Année scolaire">
                        <option value="">Année scolaire active</option>
                        @foreach($anneesScolaires as $annee)
                            <option value="{{ $annee->id }}" {{ (string) request('annee_scolaire_id') === (string) $annee->id ? 'selected' : '' }}>
                                {{ $annee->nom }}{{ $annee->active ? ' (active)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>

        <div class="mb-2">
            <small class="text-muted">
                @if(request()->hasAny(['search', 'specialite', 'statut', 'actif', 'sexe', 'annee_scolaire_id']))
                    <i class="fas fa-filter me-1"></i>
                    Filtres actifs — {{ $enseignants->total() }} résultat(s)
                    <a href="{{ route('enseignants.index') }}" class="text-danger ms-2">
                        <i class="fas fa-times"></i> Effacer
                    </a>
                @else
                    {{ $enseignants->total() }} enseignant(s)
                    @if($anneeScolaireActive)
                        — {{ $anneeScolaireActive->nom }}
                    @endif
                @endif
            </small>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th class="hide-mobile">#</th>
                        <th class="hide-sm">Photo</th>
                        <th class="col-sticky">Nom Complet</th>
                        <th class="hide-mobile">Email</th>
                        <th>Téléphone</th>
                        <th>Spécialité</th>
                        <th class="hide-sm">Statut</th>
                        <th class="hide-mobile">Date Embauche</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($enseignants as $enseignant)
                    <tr class="table-row-clickable" data-href="{{ route('enseignants.show', $enseignant) }}" role="button" tabindex="0">
                        <td class="hide-mobile">{{ $loop->iteration }}</td>
                        <td class="hide-sm">
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
                        <td class="col-sticky">
                            <strong>{{ $enseignant->utilisateur->prenom }} {{ $enseignant->utilisateur->nom }}</strong>
                            <br>
                            <small class="text-muted">{{ $enseignant->utilisateur->sexe == 'M' ? 'Masculin' : 'Féminin' }}</small>
                        </td>
                        <td class="hide-mobile">{{ $enseignant->utilisateur->email }}</td>
                        <td>
                            @if($enseignant->utilisateur->telephone)
                                <a href="tel:{{ $enseignant->utilisateur->telephone }}" class="phone-link text-decoration-none" onclick="event.stopPropagation()">
                                    <i class="fas fa-phone me-1"></i>
                                    <span class="phone-number">{{ $enseignant->utilisateur->telephone }}</span>
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $enseignant->specialite }}</td>
                        <td class="hide-sm">
                            <span class="badge bg-{{ $enseignant->actif ? 'success' : 'danger' }}">
                                {{ $enseignant->actif ? 'Actif' : 'Inactif' }}
                            </span>
                            <br>
                            <small class="badge bg-info">{{ ucfirst($enseignant->statut) }}</small>
                        </td>
                        <td class="hide-mobile">{{ \Carbon\Carbon::parse($enseignant->date_embauche)->format('d/m/Y') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">
                            <i class="fas fa-info-circle fa-2x mb-3"></i>
                            <p>Aucun enseignant ne correspond aux critères.</p>
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
