@extends('layouts.app')

@section('title', 'Rapports de Sorties')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar mr-2"></i>
                        Rapports de Sorties
                    </h3>
                </div>
                <div class="card-body">
                    <!-- Filtres de période -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form method="GET" action="{{ route('depenses.rapports') }}">
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
                                    <h3>{{ $stats['total_depenses'] }}</h3>
                                    <p class="mb-0">Total Sorties</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h3>{{ $stats['depenses_payees'] }}</h3>
                                    <p class="mb-0">Payées</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h3>{{ $stats['depenses_en_attente'] }}</h3>
                                    <p class="mb-0">En Attente</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h3>{{ $stats['depenses_approuvees'] }}</h3>
                                    <p class="mb-0">Approuvées</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center">
                                    <h3>{{ number_format($stats['montant_total'] / 1000, 0) }}K</h3>
                                    <p class="mb-0">Total (GNF)</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-secondary text-white">
                                <div class="card-body text-center">
                                    <h3>{{ number_format($stats['montant_paye'] / 1000, 0) }}K</h3>
                                    <p class="mb-0">Payé (GNF)</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Répartition par type -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Répartition par Type</h5>
                                </div>
                                <div class="card-body">
                                    @if($depensesParType->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Type</th>
                                                        <th>Nombre</th>
                                                        <th>Total (GNF)</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($depensesParType as $depense)
                                                        <tr>
                                                            <td>
                                                                @php
                                                                    $types = [
                                                                        'salaire_enseignant' => 'Salaire Enseignant',
                                                                        'salaire_personnel' => 'Salaire Personnel',
                                                                        'achat_materiel' => 'Achat Matériel',
                                                                        'maintenance' => 'Maintenance',
                                                                        'electricite' => 'Électricité',
                                                                        'eau' => 'Eau',
                                                                        'nourriture' => 'Nourriture',
                                                                        'transport' => 'Transport',
                                                                        'communication' => 'Communication',
                                                                        'formation' => 'Formation',
                                                                        'autre' => 'Autre'
                                                                    ];
                                                                @endphp
                                                                {{ $types[$depense->type_depense] ?? $depense->type_depense }}
                                                            </td>
                                                            <td>{{ $depense->count }}</td>
                                                            <td><strong>{{ number_format($depense->total, 0, ',', ' ') }}</strong></td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <p class="text-muted text-center">Aucune sortie trouvée pour cette période</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Répartition des Statuts</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-4">
                                            <div class="border rounded p-2">
                                                <h4 class="text-success">{{ $stats['depenses_payees'] }}</h4>
                                                <small>Payées</small>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="border rounded p-2">
                                                <h4 class="text-warning">{{ $stats['depenses_en_attente'] }}</h4>
                                                <small>En Attente</small>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="border rounded p-2">
                                                <h4 class="text-info">{{ $stats['depenses_approuvees'] }}</h4>
                                                <small>Approuvées</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sorties récentes -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-history mr-2"></i>
                                Sorties Récentes
                            </h5>
                            <a href="{{ route('depenses.index') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-list mr-1"></i>
                                Voir Toutes
                            </a>
                        </div>
                        <div class="card-body">
                            @if($depensesRecentes->count() > 0)
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
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($depensesRecentes as $depense)
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
                                                        @php
                                                            $types = [
                                                                'salaire_enseignant' => 'Salaire Enseignant',
                                                                'salaire_personnel' => 'Salaire Personnel',
                                                                'achat_materiel' => 'Achat Matériel',
                                                                'maintenance' => 'Maintenance',
                                                                'electricite' => 'Électricité',
                                                                'eau' => 'Eau',
                                                                'nourriture' => 'Nourriture',
                                                                'transport' => 'Transport',
                                                                'communication' => 'Communication',
                                                                'formation' => 'Formation',
                                                                'autre' => 'Autre'
                                                            ];
                                                        @endphp
                                                        <span class="badge badge-info">
                                                            {{ $types[$depense->type_depense] ?? $depense->type_depense }}
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
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                    <p>Aucune sortie récente trouvée</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
