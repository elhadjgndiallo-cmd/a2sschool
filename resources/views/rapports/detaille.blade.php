@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line text-primary me-2"></i>
                        Rapport Détaillé
                    </h3>
                    <div class="btn-group">
                        <button onclick="window.print()" class="btn btn-outline-primary">
                            <i class="fas fa-print me-2"></i>Imprimer
                        </button>
                        <a href="{{ route('rapports.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Retour
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- En-tête du rapport -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <h5>Période : {{ \Carbon\Carbon::parse($dateDebut)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($dateFin)->format('d/m/Y') }}</h5>
                            <p class="text-muted mb-2">Type : 
                                @switch($type)
                                    @case('entrees')
                                        Entrées seulement
                                        @break
                                    @case('sorties')
                                        Sorties seulement
                                        @break
                                    @default
                                        Tous les mouvements
                                @endswitch
                            </p>
                            
                            <!-- Affichage des filtres appliqués -->
                            @if($typeDepense || $sourceEntree || $statutDepense || $montantMin || $montantMax)
                                <div class="mt-3">
                                    <h6 class="text-info mb-2">
                                        <i class="fas fa-filter me-2"></i>Filtres appliqués :
                                    </h6>
                                    <div class="d-flex flex-wrap gap-2">
                                        @if($typeDepense)
                                            <span class="badge bg-primary">
                                                <i class="fas fa-tag me-1"></i>Type: {{ ucfirst(str_replace('_', ' ', $typeDepense)) }}
                                            </span>
                                        @endif
                                        @if($sourceEntree)
                                            <span class="badge bg-success">
                                                <i class="fas fa-folder me-1"></i>Source: {{ ucfirst(str_replace('_', ' ', $sourceEntree)) }}
                                            </span>
                                        @endif
                                        @if($statutDepense)
                                            <span class="badge bg-warning">
                                                <i class="fas fa-info-circle me-1"></i>Statut: {{ ucfirst(str_replace('_', ' ', $statutDepense)) }}
                                            </span>
                                        @endif
                                        @if($montantMin || $montantMax)
                                            <span class="badge bg-info">
                                                <i class="fas fa-money-bill-wave me-1"></i>Montant: 
                                                @if($montantMin && $montantMax)
                                                    {{ number_format($montantMin, 0, ',', ' ') }} - {{ number_format($montantMax, 0, ',', ' ') }} GNF
                                                @elseif($montantMin)
                                                    ≥ {{ number_format($montantMin, 0, ',', ' ') }} GNF
                                                @else
                                                    ≤ {{ number_format($montantMax, 0, ',', ' ') }} GNF
                                                @endif
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-4 text-end">
                            <h5>Solde de la période : 
                                <span class="badge {{ $solde >= 0 ? 'bg-success' : 'bg-danger' }} fs-6">
                                    {{ number_format($solde, 0, ',', ' ') }} GNF
                                </span>
                            </h5>
                        </div>
                    </div>

                    <!-- Résumé financier -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-success text-white h-100">
                                <div class="card-body d-flex flex-column">
                                    <div class="text-center mb-3">
                                        <h2 class="card-title mb-0 font-weight-bold">{{ number_format($totalEntrees, 0, ',', ' ') }} GNF</h2>
                                        <p class="card-text mb-2">Total Entrées</p>
                                    </div>
                                    <div class="mt-auto">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <small class="text-light">Manuelles:</small>
                                            <small class="text-light font-weight-bold">{{ number_format($totalEntreesManuelles, 0, ',', ' ') }} GNF</small>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-light">Scolarité:</small>
                                            <small class="text-light font-weight-bold">{{ number_format($totalPaiements, 0, ',', ' ') }} GNF</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white h-100">
                                <div class="card-body d-flex flex-column">
                                    <div class="text-center mb-3">
                                        <h2 class="card-title mb-0 font-weight-bold">{{ number_format($totalSorties, 0, ',', ' ') }} GNF</h2>
                                        <p class="card-text mb-2">Total Sorties</p>
                                    </div>
                                    <div class="mt-auto">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <small class="text-light">Manuelles:</small>
                                            <small class="text-light font-weight-bold">{{ number_format($totalSortiesManuelles, 0, ',', ' ') }} GNF</small>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-light">Salaires:</small>
                                            <small class="text-light font-weight-bold">{{ number_format($totalSalairesEnseignants, 0, ',', ' ') }} GNF</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white h-100">
                                <div class="card-body d-flex flex-column justify-content-center text-center">
                                    <h2 class="card-title mb-0 font-weight-bold">{{ number_format($totalPaiements, 0, ',', ' ') }} GNF</h2>
                                    <p class="card-text">Frais de Scolarité</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card {{ $solde >= 0 ? 'bg-primary' : 'bg-warning' }} text-white h-100">
                                <div class="card-body d-flex flex-column">
                                    <div class="text-center mb-3">
                                        <h2 class="card-title mb-0 font-weight-bold">{{ number_format($solde, 0, ',', ' ') }} GNF</h2>
                                        <p class="card-text mb-2">Solde</p>
                                    </div>
                                    <div class="mt-auto text-center">
                                        <small class="text-light">Entrées - Sorties</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tableau unifié des mouvements -->
                    @if(($entrees->count() > 0 || $sorties->count() > 0))
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class="fas fa-table me-2"></i>Mouvements Comptables
                                    <span class="badge bg-primary ms-2">{{ $entrees->count() + $sorties->count() }} mouvement(s)</span>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Date</th>
                                                <th>Type</th>
                                                <th>Libellé</th>
                                                <th class="text-end">Entrée</th>
                                                <th class="text-end">Sortie</th>
                                                <th class="text-end">Solde</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $soldeCumule = 0;
                                                $mouvements = collect();
                                                
                                                // Ajouter les entrées
                                                foreach($entrees as $entree) {
                                                    $mouvements->push([
                                                        'date' => $entree->date_entree,
                                                        'type' => 'Entrée',
                                                        'libelle' => $entree->libelle,
                                                        'montant' => $entree->montant,
                                                        'is_entree' => true,
                                                        'source' => $entree->source,
                                                        'mode' => $entree->mode_paiement,
                                                        'reference' => $entree->reference
                                                    ]);
                                                }
                                                
                                                // Ajouter les sorties
                                                foreach($sorties as $sortie) {
                                                    $mouvements->push([
                                                        'date' => $sortie->date_depense,
                                                        'type' => 'Sortie',
                                                        'libelle' => $sortie->libelle,
                                                        'montant' => $sortie->montant,
                                                        'is_entree' => false,
                                                        'type_depense' => $sortie->type_depense,
                                                        'statut' => $sortie->statut
                                                    ]);
                                                }
                                                
                                                // Trier par date
                                                $mouvements = $mouvements->sortBy('date');
                                            @endphp
                                            
                                            @foreach($mouvements as $mouvement)
                                                @php
                                                    if($mouvement['is_entree']) {
                                                        $soldeCumule += $mouvement['montant'];
                                                    } else {
                                                        $soldeCumule -= $mouvement['montant'];
                                                    }
                                                @endphp
                                                <tr>
                                                    <td>{{ $mouvement['date']->format('d/m/Y') }}</td>
                                                    <td>
                                                        @if($mouvement['is_entree'])
                                                            <span class="badge bg-success">
                                                                <i class="fas fa-arrow-down me-1"></i>Entrée
                                                            </span>
                                                        @else
                                                            <span class="badge bg-danger">
                                                                <i class="fas fa-arrow-up me-1"></i>Sortie
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <strong>{{ $mouvement['libelle'] }}</strong>
                                                        @if($mouvement['is_entree'])
                                                            <br><small class="text-muted">
                                                                <i class="fas fa-folder me-1"></i>{{ $mouvement['source'] }}
                                                                @if($mouvement['reference'])
                                                                    • Ref: {{ $mouvement['reference'] }}
                                                                @endif
                                                            </small>
                                                        @else
                                                            <br><small class="text-muted">
                                                                @switch($mouvement['type_depense'])
                                                                    @case('salaire_enseignant')
                                                                        <i class="fas fa-chalkboard-teacher me-1"></i>Salaire Enseignant
                                                                        @break
                                                                    @case('salaire_personnel')
                                                                        <i class="fas fa-users me-1"></i>Salaire Personnel
                                                                        @break
                                                                    @case('achat_materiel')
                                                                        <i class="fas fa-shopping-cart me-1"></i>Achat Matériel
                                                                        @break
                                                                    @case('maintenance')
                                                                        <i class="fas fa-tools me-1"></i>Maintenance
                                                                        @break
                                                                    @case('electricite')
                                                                        <i class="fas fa-bolt me-1"></i>Électricité
                                                                        @break
                                                                    @case('eau')
                                                                        <i class="fas fa-tint me-1"></i>Eau
                                                                        @break
                                                                    @case('nourriture')
                                                                        <i class="fas fa-utensils me-1"></i>Nourriture
                                                                        @break
                                                                    @case('transport')
                                                                        <i class="fas fa-car me-1"></i>Transport
                                                                        @break
                                                                    @case('communication')
                                                                        <i class="fas fa-phone me-1"></i>Communication
                                                                        @break
                                                                    @case('formation')
                                                                        <i class="fas fa-graduation-cap me-1"></i>Formation
                                                                        @break
                                                                    @default
                                                                        <i class="fas fa-ellipsis-h me-1"></i>Autre
                                                                @endswitch
                                                                • 
                                                                @switch($mouvement['statut'])
                                                                    @case('en_attente')
                                                                        <span class="text-warning">En attente</span>
                                                                        @break
                                                                    @case('approuve')
                                                                        <span class="text-success">Approuvé</span>
                                                                        @break
                                                                    @case('paye')
                                                                        <span class="text-primary">Payé</span>
                                                                        @break
                                                                    @case('annule')
                                                                        <span class="text-danger">Annulé</span>
                                                                        @break
                                                                @endswitch
                                                            </small>
                                                        @endif
                                                    </td>
                                                    <td class="text-end">
                                                        @if($mouvement['is_entree'])
                                                            <strong class="text-success">{{ number_format($mouvement['montant'], 0, ',', ' ') }} GNF</strong>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-end">
                                                        @if(!$mouvement['is_entree'])
                                                            <strong class="text-danger">{{ number_format($mouvement['montant'], 0, ',', ' ') }} GNF</strong>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-end">
                                                        <strong class="{{ $soldeCumule >= 0 ? 'text-success' : 'text-danger' }}">
                                                            {{ number_format($soldeCumule, 0, ',', ' ') }} GNF
                                                        </strong>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-dark">
                                            <tr>
                                                <th colspan="3" class="text-end">TOTAUX :</th>
                                                <th class="text-end text-success">{{ number_format($totalEntrees, 0, ',', ' ') }} GNF</th>
                                                <th class="text-end text-danger">{{ number_format($totalSorties, 0, ',', ' ') }} GNF</th>
                                                <th class="text-end {{ $solde >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($solde, 0, ',', ' ') }} GNF</th>
                                            </tr>
                                            <tr>
                                                <th colspan="3" class="text-end">DÉTAIL DU SOLDE :</th>
                                                <th class="text-end text-success">{{ number_format($totalEntrees, 0, ',', ' ') }} GNF (Entrées)</th>
                                                <th class="text-end text-danger">{{ number_format($totalSorties, 0, ',', ' ') }} GNF (Sorties)</th>
                                                <th class="text-end text-info">{{ number_format($totalPaiements, 0, ',', ' ') }} GNF (Paiements)</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Message si aucune donnée -->
                    @if($entrees->count() === 0 && $sorties->count() === 0)
                        <div class="text-center py-5">
                            <i class="fas fa-chart-line text-muted" style="font-size: 4rem;"></i>
                            <h4 class="text-muted mt-3">Aucune donnée trouvée</h4>
                            <p class="text-muted">Aucun mouvement trouvé pour la période sélectionnée.</p>
                        </div>
                    @endif

                    <!-- Pied de page du rapport -->
                    <div class="row mt-4 pt-3 border-top">
                        <div class="col-md-6">
                            <small class="text-muted">
                                <i class="fas fa-calendar me-1"></i>
                                Rapport généré le {{ now()->format('d/m/Y à H:i') }}
                            </small>
                        </div>
                        <div class="col-md-6 text-end">
                            <small class="text-muted">
                                <i class="fas fa-user me-1"></i>
                                Généré par {{ auth()->user()->nom ?? 'Administrateur' }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Styles pour l'impression -->
<style>
@media print {
    .btn, .card-header .btn-group {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    
    .table {
        font-size: 12px;
    }
    
    .badge {
        border: 1px solid #000;
    }
}
</style>
@endsection
