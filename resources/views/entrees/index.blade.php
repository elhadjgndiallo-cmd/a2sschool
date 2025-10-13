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
                    <p class="card-text">Total Entrées</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Filtres</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('entrees.index') }}">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="source" class="form-label">Source</label>
                                <select name="source" id="source" class="form-select">
                                    <option value="">Toutes les sources</option>
                                    @foreach($sources as $source)
                                        <option value="{{ $source }}" {{ request('source') == $source ? 'selected' : '' }}>
                                            {{ $source }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="date_debut" class="form-label">Date début</label>
                                <input type="date" name="date_debut" id="date_debut" class="form-control" value="{{ request('date_debut') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="date_fin" class="form-label">Date fin</label>
                                <input type="date" name="date_fin" id="date_fin" class="form-control" value="{{ request('date_fin') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-1"></i>Filtrer
                                    </button>
                                    <a href="{{ route('entrees.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times me-1"></i>Effacer
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

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
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($paginatedEntries as $entry)
                                        <tr>
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
                                            <td>
                                                <div class="btn-group" role="group">
                                                    @if($entry->type == 'entree' && !in_array($entry->source, ['Scolarité', 'Inscription', 'Réinscription', 'Transport', 'Cantine', 'Uniforme', 'Livres', 'Autres frais', 'Paiements scolaires']))
                                                        <a href="{{ route('entrees.show', $entry->data) }}" class="btn btn-sm btn-info">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="{{ route('entrees.edit', $entry->data) }}" class="btn btn-sm btn-warning">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form action="{{ route('entrees.destroy', $entry->data) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette entrée ?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    @elseif($entry->type == 'paiement')
                                                        <a href="{{ route('paiements.show', $entry->data->fraisScolarite) }}" class="btn btn-sm btn-info">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="{{ route('paiements.recu', $entry->data->fraisScolarite) }}" class="btn btn-sm btn-success">
                                                            <i class="fas fa-receipt"></i>
                                                        </a>
                                                    @else
                                                        <a href="{{ route('entrees.show', $entry->data) }}" class="btn btn-sm btn-info">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        @if(in_array($entry->source, ['Scolarité', 'Inscription', 'Réinscription', 'Transport', 'Cantine', 'Uniforme', 'Livres', 'Autres frais', 'Paiements scolaires']))
                                                            <a href="{{ route('entrees.recu', $entry->data) }}" class="btn btn-sm btn-success">
                                                                <i class="fas fa-receipt"></i>
                                                            </a>
                                                        @endif
                                                    @endif
                                                </div>
                                            </td>
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
