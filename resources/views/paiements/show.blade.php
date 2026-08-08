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
                        <a href="{{ route('paiements.index') }}" class="btn btn-secondary">
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
                                                <th>Mois</th>
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
                                                        <strong>{{ $tranche->libelle_mois }}</strong>
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
                                                        @php
                                                            $resteTranche = max(0, round((float) $tranche->montant_tranche - (float) $tranche->montant_paye, 2));
                                                        @endphp
                                                        @if($resteTranche > 0.01 && auth()->user()->hasPermission('paiements.create'))
                                                            <a href="{{ route('factures.create', ['eleve_id' => $frais->eleve_id, 'lignes' => ['tranche:' . $tranche->id]]) }}"
                                                               class="btn btn-sm btn-success">
                                                                <i class="fas fa-credit-card"></i>
                                                                Payer
                                                            </a>
                                                        @elseif($resteTranche <= 0.01)
                                                            <span class="text-success">
                                                                <i class="fas fa-check-circle"></i>
                                                                Payé
                                                            </span>
                                                        @else
                                                            <span class="text-muted">—</span>
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

                    <!-- Historique des factures -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-history mr-2"></i>
                                Historique des Paiements
                            </h5>
                            @if(auth()->user()->hasPermission('paiements.create'))
                                <a href="{{ route('factures.create', ['eleve_id' => $frais->eleve_id]) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-plus mr-1"></i> Nouvelle facture
                                </a>
                            @endif
                        </div>
                        <div class="card-body">
                            @if($factures->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover mb-0">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th>N° Facture</th>
                                                <th>Date</th>
                                                <th class="text-end">Total</th>
                                                <th>Mode</th>
                                                <th>Statut</th>
                                                <th>Émise par</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($factures as $facture)
                                                <tr class="table-row-clickable" data-href="{{ route('factures.show', $facture) }}" role="button" tabindex="0">
                                                    <td><strong>{{ $facture->numero_facture }}</strong></td>
                                                    <td>{{ $facture->date_facture->format('d/m/Y') }}</td>
                                                    <td class="text-end"><strong>{{ number_format($facture->total, 0, ',', ' ') }} GNF</strong></td>
                                                    <td>{{ ucfirst(str_replace('_', ' ', $facture->mode_paiement)) }}</td>
                                                    <td>
                                                        <span class="badge bg-{{ $facture->statutBadgeClass() }}">
                                                            {{ $facture->statutLibelle() }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $facture->generePar->nom ?? 'N/A' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted mb-0 text-center py-3">Aucune facture enregistrée pour cet élève.</p>
                            @endif
                        </div>
                    </div>

                    <div class="card mb-4 mt-4">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0"><i class="fas fa-bolt mr-2"></i>Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex flex-wrap gap-2">
                                @if($frais->eleve)
                                    <a href="{{ route('eleves.show', $frais->eleve->id) }}" class="btn btn-outline-primary">
                                        <i class="fas fa-user mr-1"></i> Voir l'élève
                                    </a>
                                @endif

                                @if($frais->montant_restant > 0)
                                    <a href="{{ route('recus-rappel.create') }}?eleve_id={{ $frais->eleve->id }}&frais_id={{ $frais->id }}" class="btn btn-danger">
                                        <i class="fas fa-bell mr-1"></i> Créer reçu de rappel
                                    </a>
                                @endif

                                @if($frais->paiements->count() > 0)
                                    <a href="{{ route('paiements.recu', $frais) }}" class="btn btn-info" target="_blank">
                                        <i class="fas fa-file-pdf mr-1"></i> Télécharger reçu
                                    </a>
                                    <a href="{{ route('paiements.recu', $frais) }}?print=1" class="btn btn-outline-info" target="_blank">
                                        <i class="fas fa-print mr-1"></i> Imprimer reçu
                                    </a>
                                @endif

                                <form method="POST" action="{{ route('paiements.destroy', $frais) }}" class="d-inline"
                                      onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer les frais \'{{ $frais->libelle }}\' de {{ $frais->eleve->utilisateur->nom }} {{ $frais->eleve->utilisateur->prenom }} ?\n\nCette action supprimera définitivement les frais, les paiements, les tranches et les entrées comptables liées.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger">
                                        <i class="fas fa-trash mr-1"></i> Supprimer les frais
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
