@extends('layouts.app')

@section('title', 'Comptabilité - Tableau de bord')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-calculator text-primary me-2"></i>
                    Comptabilité
                </h2>
                <div class="btn-group">
                    <a href="{{ route('comptabilite.rapport-journalier') }}" class="btn btn-outline-info">
                        <i class="fas fa-calendar-day me-1"></i>Rapport Journalier
                    </a>
                    <a href="{{ route('comptabilite.rapports') }}" class="btn btn-outline-primary">
                        <i class="fas fa-chart-line me-1"></i>Rapports
                    </a>
                    <a href="{{ route('comptabilite.entrees') }}" class="btn btn-outline-success">
                        <i class="fas fa-arrow-up me-1"></i>Entrées
                    </a>
                    <a href="{{ route('comptabilite.sorties') }}" class="btn btn-outline-danger">
                        <i class="fas fa-arrow-down me-1"></i>Sorties
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques générales -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Revenus du mois</h6>
                            <h3 class="mb-0">{{ number_format($stats['revenus_mois'], 0, ',', ' ') }} GNF</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-arrow-up fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Dépenses du mois</h6>
                            <h3 class="mb-0">{{ number_format($stats['depenses_mois'], 0, ',', ' ') }} GNF</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-arrow-down fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card {{ $stats['benefice_mois'] >= 0 ? 'bg-primary' : 'bg-warning' }} text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Bénéfice du mois</h6>
                            <h3 class="mb-0">{{ number_format($stats['benefice_mois'], 0, ',', ' ') }} GNF</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-chart-line fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Élèves en attente</h6>
                            <h3 class="mb-0">{{ $stats['eleves_en_attente'] }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques totales -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie me-2"></i>Statistiques Totales
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="text-center">
                                <h4 class="text-success">{{ number_format($stats['revenus_total'], 0, ',', ' ') }}</h4>
                                <small class="text-muted">Revenus totaux (GNF)</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <h4 class="text-danger">{{ number_format($stats['depenses_total'], 0, ',', ' ') }}</h4>
                                <small class="text-muted">Dépenses totales (GNF)</small>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="text-center">
                        <h4 class="{{ $stats['benefice_total'] >= 0 ? 'text-primary' : 'text-warning' }}">
                            {{ number_format($stats['benefice_total'], 0, ',', ' ') }} GNF
                        </h4>
                        <small class="text-muted">Bénéfice total</small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-clock me-2"></i>Activité Récente
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <h6 class="text-success">Dernières Entrées</h6>
                            <ul class="list-unstyled">
                                @forelse($toutesLesEntrees as $entree)
                                    <li class="mb-2">
                                        @if(isset($entree->date_entree))
                                            {{-- Entrée manuelle --}}
                                            <small class="text-muted">{{ $entree->date_entree->format('d/m/Y') }}</small><br>
                                            <strong>{{ number_format($entree->montant, 0, ',', ' ') }} GNF</strong><br>
                                            <small>{{ $entree->libelle }}</small>
                                        @else
                                            {{-- Paiement de frais de scolarité --}}
                                            <small class="text-muted">{{ $entree->date_paiement->format('d/m/Y') }}</small><br>
                                            <strong>{{ number_format($entree->montant_paye, 0, ',', ' ') }} GNF</strong><br>
                                            <small>{{ $entree->fraisScolarite->eleve->utilisateur->nom }} {{ $entree->fraisScolarite->eleve->utilisateur->prenom }} - {{ $entree->fraisScolarite->type_frais }}</small>
                                        @endif
                                    </li>
                                @empty
                                    <li class="text-muted">Aucune entrée récente</li>
                                @endforelse
                            </ul>
                        </div>
                        <div class="col-6">
                            <h6 class="text-danger">Dernières Dépenses</h6>
                            <ul class="list-unstyled">
                                @forelse($dernieresDepenses as $depense)
                                    <li class="mb-2">
                                        <small class="text-muted">{{ $depense->date_depense->format('d/m/Y') }}</small><br>
                                        <strong>{{ number_format($depense->montant, 0, ',', ' ') }} GNF</strong><br>
                                        <small>{{ $depense->libelle }}</small>
                                    </li>
                                @empty
                                    <li class="text-muted">Aucune dépense récente</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>Actions Rapides
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('entrees.create') }}" class="btn btn-success w-100">
                                <i class="fas fa-plus me-2"></i>Nouvelle Entrée
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('depenses.create') }}" class="btn btn-danger w-100">
                                <i class="fas fa-plus me-2"></i>Nouvelle Dépense
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('comptabilite.rapports') }}" class="btn btn-primary w-100">
                                <i class="fas fa-chart-line me-2"></i>Générer Rapport
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('paiements.index') }}" class="btn btn-info w-100">
                                <i class="fas fa-credit-card me-2"></i>Gérer Paiements
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
