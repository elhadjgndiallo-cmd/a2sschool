@extends('layouts.app')

@section('title', 'Entrées - Comptabilité')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-arrow-up text-success me-2"></i>
                    Entrées (Revenus)
                </h2>
                <div class="btn-group">
                    <a href="{{ route('entrees.create') }}" class="btn btn-success">
                        <i class="fas fa-plus me-1"></i>Nouvelle Entrée
                    </a>
                    <button class="btn btn-outline-primary" onclick="window.print()">
                        <i class="fas fa-print me-1"></i>Imprimer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-filter me-2"></i>Filtres
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('comptabilite.entrees') }}">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="date_debut" class="form-label">Date de début</label>
                                <input type="date" class="form-control" id="date_debut" name="date_debut" 
                                       value="{{ request('date_debut') }}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="date_fin" class="form-label">Date de fin</label>
                                <input type="date" class="form-control" id="date_fin" name="date_fin" 
                                       value="{{ request('date_fin') }}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="source" class="form-label">Source</label>
                                <select class="form-select" id="source" name="source">
                                    <option value="">Toutes les sources</option>
                                    @foreach($sources as $source)
                                        <option value="{{ $source }}" {{ request('source') == $source ? 'selected' : '' }}>
                                            {{ $source }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-search me-1"></i>Filtrer
                                </button>
                                <a href="{{ route('comptabilite.entrees') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-refresh me-1"></i>Réinitialiser
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ number_format($statsEntrees['total'], 0, ',', ' ') }}</h3>
                    <small>Total (GNF)</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ $statsEntrees['nombre'] }}</h3>
                    <small>Nombre d'entrées</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ number_format($statsEntrees['moyenne'], 0, ',', ' ') }}</h3>
                    <small>Moyenne (GNF)</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des entrées -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>Liste des Entrées
                    </h5>
                </div>
                <div class="card-body">
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
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($paginatedEntries as $entry)
                                        <tr>
                                            <td>
                                                <i class="fas fa-calendar text-muted me-1"></i>
                                                {{ $entry->date->format('d/m/Y') }}
                                            </td>
                                            <td>
                                                <strong>{{ $entry->description }}</strong>
                                                <br><small class="text-muted">{{ $entry->source }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $entry->type == 'entree' ? 'info' : 'primary' }}">
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
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    @if($entry->type == 'entree' && !in_array($entry->source, ['Scolarité', 'Inscription', 'Réinscription', 'Transport', 'Cantine', 'Uniforme', 'Livres', 'Autres frais', 'Paiements scolaires']))
                                                        <a href="{{ route('entrees.show', $entry->data) }}" class="btn btn-outline-primary" title="Voir">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="{{ route('entrees.edit', $entry->data) }}" class="btn btn-outline-warning" title="Modifier">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form method="POST" action="{{ route('entrees.destroy', $entry->data) }}" 
                                                              style="display: inline;" 
                                                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette entrée ?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-outline-danger" title="Supprimer">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    @elseif($entry->type == 'paiement')
                                                        <a href="{{ route('paiements.show', $entry->data->fraisScolarite) }}" class="btn btn-outline-primary" title="Voir">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="{{ route('paiements.recu', $entry->data->fraisScolarite) }}" class="btn btn-outline-success" title="Reçu">
                                                            <i class="fas fa-receipt"></i>
                                                        </a>
                                                    @else
                                                        <a href="{{ route('entrees.show', $entry->data) }}" class="btn btn-outline-primary" title="Voir">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        @if(in_array($entry->source, ['Scolarité', 'Inscription', 'Réinscription', 'Transport', 'Cantine', 'Uniforme', 'Livres', 'Autres frais', 'Paiements scolaires']))
                                                            <a href="{{ route('entrees.recu', $entry->data) }}" class="btn btn-outline-success" title="Reçu">
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
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Aucune entrée trouvée</h5>
                            <p class="text-muted">Commencez par ajouter une nouvelle entrée.</p>
                            <a href="{{ route('entrees.create') }}" class="btn btn-success">
                                <i class="fas fa-plus me-1"></i>Ajouter une entrée
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
