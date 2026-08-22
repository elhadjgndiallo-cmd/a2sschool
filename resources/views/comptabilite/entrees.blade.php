@extends('layouts.app')

@section('title', 'Entrées - Comptabilité')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-arrow-up text-success me-2"></i>
                        <span class="d-none d-sm-inline">Entrées (Revenus)</span>
                        <span class="d-sm-none">Entrées</span>
                    </h3>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-success" onclick="window.print()">
                            <i class="fas fa-print me-1"></i>
                            <span class="d-none d-sm-inline">Imprimer</span>
                            <span class="d-sm-none">Print</span>
                        </button>
                        <a href="{{ route('entrees.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>
                            <span class="d-none d-sm-inline">Nouvelle Entrée</span>
                            <span class="d-sm-none">Nouveau</span>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filtres compacts (comme liste élèves) -->
                    <form method="GET" action="{{ route('comptabilite.entrees') }}" class="mb-3">
                        <div class="row g-2">
                            <div class="col-12 col-sm-6 col-md-2">
                                <select class="form-control" name="annee_scolaire_id" onchange="this.form.submit()">
                                    @foreach($anneesScolaires ?? \App\Models\AnneeScolaire::orderBy('date_debut','desc')->get() as $annee)
                                        <option value="{{ $annee->id }}" {{ request('annee_scolaire_id') == $annee->id ? 'selected' : ($annee->active && !request('annee_scolaire_id') ? 'selected' : '') }}>
                                            {{ $annee->nom }}{{ $annee->active ? ' (active)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6 col-sm-6 col-md-2">
                                <input type="date"
                                       class="form-control"
                                       name="date_debut"
                                       value="{{ request('date_debut') }}"
                                       title="Date de début"
                                       placeholder="Date début">
                            </div>
                            <div class="col-6 col-sm-6 col-md-2">
                                <input type="date"
                                       class="form-control"
                                       name="date_fin"
                                       value="{{ request('date_fin') }}"
                                       title="Date de fin"
                                       placeholder="Date fin">
                            </div>
                            <div class="col-12 col-sm-6 col-md-3">
                                <select class="form-control" name="source">
                                    <option value="">Toutes les sources</option>
                                    @foreach($sources as $source)
                                        <option value="{{ $source }}" {{ request('source') == $source ? 'selected' : '' }}>
                                            {{ $source }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 col-md-3">
                                <div class="d-flex gap-1">
                                    <button type="submit" class="btn btn-primary flex-fill">
                                        <i class="fas fa-search"></i>
                                        <span class="d-none d-sm-inline">Filtrer</span>
                                    </button>
                                    <a href="{{ route('comptabilite.entrees') }}" class="btn btn-outline-secondary" title="Réinitialiser">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="row g-2 mt-2" id="advancedFilters" style="display: none;">
                            <div class="col-12 col-sm-6 col-md-4">
                                <select class="form-control" name="type_entree">
                                    <option value="">Tous les types</option>
                                    <option value="manuelle" {{ request('type_entree') == 'manuelle' ? 'selected' : '' }}>
                                        Entrées manuelles
                                    </option>
                                    <option value="paiement" {{ request('type_entree') == 'paiement' ? 'selected' : '' }}>
                                        Paiements de scolarité
                                    </option>
                                </select>
                            </div>
                            <div class="col-6 col-sm-6 col-md-4">
                                <input type="number"
                                       class="form-control"
                                       name="montant_min"
                                       value="{{ request('montant_min') }}"
                                       min="0"
                                       step="1000"
                                       placeholder="Montant min (GNF)">
                            </div>
                            <div class="col-6 col-sm-6 col-md-4">
                                <input type="number"
                                       class="form-control"
                                       name="montant_max"
                                       value="{{ request('montant_max') }}"
                                       min="0"
                                       step="1000"
                                       placeholder="Montant max (GNF)">
                            </div>
                        </div>

                        <div class="mt-2">
                            <button type="button" class="btn btn-sm btn-outline-info" onclick="toggleAdvancedFilters()">
                                <i class="fas fa-cog"></i> Filtres avancés
                            </button>
                        </div>
                    </form>

                    <!-- Stats compactes -->
                    <div class="mb-3 d-flex flex-wrap gap-3 align-items-center">
                        <small class="text-muted">
                            <i class="fas fa-coins text-success me-1"></i>
                            Total : <strong class="text-success">{{ number_format($statsEntrees['total'], 0, ',', ' ') }} GNF</strong>
                        </small>
                        <small class="text-muted">
                            <i class="fas fa-list me-1"></i>
                            {{ $statsEntrees['nombre'] }} entrée(s)
                        </small>
                        @if(request()->hasAny(['date_debut', 'date_fin', 'source', 'type_entree', 'montant_min', 'montant_max']))
                            <small>
                                <a href="{{ route('comptabilite.entrees', request()->only('annee_scolaire_id')) }}" class="text-danger">
                                    <i class="fas fa-times"></i> Effacer les filtres
                                </a>
                            </small>
                        @endif
                    </div>

                    @if($paginatedEntries->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Libellé</th>
                                        <th>Source</th>
                                        <th class="text-end" style="width: 150px;">Montant</th>
                                        <th>Enregistré par</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($paginatedEntries as $entry)
                                        @php
                                            $entryHref = match(true) {
                                                $entry->type == 'paiement' => route('paiements.show', $entry->data->fraisScolarite),
                                                $entry->type == 'facture' => route('factures.show', $entry->data),
                                                default => route('entrees.show', $entry->data),
                                            };
                                        @endphp
                                        <tr class="table-row-clickable" data-href="{{ $entryHref }}" role="button" tabindex="0">
                                            <td>
                                                <i class="fas fa-calendar text-muted me-1"></i>
                                                {{ $entry->date->format('d/m/Y') }}
                                            </td>
                                            <td>
                                                <strong>{{ $entry->description }}</strong>
                                                @if(!empty($entry->detail))
                                                    <br><small class="text-muted">{{ $entry->detail }}</small>
                                                @else
                                                    <br><small class="text-muted">{{ $entry->source }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $entry->type == 'entree' ? 'info' : ($entry->type == 'facture' ? 'success' : 'primary') }}">
                                                    {{ $entry->source }}
                                                </span>
                                            </td>
                                            <td class="text-end" style="width: 150px;">
                                                <strong class="text-success">
                                                    {{ number_format($entry->montant, 0, ',', ' ') }} GNF
                                                </strong>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($entry->enregistre_par && $entry->enregistre_par->photo_profil)
                                                        <img src="{{ asset('storage/' . $entry->enregistre_par->photo_profil) }}"
                                                             alt="Photo" class="rounded-circle me-2"
                                                             style="width: 30px; height: 30px; object-fit: cover;">
                                                    @else
                                                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-2"
                                                             style="width: 30px; height: 30px;">
                                                            <i class="fas fa-user text-white" style="font-size: 12px;"></i>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <div class="fw-bold">{{ $entry->enregistre_par->nom ?? 'N/A' }} {{ $entry->enregistre_par->prenom ?? '' }}</div>
                                                        <small class="text-muted">{{ ucfirst($entry->enregistre_par->role ?? 'Système') }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                <small class="text-muted">
                                    Affichage de {{ $paginatedEntries->firstItem() }} à {{ $paginatedEntries->lastItem() }}
                                    sur {{ $paginatedEntries->total() }} entrées
                                </small>
                            </div>
                            <div>
                                <nav aria-label="Pagination">
                                    <ul class="pagination pagination-simple mb-0">
                                        @if($paginatedEntries->currentPage() > 1)
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $paginatedEntries->previousPageUrl() }}" aria-label="Précédent">
                                                    <i class="fas fa-chevron-left"></i> Précédent
                                                </a>
                                            </li>
                                        @else
                                            <li class="page-item disabled">
                                                <span class="page-link">
                                                    <i class="fas fa-chevron-left"></i> Précédent
                                                </span>
                                            </li>
                                        @endif

                                        @php
                                            $currentPage = $paginatedEntries->currentPage();
                                            $lastPage = $paginatedEntries->lastPage();
                                            $start = max(1, $currentPage - 2);
                                            $end = min($lastPage, $currentPage + 2);
                                        @endphp

                                        @for($i = $start; $i <= $end; $i++)
                                            <li class="page-item {{ $i == $currentPage ? 'active' : '' }}">
                                                <a class="page-link" href="{{ $paginatedEntries->url($i) }}">{{ $i }}</a>
                                            </li>
                                        @endfor

                                        @if($paginatedEntries->hasMorePages())
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $paginatedEntries->nextPageUrl() }}" aria-label="Suivant">
                                                    Suivant <i class="fas fa-chevron-right"></i>
                                                </a>
                                            </li>
                                        @else
                                            <li class="page-item disabled">
                                                <span class="page-link">
                                                    Suivant <i class="fas fa-chevron-right"></i>
                                                </span>
                                            </li>
                                        @endif
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Aucune entrée trouvée</h5>
                            <p class="text-muted">Commencez par ajouter une nouvelle entrée.</p>
                            <a href="{{ route('entrees.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>Ajouter une entrée
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleAdvancedFilters() {
    const advancedFilters = document.getElementById('advancedFilters');
    if (advancedFilters.style.display === 'none') {
        advancedFilters.style.display = 'flex';
    } else {
        advancedFilters.style.display = 'none';
    }
}

@if(request()->hasAny(['type_entree', 'montant_min', 'montant_max']))
document.addEventListener('DOMContentLoaded', function () {
    const advancedFilters = document.getElementById('advancedFilters');
    if (advancedFilters) {
        advancedFilters.style.display = 'flex';
    }
});
@endif
</script>
@endsection
