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
                            <h6 class="card-title">Total Revenus</h6>
                            <h3 class="mb-0">{{ number_format($totalRevenus, 0, ',', ' ') }} GNF</h3>
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
                            <h6 class="card-title">Total Sorties</h6>
                            <h3 class="mb-0">{{ number_format($totalSorties, 0, ',', ' ') }} GNF</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-arrow-down fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card {{ $beneficeTotal >= 0 ? 'bg-primary' : 'bg-warning' }} text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Bénéfice Total</h6>
                            <h3 class="mb-0">{{ number_format($beneficeTotal, 0, ',', ' ') }} GNF</h3>
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

    <!-- Année Scolaire -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Année Scolaire
                    </h5>
                </div>
                <div class="card-body text-center">
                    <h4 class="mb-0">{{ $anneeScolaireActive->nom ?? 'N/A' }}</h4>
                    <small class="text-muted">
                        Du {{ $anneeScolaireActive->date_debut->format('d/m/Y') ?? 'N/A' }} 
                        au {{ $anneeScolaireActive->date_fin->format('d/m/Y') ?? 'N/A' }}
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Toutes les entrées de l'année scolaire -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-arrow-up text-success me-2"></i>Toutes les Entrées ({{ $anneeScolaireActive->nom ?? 'Année scolaire active' }})
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th>Source</th>
                                    <th class="text-end">Montant (GNF)</th>
                                    <th>Enregistré par</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($toutesLesEntrees as $entree)
                                    <tr>
                                        <td>{{ $entree->date->format('d/m/Y') }}</td>
                                        <td>{{ $entree->description }}</td>
                                        <td>
                                            <span class="badge bg-primary">{{ $entree->source }}</span>
                                        </td>
                                        <td class="text-end">
                                            <strong>{{ number_format($entree->montant, 0, ',', ' ') }}</strong>
                                        </td>
                                        <td>{{ $entree->enregistre_par->nom ?? 'N/A' }} {{ $entree->enregistre_par->prenom ?? '' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">Aucune entrée pour cette année scolaire</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="table-light">
                                    <th colspan="3" class="text-end">Total des entrées :</th>
                                    <th class="text-end">
                                        <strong>{{ number_format($toutesLesEntrees->sum('montant'), 0, ',', ' ') }} GNF</strong>
                                    </th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toutes les sorties de l'année scolaire -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-arrow-down text-danger me-2"></i>Toutes les Sorties ({{ $anneeScolaireActive->nom ?? 'Année scolaire active' }})
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th>Type</th>
                                    <th class="text-end">Montant (GNF)</th>
                                    <th>Enregistré par</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($toutesLesSorties as $sortie)
                                    <tr>
                                        <td>{{ $sortie->date->format('d/m/Y') }}</td>
                                        <td>{{ $sortie->description }}</td>
                                        <td>
                                            <span class="badge bg-danger">{{ ucfirst(str_replace('_', ' ', $sortie->type_depense)) }}</span>
                                        </td>
                                        <td class="text-end">
                                            <strong>{{ number_format($sortie->montant, 0, ',', ' ') }}</strong>
                                        </td>
                                        <td>{{ $sortie->enregistre_par->nom ?? 'N/A' }} {{ $sortie->enregistre_par->prenom ?? '' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">Aucune sortie pour cette année scolaire</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="table-light">
                                    <th colspan="3" class="text-end">Total des sorties :</th>
                                    <th class="text-end">
                                        <strong>{{ number_format($toutesLesSorties->sum('montant'), 0, ',', ' ') }} GNF</strong>
                                    </th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
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
                            <a href="{{ route('comptabilite.rapport-journalier') }}" class="btn btn-info w-100">
                                <i class="fas fa-calendar-day me-2"></i>Rapport Journalier
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('paiements.index') }}" class="btn btn-primary w-100">
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
