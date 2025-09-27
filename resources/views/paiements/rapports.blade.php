@extends('layouts.app')

@section('title', 'Rapports de Paiement')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar mr-2"></i>
                        Rapports de Paiement
                    </h3>
                </div>
                <div class="card-body">
                    <!-- Statistiques générales -->
                    <div class="row mb-4">
                        <div class="col-md-2">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h3>{{ $stats['total_frais'] }}</h3>
                                    <p class="mb-0">Total Frais</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h3>{{ $stats['frais_payes'] }}</h3>
                                    <p class="mb-0">Frais Payés</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h3>{{ $stats['frais_en_attente'] }}</h3>
                                    <p class="mb-0">En Attente</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center">
                                    <h3>{{ $stats['frais_en_retard'] }}</h3>
                                    <p class="mb-0">En Retard</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-info text-white">
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

                    <!-- Taux de recouvrement -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Taux de Recouvrement</h5>
                                </div>
                                <div class="card-body">
                                    @php
                                        $tauxRecouvrement = $stats['montant_total'] > 0 ? 
                                            ($stats['montant_paye'] / $stats['montant_total']) * 100 : 0;
                                    @endphp
                                    <div class="progress" style="height: 30px;">
                                        <div class="progress-bar 
                                            @if($tauxRecouvrement >= 80) bg-success
                                            @elseif($tauxRecouvrement >= 60) bg-warning
                                            @else bg-danger @endif" 
                                            role="progressbar" 
                                            style="width: {{ $tauxRecouvrement }}%">
                                            {{ number_format($tauxRecouvrement, 1) }}%
                                        </div>
                                    </div>
                                    <div class="mt-2 text-center">
                                        <strong>{{ number_format($stats['montant_paye'], 0, ',', ' ') }} GNF</strong> 
                                        sur 
                                        <strong>{{ number_format($stats['montant_total'], 0, ',', ' ') }} GNF</strong>
                                    </div>
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
                                                <h4 class="text-success">{{ $stats['frais_payes'] }}</h4>
                                                <small>Payés</small>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="border rounded p-2">
                                                <h4 class="text-warning">{{ $stats['frais_en_attente'] }}</h4>
                                                <small>En Attente</small>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="border rounded p-2">
                                                <h4 class="text-danger">{{ $stats['frais_en_retard'] }}</h4>
                                                <small>En Retard</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Paiements récents -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-history mr-2"></i>
                                Paiements Récents
                            </h5>
                            <a href="{{ route('paiements.index') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-list mr-1"></i>
                                Voir Tous
                            </a>
                        </div>
                        <div class="card-body">
                            @if($paiementsRecents->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th>Date</th>
                                                <th>Élève</th>
                                                <th>Frais</th>
                                                <th>Montant</th>
                                                <th>Mode</th>
                                                <th>Reçu</th>
                                                <th>Encaissé par</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($paiementsRecents as $paiement)
                                                <tr>
                                                    <td>{{ $paiement->date_paiement->format('d/m/Y') }}</td>
                                                    <td>
                                                        <strong>{{ $paiement->fraisScolarite->eleve->nom }} {{ $paiement->fraisScolarite->eleve->prenom }}</strong>
                                                        <br>
                                                        <small class="text-muted">{{ $paiement->fraisScolarite->eleve->classe->nom ?? 'N/A' }}</small>
                                                    </td>
                                                    <td>
                                                        {{ $paiement->fraisScolarite->libelle }}
                                                        <br>
                                                        <small class="text-muted">{{ ucfirst($paiement->fraisScolarite->type_frais) }}</small>
                                                    </td>
                                                    <td>
                                                        <strong class="text-success">
                                                            {{ number_format($paiement->montant_paye, 0, ',', ' ') }} GNF
                                                        </strong>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-info">
                                                            {{ ucfirst($paiement->mode_paiement) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <code>{{ $paiement->numero_recu }}</code>
                                                    </td>
                                                    <td>{{ $paiement->encaissePar->nom ?? 'N/A' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                    <p>Aucun paiement récent trouvé</p>
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
