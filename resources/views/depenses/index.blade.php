@extends('layouts.app')

@section('title', 'Gestion des Sorties')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="card-title mb-0">
                            <i class="fas fa-arrow-down mr-2"></i>
                            Gestion des Sorties
                        </h3>
                        @if($anneeScolaire ?? null)
                            <small class="text-muted">
                                <i class="fas fa-calendar-alt me-1"></i>
                                Année scolaire active : <strong>{{ $anneeScolaire->nom }}</strong>
                            </small>
                        @endif
                    </div>
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
                    <form method="GET" action="{{ route('depenses.index') }}" class="mb-3">
                        <div class="row g-2">
                            <div class="col-12 col-sm-6 col-md-3">
                                <input type="text" name="search" id="search" class="form-control"
                                       value="{{ request('search') }}" placeholder="Libellé, bénéficiaire...">
                            </div>
                            <div class="col-12 col-sm-6 col-md-2">
                                <select name="type_depense" id="type_depense" class="form-control" title="Type">
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
                            <div class="col-12 col-sm-6 col-md-2">
                                <select name="statut" id="statut" class="form-control" title="Statut">
                                    <option value="">Tous les statuts</option>
                                    <option value="en_attente" {{ request('statut') == 'en_attente' ? 'selected' : '' }}>En Attente</option>
                                    <option value="approuve" {{ request('statut') == 'approuve' ? 'selected' : '' }}>Approuvé</option>
                                    <option value="paye" {{ request('statut') == 'paye' ? 'selected' : '' }}>Payé</option>
                                    <option value="annule" {{ request('statut') == 'annule' ? 'selected' : '' }}>Annulé</option>
                                </select>
                            </div>
                            <div class="col-6 col-sm-6 col-md-2">
                                <input type="date" name="date_debut" id="date_debut" class="form-control"
                                       value="{{ request('date_debut') }}" title="Date début" placeholder="Date début">
                            </div>
                            <div class="col-6 col-sm-6 col-md-1">
                                <input type="date" name="date_fin" id="date_fin" class="form-control"
                                       value="{{ request('date_fin') }}" title="Date fin" placeholder="Date fin">
                            </div>
                            <div class="col-12 col-sm-6 col-md-2">
                                <div class="d-flex gap-1">
                                    <button type="submit" class="btn btn-primary flex-fill">
                                        <i class="fas fa-search"></i>
                                        <span class="d-none d-sm-inline">Filtrer</span>
                                    </button>
                                    <a href="{{ route('depenses.index') }}" class="btn btn-outline-secondary" title="Réinitialiser">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Date</th>
                                    <th>Libellé</th>
                                    <th class="hide-mobile">Type</th>
                                    <th class="hide-mobile">Bénéficiaire</th>
                                    <th>Montant</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($depenses as $depense)
                                    <tr class="table-row-clickable" data-href="{{ route('depenses.show', $depense) }}" role="button" tabindex="0">
                                        <td>{{ $depense->date_depense->format('d/m/Y') }}</td>
                                        <td>
                                            <strong>{{ $depense->libelle }}</strong>
                                            @if($depense->reference_facture)
                                                <br>
                                                <small class="text-muted">Ref: {{ $depense->reference_facture }}</small>
                                            @endif
                                        </td>
                                        <td class="hide-mobile">
                                            <span class="badge badge-info">
                                                {{ $depense->type_depense_libelle }}
                                            </span>
                                        </td>
                                        <td class="hide-mobile">{{ $depense->beneficiaire ?? '-' }}</td>
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
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">
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
                        <div class="mt-3">
                            {{ $depenses->appends(request()->query())->links('vendor.pagination.custom') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

