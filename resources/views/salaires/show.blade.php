@extends('layouts.app')

@section('title', 'Détails du Salaire')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-coins mr-2"></i>
                        Détails du Salaire
                    </h3>
                    <div>
                        @if($salaire->statut === 'calculé')
                            <a href="{{ route('salaires.edit', $salaire) }}" class="btn btn-warning">
                                <i class="fas fa-edit mr-1"></i>
                                Modifier
                            </a>
                            <form action="{{ route('salaires.valider', $salaire) }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-success" 
                                        onclick="return confirm('Valider ce salaire ?')">
                                    <i class="fas fa-check mr-1"></i>
                                    Valider
                                </button>
                            </form>
                        @endif
                        
                        @if($salaire->statut === 'validé')
                            <a href="{{ route('salaires.payer.form', $salaire) }}" class="btn btn-primary">
                                <i class="fas fa-money-bill-wave mr-1"></i>
                                Payer
                            </a>
                        @endif
                        
                        <a href="{{ route('salaires.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i>
                            Retour
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Informations générales -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-user mr-2"></i>
                                        Enseignant
                                    </h5>
                                    <p class="mb-1"><strong>Nom:</strong> {{ $salaire->enseignant->utilisateur->nom }} {{ $salaire->enseignant->utilisateur->prenom }}</p>
                                    <p class="mb-1"><strong>Email:</strong> {{ $salaire->enseignant->utilisateur->email }}</p>
                                    <p class="mb-0"><strong>Téléphone:</strong> {{ $salaire->enseignant->utilisateur->telephone ?? 'Non renseigné' }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-calendar mr-2"></i>
                                        Période
                                    </h5>
                                    <p class="mb-1"><strong>Début:</strong> {{ $salaire->periode_debut->format('d/m/Y') }}</p>
                                    <p class="mb-1"><strong>Fin:</strong> {{ $salaire->periode_fin->format('d/m/Y') }}</p>
                                    <p class="mb-0"><strong>Durée:</strong> {{ $salaire->periode_debut->diffInDays($salaire->periode_fin) + 1 }} jours</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Statut -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <h6><i class="fas fa-info-circle mr-2"></i>Statut du Salaire</h6>
                                <div class="d-flex align-items-center">
                                    @switch($salaire->statut)
                                        @case('calculé')
                                            <span class="badge badge-warning badge-lg mr-3">Calculé</span>
                                            <span>Le salaire a été calculé et est en attente de validation.</span>
                                            @break
                                        @case('validé')
                                            <span class="badge badge-info badge-lg mr-3">Validé</span>
                                            <span>Le salaire a été validé et peut être payé.</span>
                                            @break
                                        @case('payé')
                                            <span class="badge badge-success badge-lg mr-3">Payé</span>
                                            <span>Le salaire a été payé le {{ $salaire->date_paiement->format('d/m/Y') }}.</span>
                                            @break
                                        @case('annulé')
                                            <span class="badge badge-danger badge-lg mr-3">Annulé</span>
                                            <span>Le salaire a été annulé.</span>
                                            @break
                                    @endswitch
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Détails du calcul -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-calculator mr-2"></i>
                                        Calcul du Salaire
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <tbody>
                                                <tr>
                                                    <td><strong>Nombre d'heures:</strong></td>
                                                    <td class="text-right">{{ $salaire->nombre_heures }}h</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Taux horaire:</strong></td>
                                                    <td class="text-right">{{ number_format($salaire->taux_horaire, 0, ',', ' ') }} GNF/h</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Salaire horaire:</strong></td>
                                                    <td class="text-right">{{ number_format($salaire->nombre_heures * $salaire->taux_horaire, 0, ',', ' ') }} GNF</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Salaire de base:</strong></td>
                                                    <td class="text-right">{{ number_format($salaire->salaire_base, 0, ',', ' ') }} GNF</td>
                                                </tr>
                                                <tr class="table-info">
                                                    <td><strong>Sous-total:</strong></td>
                                                    <td class="text-right"><strong>{{ number_format($salaire->salaire_base + ($salaire->nombre_heures * $salaire->taux_horaire), 0, ',', ' ') }} GNF</strong></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-gift mr-2"></i>
                                        Primes
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <tbody>
                                                <tr>
                                                    <td>Prime d'ancienneté:</td>
                                                    <td class="text-right">{{ number_format($salaire->prime_anciennete, 0, ',', ' ') }} GNF</td>
                                                </tr>
                                                <tr>
                                                    <td>Prime de performance:</td>
                                                    <td class="text-right">{{ number_format($salaire->prime_performance, 0, ',', ' ') }} GNF</td>
                                                </tr>
                                                <tr>
                                                    <td>Heures supplémentaires:</td>
                                                    <td class="text-right">{{ number_format($salaire->prime_heures_supplementaires, 0, ',', ' ') }} GNF</td>
                                                </tr>
                                                <tr class="table-success">
                                                    <td><strong>Total primes:</strong></td>
                                                    <td class="text-right"><strong>{{ number_format($salaire->prime_anciennete + $salaire->prime_performance + $salaire->prime_heures_supplementaires, 0, ',', ' ') }} GNF</strong></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Déductions et totaux -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-minus-circle mr-2"></i>
                                        Déductions
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <tbody>
                                                <tr>
                                                    <td>Déduction absences:</td>
                                                    <td class="text-right text-danger">{{ number_format($salaire->deduction_absences, 0, ',', ' ') }} GNF</td>
                                                </tr>
                                                <tr>
                                                    <td>Autres déductions:</td>
                                                    <td class="text-right text-danger">{{ number_format($salaire->deduction_autres, 0, ',', ' ') }} GNF</td>
                                                </tr>
                                                <tr class="table-danger">
                                                    <td><strong>Total déductions:</strong></td>
                                                    <td class="text-right"><strong class="text-danger">{{ number_format($salaire->deduction_absences + $salaire->deduction_autres, 0, ',', ' ') }} GNF</strong></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-coins mr-2"></i>
                                        Totaux
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="text-center">
                                        <h4 class="text-primary">Salaire Brut</h4>
                                        <h2 class="text-primary">{{ number_format($salaire->salaire_brut, 0, ',', ' ') }} GNF</h2>
                                        
                                        <hr>
                                        
                                        <h4 class="text-success">Salaire Net</h4>
                                        <h1 class="text-success font-weight-bold">{{ number_format($salaire->salaire_net, 0, ',', ' ') }} GNF</h1>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Historique et informations -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-history mr-2"></i>
                                        Historique et Informations
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <h6>Calcul</h6>
                                            <p class="mb-1"><strong>Date:</strong> {{ $salaire->date_calcul ? $salaire->date_calcul->format('d/m/Y H:i') : 'Non calculé' }}</p>
                                            <p class="mb-0"><strong>Par:</strong> {{ $salaire->calculePar->nom ?? 'Non renseigné' }}</p>
                                        </div>
                                        <div class="col-md-4">
                                            <h6>Validation</h6>
                                            <p class="mb-1"><strong>Date:</strong> {{ $salaire->date_validation ? $salaire->date_validation->format('d/m/Y H:i') : 'Non validé' }}</p>
                                            <p class="mb-0"><strong>Par:</strong> {{ $salaire->validePar->nom ?? 'Non renseigné' }}</p>
                                        </div>
                                        <div class="col-md-4">
                                            <h6>Paiement</h6>
                                            <p class="mb-1"><strong>Date:</strong> {{ $salaire->date_paiement ? $salaire->date_paiement->format('d/m/Y H:i') : 'Non payé' }}</p>
                                            <p class="mb-0"><strong>Par:</strong> {{ $salaire->payePar->nom ?? 'Non renseigné' }}</p>
                                        </div>
                                    </div>
                                    
                                    @if($salaire->observations)
                                        <hr>
                                        <h6>Observations</h6>
                                        <p class="text-muted">{{ $salaire->observations }}</p>
                                    @endif
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
