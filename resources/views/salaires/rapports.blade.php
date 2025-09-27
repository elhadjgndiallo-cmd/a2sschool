@extends('layouts.app')

@section('title', 'Rapports de Salaires')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar mr-2"></i>
                        Rapports de Salaires des Enseignants
                    </h3>
                </div>
                <div class="card-body">
                    <!-- Filtres de période -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form method="GET" action="{{ route('salaires.rapports') }}">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="date_debut">Date Début</label>
                                            <input type="date" name="date_debut" id="date_debut" class="form-control" 
                                                   value="{{ $dateDebut }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="date_fin">Date Fin</label>
                                            <input type="date" name="date_fin" id="date_fin" class="form-control" 
                                                   value="{{ $dateFin }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <button type="submit" class="btn btn-primary btn-block">
                                                <i class="fas fa-search mr-1"></i>
                                                Filtrer
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Statistiques générales -->
                    <div class="row mb-4">
                        <div class="col-md-2">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h3>{{ $stats['total_salaires'] }}</h3>
                                    <p class="mb-0">Total Salaires</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h3>{{ $stats['salaires_payes'] }}</h3>
                                    <p class="mb-0">Payés</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h3>{{ $stats['salaires_valides'] }}</h3>
                                    <p class="mb-0">Validés</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h3>{{ $stats['salaires_calcules'] }}</h3>
                                    <p class="mb-0">Calculés</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center">
                                    <h3>{{ number_format($stats['montant_total_brut'] / 1000, 0) }}K</h3>
                                    <p class="mb-0">Total Brut (GNF)</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-secondary text-white">
                                <div class="card-body text-center">
                                    <h3>{{ number_format($stats['montant_total_net'] / 1000, 0) }}K</h3>
                                    <p class="mb-0">Total Net (GNF)</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Répartition des statuts -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Répartition des Statuts</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-3">
                                            <div class="border rounded p-2">
                                                <h4 class="text-success">{{ $stats['salaires_payes'] }}</h4>
                                                <small>Payés</small>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="border rounded p-2">
                                                <h4 class="text-info">{{ $stats['salaires_valides'] }}</h4>
                                                <small>Validés</small>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="border rounded p-2">
                                                <h4 class="text-warning">{{ $stats['salaires_calcules'] }}</h4>
                                                <small>Calculés</small>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="border rounded p-2">
                                                <h4 class="text-primary">{{ $stats['total_salaires'] - $stats['salaires_payes'] - $stats['salaires_valides'] - $stats['salaires_calcules'] }}</h4>
                                                <small>Autres</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Montants Totaux</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <div class="border rounded p-2">
                                                <h4 class="text-danger">{{ number_format($stats['montant_total_brut'] / 1000, 0) }}K GNF</h4>
                                                <small>Salaire Brut Total</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="border rounded p-2">
                                                <h4 class="text-success">{{ number_format($stats['montant_total_net'] / 1000, 0) }}K GNF</h4>
                                                <small>Salaire Net Total</small>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="text-center">
                                        <h5 class="text-info">
                                            Différence: {{ number_format(($stats['montant_total_brut'] - $stats['montant_total_net']) / 1000, 0) }}K GNF
                                        </h5>
                                        <small class="text-muted">Total des déductions</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Salaires par enseignant -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-users mr-2"></i>
                                Salaires par Enseignant
                            </h5>
                            <a href="{{ route('salaires.index') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-list mr-1"></i>
                                Voir Tous
                            </a>
                        </div>
                        <div class="card-body">
                            @if($salairesParEnseignant->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th>Enseignant</th>
                                                <th>Nombre de Salaires</th>
                                                <th>Total Net (GNF)</th>
                                                <th>Moyenne par Salaire</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($salairesParEnseignant as $salaire)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $salaire->enseignant->utilisateur->nom }} {{ $salaire->enseignant->utilisateur->prenom }}</strong>
                                                        <br>
                                                        <small class="text-muted">{{ $salaire->enseignant->utilisateur->email }}</small>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge badge-info">{{ $salaire->count }}</span>
                                                    </td>
                                                    <td class="text-right">
                                                        <strong class="text-success">
                                                            {{ number_format($salaire->total_net, 0, ',', ' ') }} GNF
                                                        </strong>
                                                    </td>
                                                    <td class="text-right">
                                                        <strong class="text-primary">
                                                            {{ number_format($salaire->total_net / $salaire->count, 0, ',', ' ') }} GNF
                                                        </strong>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('salaires.index', ['enseignant_id' => $salaire->enseignant_id]) }}" 
                                                           class="btn btn-sm btn-info" title="Voir les salaires">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-dark">
                                            <tr>
                                                <th colspan="2">TOTAL</th>
                                                <th class="text-right">
                                                    {{ number_format($salairesParEnseignant->sum('total_net'), 0, ',', ' ') }} GNF
                                                </th>
                                                <th class="text-right">
                                                    {{ number_format($salairesParEnseignant->sum('total_net') / $salairesParEnseignant->sum('count'), 0, ',', ' ') }} GNF
                                                </th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @else
                                <div class="text-center text-muted py-4">
                                    <i class="fas fa-coins fa-3x mb-3"></i>
                                    <p>Aucun salaire trouvé pour cette période</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Résumé de la période -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-calendar mr-2"></i>
                                Résumé de la Période
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <h6>Période Analysée</h6>
                                    <p class="mb-1"><strong>Début:</strong> {{ \Carbon\Carbon::parse($dateDebut)->format('d/m/Y') }}</p>
                                    <p class="mb-1"><strong>Fin:</strong> {{ \Carbon\Carbon::parse($dateFin)->format('d/m/Y') }}</p>
                                    <p class="mb-0"><strong>Durée:</strong> {{ \Carbon\Carbon::parse($dateDebut)->diffInDays(\Carbon\Carbon::parse($dateFin)) + 1 }} jours</p>
                                </div>
                                <div class="col-md-4">
                                    <h6>Statistiques</h6>
                                    <p class="mb-1"><strong>Nombre d'enseignants:</strong> {{ $salairesParEnseignant->count() }}</p>
                                    <p class="mb-1"><strong>Total des salaires:</strong> {{ $stats['total_salaires'] }}</p>
                                    <p class="mb-0"><strong>Taux de paiement:</strong> {{ $stats['total_salaires'] > 0 ? round(($stats['salaires_payes'] / $stats['total_salaires']) * 100, 1) : 0 }}%</p>
                                </div>
                                <div class="col-md-4">
                                    <h6>Montants</h6>
                                    <p class="mb-1"><strong>Salaire brut moyen:</strong> {{ $stats['total_salaires'] > 0 ? number_format($stats['montant_total_brut'] / $stats['total_salaires'], 0, ',', ' ') : 0 }} GNF</p>
                                    <p class="mb-1"><strong>Salaire net moyen:</strong> {{ $stats['total_salaires'] > 0 ? number_format($stats['montant_total_net'] / $stats['total_salaires'], 0, ',', ' ') : 0 }} GNF</p>
                                    <p class="mb-0"><strong>Déductions moyennes:</strong> {{ $stats['total_salaires'] > 0 ? number_format(($stats['montant_total_brut'] - $stats['montant_total_net']) / $stats['total_salaires'], 0, ',', ' ') : 0 }} GNF</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
