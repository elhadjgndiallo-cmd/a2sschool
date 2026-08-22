@extends('layouts.app')

@section('title', 'Comptabilité - Tableau de bord')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
                <h2 class="mb-0 mb-md-0">
                    <i class="fas fa-calculator text-primary me-2"></i>
                    Comptabilité
                </h2>
                <div class="btn-group w-100 w-md-auto flex-wrap">
                    <a href="{{ route('comptabilite.rapport-journalier') }}" class="btn btn-outline-info">
                        <i class="fas fa-calendar-day me-1"></i><span class="d-none d-sm-inline">Rapport Journalier</span><span class="d-sm-none">Journalier</span>
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

    <!-- Trois diagrammes : comparaison + entrées/sorties par source -->
    <div class="row mb-4">
        <div class="col-12 col-lg-4 mb-3">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie text-primary me-2"></i>Entrées vs Sorties
                    </h5>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <canvas id="comparaisonChart" style="max-height: 280px;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4 mb-3">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar text-success me-2"></i>Entrées par source
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="entreesSourceChart" style="max-height: 280px;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4 mb-3">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar text-danger me-2"></i>Sorties par source
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="sortiesSourceChart" style="max-height: 280px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- 10 dernières entrées de l'année scolaire -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-arrow-up text-success me-2"></i>10 Dernières Entrées
                    </h5>
                    <a href="{{ route('comptabilite.entrees') }}" class="btn btn-sm btn-outline-success">
                        <i class="fas fa-list me-1"></i>Voir toutes les entrées
                    </a>
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
                                    <th colspan="3" class="text-end">
                                        <small class="text-muted">Total des 10 dernières entrées affichées :</small>
                                    </th>
                                    <th class="text-end">
                                        <strong>{{ number_format($toutesLesEntrees->sum('montant'), 0, ',', ' ') }} GNF</strong>
                                    </th>
                                    <th></th>
                                </tr>
                                <tr class="table-success">
                                    <th colspan="3" class="text-end">
                                        <strong>Total de TOUTES les entrées ({{ $anneeScolaireActive->nom }}) :</strong>
                                    </th>
                                    <th class="text-end">
                                        <strong class="text-success fs-5">{{ number_format($totalRevenus, 0, ',', ' ') }} GNF</strong>
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

    <!-- 10 dernières sorties de l'année scolaire -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-arrow-down text-danger me-2"></i>10 Dernières Sorties
                    </h5>
                    <a href="{{ route('comptabilite.sorties') }}" class="btn btn-sm btn-outline-danger">
                        <i class="fas fa-list me-1"></i>Voir toutes les sorties
                    </a>
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
                                        <td>{{ $sortie->libelle ?? $sortie->description }}</td>
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
                                    <th colspan="3" class="text-end">
                                        <small class="text-muted">Total des 10 dernières sorties affichées :</small>
                                    </th>
                                    <th class="text-end">
                                        <strong>{{ number_format($toutesLesSorties->sum('montant'), 0, ',', ' ') }} GNF</strong>
                                    </th>
                                    <th></th>
                                </tr>
                                <tr class="table-danger">
                                    <th colspan="3" class="text-end">
                                        <strong>Total de TOUTES les sorties ({{ $anneeScolaireActive->nom }}) :</strong>
                                    </th>
                                    <th class="text-end">
                                        <strong class="text-danger fs-5">{{ number_format($totalSorties, 0, ',', ' ') }} GNF</strong>
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
                            <a href="{{ route('comptabilite.impayes-mensuels') }}" class="btn btn-warning w-100">
                                <i class="fas fa-user-times me-2"></i>Impayés mensuels
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

@push('scripts')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const diagrammes = @json($diagrammesData);

    const formatGnf = (value) => new Intl.NumberFormat('fr-FR', {
        style: 'decimal',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(value) + ' GNF';

    const formatCompact = (value) => new Intl.NumberFormat('fr-FR', {
        notation: 'compact',
        compactDisplay: 'short'
    }).format(value) + ' GNF';

    const palette = [
        '#0d6efd', '#198754', '#dc3545', '#ffc107', '#0dcaf0',
        '#6f42c1', '#fd7e14', '#20c997', '#6610f2', '#d63384'
    ];

    const moneyTooltip = {
        callbacks: {
            label: function(context) {
                const label = context.label || context.dataset.label || '';
                const value = context.parsed.y ?? context.parsed ?? 0;
                return (label ? label + ': ' : '') + formatGnf(value);
            }
        }
    };

    // 1. Secteur : Entrées vs Sorties
    const comparaisonCtx = document.getElementById('comparaisonChart');
    if (comparaisonCtx) {
        new Chart(comparaisonCtx, {
            type: 'pie',
            data: {
                labels: diagrammes.comparaison.labels,
                datasets: [{
                    data: diagrammes.comparaison.data,
                    backgroundColor: ['#198754', '#dc3545'],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: moneyTooltip
                }
            }
        });
    }

    // 2. Histogramme : Entrées par source
    const entreesCtx = document.getElementById('entreesSourceChart');
    if (entreesCtx) {
        const labels = diagrammes.entreesParSource.labels || [];
        const data = diagrammes.entreesParSource.data || [];
        new Chart(entreesCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Entrées (GNF)',
                    data: data,
                    backgroundColor: labels.map((_, i) => palette[i % palette.length]),
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false },
                    tooltip: moneyTooltip
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { callback: (v) => formatCompact(v) }
                    },
                    x: {
                        ticks: {
                            maxRotation: 45,
                            minRotation: 0,
                            font: { size: 10 }
                        }
                    }
                }
            }
        });
    }

    // 3. Histogramme : Sorties par source
    const sortiesCtx = document.getElementById('sortiesSourceChart');
    if (sortiesCtx) {
        const labels = diagrammes.sortiesParSource.labels || [];
        const data = diagrammes.sortiesParSource.data || [];
        new Chart(sortiesCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Sorties (GNF)',
                    data: data,
                    backgroundColor: labels.map((_, i) => palette[(i + 2) % palette.length]),
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false },
                    tooltip: moneyTooltip
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { callback: (v) => formatCompact(v) }
                    },
                    x: {
                        ticks: {
                            maxRotation: 45,
                            minRotation: 0,
                            font: { size: 10 }
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush
