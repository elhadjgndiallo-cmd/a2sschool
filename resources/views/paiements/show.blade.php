@extends('layouts.app')

@section('title', 'Détails du Frais de Scolarité')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-file-invoice mr-2"></i>
                        Détails du Frais de Scolarité
                    </h3>
                    <div>
                        @if($frais->montant_restant > 0)
                            @if($frais->paiement_par_tranches)
                                <button class="btn btn-warning" onclick="payerTranche()">
                                    <i class="fas fa-credit-card mr-1"></i>
                                    Payer un Mois
                                </button>
                            @else
                                <a href="{{ route('paiements.payer-direct', $frais) }}" class="btn btn-success">
                                    <i class="fas fa-money-bill-wave mr-1"></i>
                                    Payer
                                </a>
                            @endif
                        @endif
                        @if($frais->paiements->count() > 0)
                            <a href="{{ route('paiements.recu', $frais) }}" class="btn btn-info ml-2" target="_blank">
                                <i class="fas fa-file-pdf mr-1"></i>
                                Télécharger Reçu
                            </a>
                            <a href="{{ route('paiements.recu', $frais) }}?print=1" class="btn btn-warning ml-2" target="_blank">
                                <i class="fas fa-print mr-1"></i>
                                Imprimer PDF
                            </a>
                        @endif
                        <a href="{{ route('paiements.index') }}" class="btn btn-secondary ml-2">
                            <i class="fas fa-arrow-left mr-1"></i>
                            Retour
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

                    <!-- Informations générales -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Informations Générales</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>Élève:</strong></td>
                                            <td>{{ $frais->eleve->utilisateur->nom ?? 'N/A' }} {{ $frais->eleve->utilisateur->prenom ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Classe:</strong></td>
                                            <td>{{ $frais->eleve->classe->nom ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Libellé:</strong></td>
                                            <td>{{ $frais->libelle }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Type:</strong></td>
                                            <td>
                                                <span class="badge badge-info">{{ ucfirst($frais->type_frais) }}</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Montant Total:</strong></td>
                                            <td><strong>{{ number_format($frais->montant, 0, ',', ' ') }} GNF</strong></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Date d'Échéance:</strong></td>
                                            <td>{{ $frais->date_echeance->format('d/m/Y') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Statut:</strong></td>
                                            <td>
                                                @switch($frais->statut)
                                                    @case('paye')
                                                        <span class="badge badge-success">Payé</span>
                                                        @break
                                                    @case('en_attente')
                                                        <span class="badge badge-warning">En attente</span>
                                                        @break
                                                    @case('en_retard')
                                                        <span class="badge badge-danger">En retard</span>
                                                        @break
                                                    @default
                                                        <span class="badge badge-secondary">{{ $frais->statut }}</span>
                                                @endswitch
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Résumé des Paiements</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <div class="border rounded p-3">
                                                <h4 class="text-success">{{ number_format($frais->montant - $frais->montant_restant, 0, ',', ' ') }}</h4>
                                                <small class="text-muted">GNF Payés</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="border rounded p-3">
                                                <h4 class="text-danger">{{ number_format($frais->montant_restant, 0, ',', ' ') }}</h4>
                                                <small class="text-muted">GNF Restants</small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3">
                                        <div class="progress" style="height: 25px;">
                                            @php
                                                $pourcentage = $frais->montant > 0 ? 
                                                    (($frais->montant - $frais->montant_restant) / $frais->montant) * 100 : 0;
                                            @endphp
                                            <div class="progress-bar 
                                                @if($pourcentage == 100) bg-success
                                                @elseif($pourcentage > 0) bg-warning
                                                @else bg-secondary @endif" 
                                                role="progressbar" 
                                                style="width: {{ $pourcentage }}%">
                                                {{ number_format($pourcentage, 1) }}%
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($frais->paiement_par_tranches && $frais->tranchesPaiement->count() > 0)
                        <!-- Paiements mensuels -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-list mr-2"></i>
                                    Paiements Mensuels ({{ $frais->nombre_tranches }} mois - {{ ucfirst($frais->periode_tranche) }})
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th>Tranche</th>
                                                <th>Montant</th>
                                                <th>Échéance</th>
                                                <th>Statut</th>
                                                <th>Montant Payé</th>
                                                <th>Date Paiement</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($frais->tranchesPaiement->sortBy('numero_tranche') as $tranche)
                                                <tr class="{{ $tranche->isEnRetard() ? 'table-danger' : '' }}">
                                                    <td>
                                                        <strong>Mois {{ $tranche->numero_tranche }}</strong>
                                                    </td>
                                                    <td>{{ number_format($tranche->montant_tranche, 0, ',', ' ') }} GNF</td>
                                                    <td>{{ $tranche->date_echeance->format('d/m/Y') }}</td>
                                                    <td>
                                                        @switch($tranche->statut)
                                                            @case('paye')
                                                                <span class="badge badge-success">Payé</span>
                                                                @break
                                                            @case('en_attente')
                                                                <span class="badge badge-warning">En attente</span>
                                                                @break
                                                            @case('en_retard')
                                                                <span class="badge badge-danger">En retard</span>
                                                                @break
                                                            @default
                                                                <span class="badge badge-secondary">{{ $tranche->statut }}</span>
                                                        @endswitch
                                                    </td>
                                                    <td>{{ number_format($tranche->montant_paye, 0, ',', ' ') }} GNF</td>
                                                    <td>{{ $tranche->date_paiement ? $tranche->date_paiement->format('d/m/Y') : '-' }}</td>
                                                    <td>
                                                        @if($tranche->statut !== 'paye')
                                                            <a href="{{ route('paiements.payer-tranche', $tranche) }}" 
                                                               class="btn btn-sm btn-success">
                                                                <i class="fas fa-credit-card"></i>
                                                                Payer
                                                            </a>
                                                        @else
                                                            <span class="text-success">
                                                                <i class="fas fa-check-circle"></i>
                                                                Payé
                                                            </span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Historique des paiements -->
                    @if($frais->paiements->count() > 0)
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-history mr-2"></i>
                                    Historique des Paiements
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th>Date</th>
                                                <th>Montant</th>
                                                <th>Mode</th>
                                                <th>Référence</th>
                                                <th>Reçu</th>
                                                <th>Encaissé par</th>
                                                <th>Observations</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($frais->paiements->sortByDesc('date_paiement') as $paiement)
                                                <tr>
                                                    <td>{{ $paiement->date_paiement->format('d/m/Y') }}</td>
                                                    <td><strong>{{ number_format($paiement->montant_paye, 0, ',', ' ') }} GNF</strong></td>
                                                    <td>
                                                        <span class="badge badge-info">
                                                            {{ ucfirst($paiement->mode_paiement) }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $paiement->reference_paiement ?? '-' }}</td>
                                                    <td>
                                                        <code>{{ $paiement->numero_recu }}</code>
                                                        <br>
                                                        <a href="{{ route('paiements.recu', ['frais' => $frais, 'paiement' => $paiement]) }}" 
                                                           class="btn btn-sm btn-outline-primary mt-1" 
                                                           target="_blank" 
                                                           title="Télécharger le reçu">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                    </td>
                                                    <td>{{ $paiement->encaissePar->nom ?? 'N/A' }}</td>
                                                    <td>{{ $paiement->observations ?? '-' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@if($frais->paiement_par_tranches)
<script>
function payerTranche() {
    // Rediriger vers le premier mois non payé
    const moisNonPayes = @json($frais->tranchesPaiement->where('statut', '!=', 'paye')->sortBy('numero_tranche'));
    if (moisNonPayes.length > 0) {
        window.location.href = "{{ url('paiements/tranche') }}/" + moisNonPayes[0].id + "/payer";
    } else {
        alert('le mois est déjà payés.');
    }
}
</script>
@endif
@endsection
