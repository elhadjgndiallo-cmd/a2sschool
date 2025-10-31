@extends('layouts.app')

@section('title', 'Rapports de Paiement')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-chart-bar me-2"></i>
            Rapports de Paiement
        </h2>
        <div>
            <button onclick="window.print()" class="btn btn-outline-secondary">
                <i class="fas fa-print me-1"></i>
                Imprimer
            </button>
            <a href="{{ route('paiements.index') }}" class="btn btn-primary">
                <i class="fas fa-arrow-left me-1"></i>
                Retour aux paiements
            </a>
        </div>
    </div>

    <!-- Filtres de période -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-filter me-2"></i>
                Filtres de période
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('paiements.rapports') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="date_debut" class="form-label">Date de début</label>
                    <input type="date" class="form-control" id="date_debut" name="date_debut" 
                           value="{{ request('date_debut', now()->subMonths(6)->format('Y-m-01')) }}">
                </div>
                <div class="col-md-4">
                    <label for="date_fin" class="form-label">Date de fin</label>
                    <input type="date" class="form-control" id="date_fin" name="date_fin" 
                           value="{{ request('date_fin', now()->format('Y-m-t')) }}">
                </div>
                <div class="col-md-4">
                    <label for="classe_id" class="form-label">Classe</label>
                    <select class="form-select" id="classe_id" name="classe_id">
                        <option value="">Toutes les classes</option>
                        @foreach(\App\Models\Classe::orderBy('nom')->get() as $classe)
                            <option value="{{ $classe->id }}" 
                                {{ request('classe_id') == $classe->id ? 'selected' : '' }}>
                                {{ $classe->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i>
                        Filtrer
                    </button>
                    <a href="{{ route('paiements.rapports') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i>
                        Réinitialiser
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistiques générales -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">{{ $stats['total_frais'] }}</h3>
                            <p class="mb-0">Total Frais</p>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">{{ $stats['frais_payes'] }}</h3>
                            <p class="mb-0">Frais Payés</p>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-warning text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">{{ $stats['frais_en_attente'] }}</h3>
                            <p class="mb-0">En Attente</p>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-danger text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">{{ $stats['frais_en_retard'] }}</h3>
                            <p class="mb-0">En Retard</p>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques financières -->
    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body text-center">
                    <h2 class="mb-0">{{ number_format($stats['montant_total'], 0, ',', ' ') }} GNF</h2>
                    <p class="mb-0">Montant Total</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card bg-secondary text-white h-100">
                <div class="card-body text-center">
                    <h2 class="mb-0">{{ number_format($stats['montant_paye'], 0, ',', ' ') }} GNF</h2>
                    <p class="mb-0">Montant Payé</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Taux de recouvrement et répartition -->
    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-percentage me-2"></i>
                        Taux de Recouvrement
                    </h5>
                </div>
                <div class="card-body">
                    @php
                        $tauxRecouvrement = $stats['montant_total'] > 0 ? 
                            ($stats['montant_paye'] / $stats['montant_total']) * 100 : 0;
                    @endphp
                    <div class="progress mb-3" style="height: 35px;">
                        <div class="progress-bar 
                            @if($tauxRecouvrement >= 80) bg-success
                            @elseif($tauxRecouvrement >= 60) bg-warning
                            @else bg-danger @endif" 
                            role="progressbar" 
                            style="width: {{ $tauxRecouvrement }}%">
                            <strong>{{ number_format($tauxRecouvrement, 1) }}%</strong>
                        </div>
                    </div>
                    <div class="text-center">
                        <strong>{{ number_format($stats['montant_paye'], 0, ',', ' ') }} GNF</strong> 
                        sur 
                        <strong>{{ number_format($stats['montant_total'], 0, ',', ' ') }} GNF</strong>
                        <br>
                        <small class="text-muted">
                            Reste: <strong>{{ number_format($stats['montant_total'] - $stats['montant_paye'], 0, ',', ' ') }} GNF</strong>
                        </small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie me-2"></i>
                        Répartition des Statuts
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="border rounded p-3">
                                <h4 class="text-success mb-1">{{ $stats['frais_payes'] }}</h4>
                                <small class="text-muted">Payés</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-3">
                                <h4 class="text-warning mb-1">{{ $stats['frais_en_attente'] }}</h4>
                                <small class="text-muted">En Attente</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-3">
                                <h4 class="text-danger mb-1">{{ $stats['frais_en_retard'] }}</h4>
                                <small class="text-muted">En Retard</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(isset($paiementsParClasse) && $paiementsParClasse->count() > 0)
    <!-- Statistiques par classe -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">
                <i class="fas fa-users-class me-2"></i>
                Statistiques par Classe
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Classe</th>
                            <th class="text-end">Montant Total</th>
                            <th class="text-end">Montant Payé</th>
                            <th class="text-end">Reste</th>
                            <th class="text-center">Taux</th>
                            <th class="text-center">Nombre Frais</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($paiementsParClasse as $classe)
                            @php
                                $totalClasse = $classe->montant_total ?? 0;
                                $payeClasse = $classe->montant_paye ?? 0;
                                $resteClasse = $totalClasse - $payeClasse;
                                $tauxClasse = $totalClasse > 0 ? ($payeClasse / $totalClasse) * 100 : 0;
                            @endphp
                            <tr>
                                <td><strong>{{ $classe->nom ?? 'N/A' }}</strong></td>
                                <td class="text-end">{{ number_format($totalClasse, 0, ',', ' ') }} GNF</td>
                                <td class="text-end text-success">{{ number_format($payeClasse, 0, ',', ' ') }} GNF</td>
                                <td class="text-end text-danger">{{ number_format($resteClasse, 0, ',', ' ') }} GNF</td>
                                <td class="text-center">
                                    <span class="badge 
                                        @if($tauxClasse >= 80) bg-success
                                        @elseif($tauxClasse >= 60) bg-warning
                                        @else bg-danger @endif">
                                        {{ number_format($tauxClasse, 1) }}%
                                    </span>
                                </td>
                                <td class="text-center">{{ $classe->nombre_frais ?? 0 }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Paiements récents -->
    <div class="card mb-4">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-history me-2"></i>
                Paiements Récents
            </h5>
            <a href="{{ route('paiements.index') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-list me-1"></i>
                Voir Tous
            </a>
        </div>
        <div class="card-body">
            @if($paiementsRecents->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Élève</th>
                                <th>Classe</th>
                                <th>Frais</th>
                                <th class="text-end">Montant</th>
                                <th>Mode</th>
                                <th>Reçu</th>
                                <th>Encaissé par</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($paiementsRecents as $paiement)
                                <tr>
                                    <td>{{ $paiement->date_paiement->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <strong>{{ $paiement->fraisScolarite->eleve->utilisateur->nom ?? 'N/A' }} 
                                        {{ $paiement->fraisScolarite->eleve->utilisateur->prenom ?? 'N/A' }}</strong>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $paiement->fraisScolarite->eleve->classe->nom ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        {{ $paiement->fraisScolarite->libelle ?? 'N/A' }}
                                        <br>
                                        <small class="text-muted">{{ ucfirst($paiement->fraisScolarite->type_frais ?? '') }}</small>
                                    </td>
                                    <td class="text-end">
                                        <strong class="text-success">
                                            {{ number_format($paiement->montant_paye, 0, ',', ' ') }} GNF
                                        </strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            {{ ucfirst(str_replace('_', ' ', $paiement->mode_paiement ?? '')) }}
                                        </span>
                                    </td>
                                    <td>
                                        <code>{{ $paiement->numero_recu ?? 'N/A' }}</code>
                                    </td>
                                    <td>{{ $paiement->encaissePar->nom ?? 'N/A' }} {{ $paiement->encaissePar->prenom ?? '' }}</td>
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

@push('styles')
<style>
    @media print {
        .no-print, .btn, .card-header, form {
            display: none !important;
        }
        .card {
            border: none !important;
            box-shadow: none !important;
        }
    }
</style>
@endpush

@endsection