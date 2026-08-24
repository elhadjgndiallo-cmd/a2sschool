@extends('layouts.app')

@section('title', 'Gestion des Entrées')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Gestion des Entrées</h4>
                <p class="text-muted">Suivi des entrées d'argent de l'établissement</p>
            </div>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-success text-white h-100">
                <div class="card-body d-flex flex-column justify-content-center text-center">
                    <h2 class="card-title mb-0 font-weight-bold">{{ number_format($totalEntreesManuelles, 0, ',', ' ') }} GNF</h2>
                    <p class="card-text">Entrées Manuelles</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-primary text-white h-100">
                <div class="card-body d-flex flex-column justify-content-center text-center">
                    <h2 class="card-title mb-0 font-weight-bold">{{ number_format($totalPaiementsFrais, 0, ',', ' ') }} GNF</h2>
                    <p class="card-text">Frais de Scolarité</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white h-100">
                <div class="card-body d-flex flex-column justify-content-center text-center">
                    <h2 class="card-title mb-0 font-weight-bold">{{ number_format($totalGeneral, 0, ',', ' ') }} GNF</h2>
                    <p class="card-text">Total Revenus</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <form method="GET" action="{{ route('entrees.index') }}" class="mb-3">
        <div class="row g-2">
            <div class="col-12 col-sm-6 col-md-3">
                <select name="source" id="source" class="form-select" title="Source">
                    <option value="">Toutes les sources</option>
                    @foreach($sources as $source)
                        <option value="{{ $source }}" {{ request('source') == $source ? 'selected' : '' }}>
                            {{ $source }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-sm-6 col-md-3">
                <input type="date" name="date_debut" id="date_debut" class="form-control" value="{{ request('date_debut') }}" title="Date début" placeholder="Date début">
            </div>
            <div class="col-6 col-sm-6 col-md-3">
                <input type="date" name="date_fin" id="date_fin" class="form-control" value="{{ request('date_fin') }}" title="Date fin" placeholder="Date fin">
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="d-flex gap-1">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="fas fa-search"></i>
                        <span class="d-none d-sm-inline">Filtrer</span>
                    </button>
                    <a href="{{ route('entrees.index') }}" class="btn btn-outline-secondary" title="Réinitialiser">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </div>
        </div>
    </form>

    <!-- Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h5>Entrées Manuelles</h5>
                <a href="{{ route('entrees.create') }}" class="btn btn-success">
                    <i class="fas fa-plus me-1"></i>Nouvelle Entrée
                </a>
            </div>
        </div>
    </div>

    <!-- Tableau des entrées (manuelles et paiements scolaires) -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if($paginatedEntries->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Libellé</th>
                                        <th>Source</th>
                                        <th class="text-end" style="width: 150px;">Montant</th>
                                        <th>Mode de Paiement</th>
                                        <th>Enregistré par</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($paginatedEntries as $entry)
                                        @php
                                            $entryHref = match(true) {
                                                $entry->type == 'paiement' => route('paiements.show', $entry->data->fraisScolarite),
                                                default => route('entrees.show', $entry->data),
                                            };
                                        @endphp
                                        <tr class="table-row-clickable" data-href="{{ $entryHref }}" role="button" tabindex="0">
                                            <td>{{ $entry->date->format('d/m/Y') }}</td>
                                            <td>{{ $entry->description }}</td>
                                            <td>
                                                <span class="badge bg-{{ $entry->type == 'entree' ? 'info' : 'primary' }}">{{ $entry->source }}</span>
                                            </td>
                                            <td class="text-end text-success fw-bold" style="width: 150px;">{{ number_format($entry->montant, 0, ',', ' ') }} GNF</td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $entry->type == 'entree' ? ucfirst($entry->data->mode_paiement) : 'Automatique' }}</span>
                                            </td>
                                            <td>{{ $entry->enregistre_par->nom ?? 'N/A' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination Simple -->
                        @if($paginatedEntries->hasPages())
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div>
                                    <small class="text-muted">
                                        Affichage de {{ $paginatedEntries->firstItem() }} à {{ $paginatedEntries->lastItem() }} 
                                        sur {{ $paginatedEntries->total() }} entrées
                                    </small>
                                </div>
                                <div>
                                    <nav aria-label="Pagination">
                                        <ul class="pagination pagination-simple">
                                            <!-- Bouton Précédent -->
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

                                            <!-- Numéros de pages -->
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

                                            <!-- Bouton Suivant -->
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
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Aucune entrée manuelle trouvée.</p>
                            <a href="{{ route('entrees.create') }}" class="btn btn-success">
                                <i class="fas fa-plus me-1"></i>Créer la première entrée
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
