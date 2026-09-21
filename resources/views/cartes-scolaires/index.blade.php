@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-id-card text-primary me-2"></i>
                        Gestion des Cartes Scolaires
                    </h3>
                    <div class="card-tools d-flex flex-wrap gap-2">
                        <button type="button" id="btnImprimerPlusieurs" class="btn btn-info" style="display: none;" onclick="imprimerPlusieurs()">
                            <i class="fas fa-print me-2"></i>Imprimer la sélection
                        </button>
                        <button type="button" id="btnRenouvelerSelection" class="btn btn-success" style="display: none;" onclick="ouvrirRenouvellement(false)">
                            <i class="fas fa-sync me-2"></i>Renouveler la sélection
                        </button>
                        @if(request('classe_id'))
                            <button type="button" class="btn btn-outline-success" onclick="ouvrirRenouvellement(true)">
                                <i class="fas fa-sync me-2"></i>Renouveler toute la classe
                            </button>
                        @endif
                        <a href="{{ route('cartes-scolaires.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Nouvelle Carte
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            {{ session('success') }}
                            @if(session('nouvelles_cartes'))
                                <div class="mt-2">
                                    <a class="btn btn-sm btn-outline-success" target="_blank"
                                       href="{{ route('cartes-scolaires.imprimer-plusieurs', ['cartes' => implode(',', session('nouvelles_cartes'))]) }}">
                                        <i class="fas fa-print me-1"></i>Imprimer les nouvelles cartes
                                    </a>
                                </div>
                            @endif
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <!-- Filtres -->
                    <form method="GET" action="{{ route('cartes-scolaires.index') }}" class="mb-3">
                        <div class="row g-2">
                            <div class="col-12 col-sm-6 col-md-2">
                                <select class="form-select" id="statut" name="statut" title="Statut">
                                    <option value="">Tous les statuts</option>
                                    <option value="active" {{ request('statut') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="expiree" {{ request('statut') == 'expiree' ? 'selected' : '' }}>Expirée</option>
                                    <option value="suspendue" {{ request('statut') == 'suspendue' ? 'selected' : '' }}>Suspendue</option>
                                    <option value="annulee" {{ request('statut') == 'annulee' ? 'selected' : '' }}>Annulée</option>
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 col-md-2">
                                <select class="form-select" id="type_carte" name="type_carte" title="Type de carte">
                                    <option value="">Tous les types</option>
                                    <option value="standard" {{ request('type_carte') == 'standard' ? 'selected' : '' }}>Standard</option>
                                    <option value="temporaire" {{ request('type_carte') == 'temporaire' ? 'selected' : '' }}>Temporaire</option>
                                    <option value="remplacement" {{ request('type_carte') == 'remplacement' ? 'selected' : '' }}>Remplacement</option>
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 col-md-2">
                                <select class="form-select" id="classe_id" name="classe_id" title="Classe">
                                    <option value="">Toutes les classes</option>
                                    @foreach($classes as $classe)
                                        <option value="{{ $classe->id }}" {{ (string) request('classe_id') === (string) $classe->id ? 'selected' : '' }}>
                                            {{ $classe->nom }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 col-md-2">
                                <select class="form-select" id="eleve_id" name="eleve_id" title="Élève">
                                    <option value="">Tous les élèves</option>
                                    @foreach($eleves as $eleve)
                                        <option value="{{ $eleve->id }}" {{ request('eleve_id') == $eleve->id ? 'selected' : '' }}>
                                            {{ $eleve->utilisateur->nom }} {{ $eleve->utilisateur->prenom }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 col-md-2">
                                <input type="text"
                                       class="form-control"
                                       id="numero_carte"
                                       name="numero_carte"
                                       value="{{ request('numero_carte') }}"
                                       placeholder="N° carte">
                            </div>
                            <div class="col-12 col-sm-6 col-md-2">
                                <div class="d-flex gap-1">
                                    <button type="submit" class="btn btn-primary flex-fill">
                                        <i class="fas fa-search"></i>
                                        <span class="d-none d-sm-inline">Filtrer</span>
                                    </button>
                                    <a href="{{ route('cartes-scolaires.index') }}" class="btn btn-outline-secondary" title="Réinitialiser">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Tableau des cartes -->
                    <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th width="40" class="hide-sm">
                                            <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                                        </th>
                                        <th class="hide-mobile">Numéro</th>
                                        <th>Élève</th>
                                        <th class="hide-sm">Classe</th>
                                        <th class="hide-mobile">Type</th>
                                        <th class="hide-mobile">Date d'émission</th>
                                        <th class="hide-mobile">Date d'expiration</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                            <tbody>
                                @forelse($cartes as $carte)
                                    <tr class="table-row-clickable" data-href="{{ route('cartes-scolaires.show', $carte) }}" role="button" tabindex="0">
                                        <td class="hide-sm">
                                            <input type="checkbox" name="cartes[]" value="{{ $carte->id }}" class="carte-checkbox" onchange="updateImprimerButton()" onclick="event.stopPropagation()">
                                        </td>
                                        <td class="hide-mobile">
                                            <span class="badge bg-info">{{ $carte->numero_carte }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($carte->eleve->utilisateur->photo_profil)
                                                    <img src="{{ asset('storage/' . $carte->eleve->utilisateur->photo_profil) }}" 
                                                         class="rounded-circle me-2" 
                                                         width="30" height="30" 
                                                         alt="Photo">
                                                @else
                                                    <div class="bg-secondary rounded-circle me-2 d-flex align-items-center justify-content-center" 
                                                         style="width: 30px; height: 30px;">
                                                        <i class="fas fa-user text-white"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <strong>{{ $carte->eleve->utilisateur->nom }} {{ $carte->eleve->utilisateur->prenom }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $carte->eleve->numero_etudiant }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="hide-sm">
                                            <span class="badge bg-secondary">
                                                {{ $carte->eleve->classe->nom ?? 'Non assigné' }}
                                            </span>
                                        </td>
                                        <td class="hide-mobile">
                                            <span class="badge bg-primary">{{ $carte->type_carte_libelle }}</span>
                                        </td>
                                        <td class="hide-mobile">{{ $carte->date_emission->format('d/m/Y') }}</td>
                                        <td class="hide-mobile">
                                            <span class="{{ $carte->date_expiration < now() ? 'text-danger' : 'text-success' }}">
                                                {{ $carte->date_expiration->format('d/m/Y') }}
                                            </span>
                                        </td>
                                        <td>
                                            @php
                                                $badgeClass = match($carte->statut) {
                                                    'active' => 'bg-success',
                                                    'expiree' => 'bg-danger',
                                                    'suspendue' => 'bg-warning',
                                                    'annulee' => 'bg-secondary',
                                                    default => 'bg-secondary'
                                                };
                                            @endphp
                                            <span class="badge {{ $badgeClass }}">{{ $carte->statut_libelle }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-id-card fa-3x mb-3"></i>
                                                <p>Aucune carte scolaire trouvée.</p>
                                                <a href="{{ route('cartes-scolaires.create') }}" class="btn btn-primary">
                                                    <i class="fas fa-plus me-2"></i>Créer la première carte
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($cartes->hasPages())
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div>
                                <small class="text-muted">
                                    Affichage de {{ $cartes->firstItem() ?? 0 }} à {{ $cartes->lastItem() ?? 0 }} sur {{ $cartes->total() }} carte{{ $cartes->total() > 1 ? 's' : '' }} scolaire{{ $cartes->total() > 1 ? 's' : '' }}
                                </small>
                            </div>
                            <div>
                                {{ $cartes->appends(request()->query())->links('vendor.pagination.custom') }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.carte-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    
    updateImprimerButton();
}

function updateImprimerButton() {
    const checked = document.querySelectorAll('.carte-checkbox:checked');
    const btnImprimer = document.getElementById('btnImprimerPlusieurs');
    const btnRenouveler = document.getElementById('btnRenouvelerSelection');
    
    if (checked.length > 0) {
        btnImprimer.style.display = 'inline-block';
        btnImprimer.innerHTML = `<i class="fas fa-print me-2"></i>Imprimer (${checked.length})`;
        if (btnRenouveler) {
            btnRenouveler.style.display = 'inline-block';
            btnRenouveler.innerHTML = `<i class="fas fa-sync me-2"></i>Renouveler (${checked.length})`;
        }
    } else {
        btnImprimer.style.display = 'none';
        if (btnRenouveler) btnRenouveler.style.display = 'none';
    }
    
    // Mettre à jour la checkbox "Tout sélectionner"
    const selectAll = document.getElementById('selectAll');
    const allCheckboxes = document.querySelectorAll('.carte-checkbox');
    selectAll.checked = allCheckboxes.length > 0 && checked.length === allCheckboxes.length;
}

function ouvrirRenouvellement(touteClasse) {
    const form = document.getElementById('formRenouvelerPlusieurs');
    const idsWrap = document.getElementById('renouvelerCartesIds');
    const touteClasseInput = document.getElementById('renouvelerTouteClasse');
    idsWrap.innerHTML = '';

    if (touteClasse) {
        touteClasseInput.value = '1';
    } else {
        touteClasseInput.value = '0';
        const checked = document.querySelectorAll('.carte-checkbox:checked');
        if (checked.length === 0) {
            alert('Veuillez sélectionner au moins une carte.');
            return;
        }
        checked.forEach(function (checkbox) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'cartes[]';
            input.value = checkbox.value;
            idsWrap.appendChild(input);
        });
    }

    const modalEl = document.getElementById('renouvelerPlusieursModal');
    if (window.bootstrap) {
        new bootstrap.Modal(modalEl).show();
    } else {
        form.submit();
    }
}

function imprimerPlusieurs() {
    const checked = document.querySelectorAll('.carte-checkbox:checked');
    
    if (checked.length === 0) {
        alert('Veuillez sélectionner au moins une carte.');
        return;
    }
    
    // Collecter les IDs des cartes sélectionnées
    const carteIds = Array.from(checked).map(checkbox => checkbox.value);
    
    // Construire l'URL avec les paramètres et cache-busting
    const timestamp = new Date().getTime();
    const url = '{{ route("cartes-scolaires.imprimer-plusieurs") }}?cartes=' + carteIds.join(',') + '&t=' + timestamp;
    
    // Ouvrir dans un nouvel onglet
    window.open(url, '_blank');
}

// Mettre à jour le bouton au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    updateImprimerButton();

    const dateExpirationInput = document.getElementById('date_expiration_lot');
    if (dateExpirationInput && !dateExpirationInput.value) {
        const today = new Date();
        const oneYearFromNow = new Date(today.getFullYear() + 1, today.getMonth(), today.getDate());
        dateExpirationInput.value = oneYearFromNow.toISOString().split('T')[0];
    }
});
</script>
@endsection

@push('modals')
<div class="modal fade" id="renouvelerPlusieursModal" tabindex="-1" aria-labelledby="renouvelerPlusieursModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formRenouvelerPlusieurs" method="POST" action="{{ route('cartes-scolaires.renouveler-plusieurs') }}">
                @csrf
                <input type="hidden" name="classe_id" value="{{ request('classe_id') }}">
                <input type="hidden" name="toute_classe" id="renouvelerTouteClasse" value="0">
                <div id="renouvelerCartesIds"></div>
                <div class="modal-header">
                    <h5 class="modal-title" id="renouvelerPlusieursModalLabel">
                        <i class="fas fa-sync me-2"></i>Renouveler les cartes
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">
                        Chaque nouvelle carte reprendra les <strong>informations actuelles de l’élève</strong>
                        (nom, prénom, photo, classe, matricule). L’ancienne carte sera annulée.
                    </p>
                    <div class="mb-3">
                        <label for="date_expiration_lot" class="form-label">Date d’expiration des nouvelles cartes <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="date_expiration_lot" name="date_expiration"
                               min="{{ now()->addDay()->format('Y-m-d') }}" required>
                    </div>
                    <div class="mb-0">
                        <label for="observations_lot" class="form-label">Observations (optionnel)</label>
                        <textarea class="form-control" id="observations_lot" name="observations" rows="3"
                                  placeholder="Ex. Renouvellement après réinscription / changement de classe"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success"
                            onclick="return confirm('Renouveler les cartes sélectionnées avec les informations actuelles des élèves ?');">
                        <i class="fas fa-sync me-1"></i>Confirmer le renouvellement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush


