@extends('layouts.app')

@section('title', 'Mes Paiements')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-credit-card mr-2"></i>
                        Paiements de Mes Enfants
                    </h3>
                </div>
                <div class="card-body">
                    @if(isset($message))
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i>
                            {{ $message }}
                        </div>
                    @endif

                    @if($enfants->count() > 0)
                        <!-- Statistiques -->
                        <div class="row mb-4">
                            <div class="col-md-2">
                                <div class="card bg-primary text-white">
                                    <div class="card-body text-center">
                                        <h4>{{ $stats['total_frais'] }}</h4>
                                        <p class="mb-0">Total Frais</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <h4>{{ $stats['frais_payes'] }}</h4>
                                        <p class="mb-0">Payés</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card bg-warning text-white">
                                    <div class="card-body text-center">
                                        <h4>{{ $stats['frais_en_attente'] }}</h4>
                                        <p class="mb-0">En Attente</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body text-center">
                                        <h4>{{ number_format($stats['montant_total'] / 1000, 0) }}K</h4>
                                        <p class="mb-0">Total (GNF)</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-secondary text-white">
                                    <div class="card-body text-center">
                                        <h4>{{ number_format($stats['montant_restant'] / 1000, 0) }}K</h4>
                                        <p class="mb-0">Restant (GNF)</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Liste des frais par enfant -->
                        @foreach($enfants as $enfant)
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-child mr-2"></i>
                                        {{ $enfant->nom }} {{ $enfant->prenom }} - {{ $enfant->classe->nom ?? 'Non assigné' }}
                                    </h5>
                                </div>
                                <div class="card-body">
                                    @php
                                        $fraisEnfant = $fraisScolarite->where('eleve_id', $enfant->id);
                                    @endphp

                                    @if($fraisEnfant->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>Type de Frais</th>
                                                        <th>Montant Total</th>
                                                        <th>Montant Payé</th>
                                                        <th>Montant Restant</th>
                                                        <th>Statut</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($fraisEnfant as $frais)
                                                        <tr>
                                                            <td>
                                                                <strong>{{ $frais->libelle }}</strong>
                                                                <br>
                                                                <small class="text-muted">{{ $frais->type_frais }}</small>
                                                            </td>
                                                            <td class="text-right">
                                                                <strong>{{ number_format($frais->montant, 0, ',', ' ') }} GNF</strong>
                                                            </td>
                                                            <td class="text-right">
                                                                <span class="text-success">
                                                                    {{ number_format($frais->paiements->sum('montant_paye'), 0, ',', ' ') }} GNF
                                                                </span>
                                                            </td>
                                                            <td class="text-right">
                                                                <span class="text-danger">
                                                                    {{ number_format($frais->montant_restant, 0, ',', ' ') }} GNF
                                                                </span>
                                                            </td>
                                                            <td>
                                                                @switch($frais->statut)
                                                                    @case('paye')
                                                                        <span class="badge badge-success">Payé</span>
                                                                        @break
                                                                    @case('en_attente')
                                                                        <span class="badge badge-warning">En Attente</span>
                                                                        @break
                                                                    @case('partiel')
                                                                        <span class="badge badge-info">Partiel</span>
                                                                        @break
                                                                    @default
                                                                        <span class="badge badge-secondary">{{ $frais->statut }}</span>
                                                                @endswitch
                                                            </td>
                                                            <td>
                                                                <a href="{{ route('parent.paiements.show', $frais) }}" 
                                                                   class="btn btn-sm btn-info" title="Voir détails">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="text-center text-muted py-3">
                                            <i class="fas fa-info-circle mr-2"></i>
                                            Aucun frais de scolarité trouvé pour cet enfant.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                        <!-- Actions rapides -->
                        <div class="row">
                            <div class="col-md-4">
                                <a href="{{ route('parent.paiements.historique') }}" class="btn btn-primary btn-block">
                                    <i class="fas fa-history mr-2"></i>
                                    Historique des Paiements
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="{{ route('parent.echeances') }}" class="btn btn-warning btn-block">
                                    <i class="fas fa-calendar-alt mr-2"></i>
                                    Échéances à Venir
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="{{ route('parent.recapitulatif') }}" class="btn btn-info btn-block">
                                    <i class="fas fa-chart-pie mr-2"></i>
                                    Récapitulatif
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-child fa-3x mb-3"></i>
                            <h5>Aucun enfant trouvé</h5>
                            <p>Veuillez contacter l'administration pour associer vos enfants à votre compte.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
