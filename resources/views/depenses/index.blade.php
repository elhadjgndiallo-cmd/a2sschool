@extends('layouts.app')

@section('title', 'Gestion des Sorties')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-arrow-down mr-2"></i>
                        Gestion des Sorties
                    </h3>
                    <div>
                        <a href="{{ route('depenses.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus mr-1"></i>
                            Nouvelle Sortie
                        </a>
                        <a href="{{ route('depenses.rapports') }}" class="btn btn-info">
                            <i class="fas fa-chart-bar mr-1"></i>
                            Rapports
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

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    @endif

                    <!-- Filtres -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form method="GET" action="{{ route('depenses.index') }}">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="search">Recherche</label>
                                            <input type="text" name="search" id="search" class="form-control" 
                                                   value="{{ request('search') }}" placeholder="Libellé, bénéficiaire...">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="type_depense">Type</label>
                                            <select name="type_depense" id="type_depense" class="form-control">
                                                <option value="">Tous les types</option>
                                                <option value="salaire_enseignant" {{ request('type_depense') == 'salaire_enseignant' ? 'selected' : '' }}>Salaire Enseignant</option>
                                                <option value="salaire_personnel" {{ request('type_depense') == 'salaire_personnel' ? 'selected' : '' }}>Salaire Personnel</option>
                                                <option value="achat_materiel" {{ request('type_depense') == 'achat_materiel' ? 'selected' : '' }}>Achat Matériel</option>
                                                <option value="maintenance" {{ request('type_depense') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                                <option value="electricite" {{ request('type_depense') == 'electricite' ? 'selected' : '' }}>Électricité</option>
                                                <option value="eau" {{ request('type_depense') == 'eau' ? 'selected' : '' }}>Eau</option>
                                                <option value="autre" {{ request('type_depense') == 'autre' ? 'selected' : '' }}>Autre</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="statut">Statut</label>
                                            <select name="statut" id="statut" class="form-control">
                                                <option value="">Tous les statuts</option>
                                                <option value="en_attente" {{ request('statut') == 'en_attente' ? 'selected' : '' }}>En Attente</option>
                                                <option value="approuve" {{ request('statut') == 'approuve' ? 'selected' : '' }}>Approuvé</option>
                                                <option value="paye" {{ request('statut') == 'paye' ? 'selected' : '' }}>Payé</option>
                                                <option value="annule" {{ request('statut') == 'annule' ? 'selected' : '' }}>Annulé</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="date_debut">Date Début</label>
                                            <input type="date" name="date_debut" id="date_debut" class="form-control" 
                                                   value="{{ request('date_debut') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="date_fin">Date Fin</label>
                                            <input type="date" name="date_fin" id="date_fin" class="form-control" 
                                                   value="{{ request('date_fin') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <button type="submit" class="btn btn-primary btn-block">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Date</th>
                                    <th>Libellé</th>
                                    <th>Type</th>
                                    <th>Bénéficiaire</th>
                                    <th>Montant</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($depenses as $depense)
                                    <tr>
                                        <td>{{ $depense->date_depense->format('d/m/Y') }}</td>
                                        <td>
                                            <strong>{{ $depense->libelle }}</strong>
                                            @if($depense->reference_facture)
                                                <br>
                                                <small class="text-muted">Ref: {{ $depense->reference_facture }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-info">
                                                {{ $depense->type_depense_libelle }}
                                            </span>
                                        </td>
                                        <td>{{ $depense->beneficiaire ?? '-' }}</td>
                                        <td>
                                            <strong class="text-danger">
                                                {{ number_format($depense->montant, 0, ',', ' ') }} GNF
                                            </strong>
                                        </td>
                                        <td>
                                            @switch($depense->statut)
                                                @case('en_attente')
                                                    <span class="badge badge-warning">En Attente</span>
                                                    @break
                                                @case('approuve')
                                                    <span class="badge badge-info">Approuvé</span>
                                                    @break
                                                @case('paye')
                                                    <span class="badge badge-success">Payé</span>
                                                    @break
                                                @case('annule')
                                                    <span class="badge badge-danger">Annulé</span>
                                                    @break
                                                @default
                                                    <span class="badge badge-secondary">{{ $depense->statut }}</span>
                                            @endswitch
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('depenses.show', $depense) }}" 
                                                   class="btn btn-sm btn-info" title="Voir détails">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('depenses.edit', $depense) }}" 
                                                   class="btn btn-sm btn-warning" title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @if($depense->statut === 'en_attente')
                                                    <form action="{{ route('depenses.approuver', $depense) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success" title="Approuver">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                @if($depense->statut === 'approuve')
                                                    <a href="{{ route('depenses.payer', $depense) }}" 
                                                       class="btn btn-sm btn-primary" title="Payer">
                                                        <i class="fas fa-money-bill-wave"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">
                                            <i class="fas fa-inbox fa-2x mb-2"></i>
                                            <br>
                                            Aucune sortie trouvée
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination des dépenses -->
                    @if($depenses->hasPages())
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                <small class="text-muted">
                                    Affichage de {{ $depenses->firstItem() ?? 0 }} à {{ $depenses->lastItem() ?? 0 }} sur {{ $depenses->total() }} sortie{{ $depenses->total() > 1 ? 's' : '' }}
                                </small>
                            </div>
                            <div>
                                {{ $depenses->appends(request()->query())->links('vendor.pagination.custom') }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
.pagination {
    margin-bottom: 0;
    justify-content: center;
}

.pagination .page-link {
    color: #007bff;
    border: 1px solid #dee2e6;
    padding: 0.5rem 0.75rem;
    margin: 0 2px;
    border-radius: 0.375rem;
    text-decoration: none;
    transition: all 0.2s ease;
}

.pagination .page-link:hover {
    color: #0056b3;
    background-color: #e9ecef;
    border-color: #dee2e6;
    text-decoration: none;
}

.pagination .page-item.active .page-link {
    background-color: #007bff;
    border-color: #007bff;
    color: white;
}

.pagination .page-item.disabled .page-link {
    color: #6c757d;
    background-color: #fff;
    border-color: #dee2e6;
    cursor: not-allowed;
}

.pagination .page-item.disabled .page-link:hover {
    background-color: #fff;
    border-color: #dee2e6;
    color: #6c757d;
}
</style>
@endsection
