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

    <!-- Graphique d'évolution -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line text-primary me-2"></i>Évolution Revenus vs Dépenses
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="evolutionChart" style="max-height: 400px;"></canvas>
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
    // Données pour le graphique d'évolution
    const evolutionData = @json($evolutionData);
    
    const ctx = document.getElementById('evolutionChart');
    
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: evolutionData.labels,
                datasets: [
                    {
                        label: 'Revenus (GNF)',
                        data: evolutionData.revenus,
                        borderColor: 'rgb(40, 167, 69)',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 3,
                        pointRadius: 5,
                        pointHoverRadius: 7
                    },
                    {
                        label: 'Dépenses (GNF)',
                        data: evolutionData.depenses,
                        borderColor: 'rgb(220, 53, 69)',
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 3,
                        pointRadius: 5,
                        pointHoverRadius: 7
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            font: {
                                size: 14,
                                weight: 'bold'
                            },
                            padding: 20,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('fr-FR', {
                                        style: 'decimal',
                                        minimumFractionDigits: 0,
                                        maximumFractionDigits: 0
                                    }).format(context.parsed.y) + ' GNF';
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('fr-FR', {
                                    notation: 'compact',
                                    compactDisplay: 'short'
                                }).format(value) + ' GNF';
                            },
                            font: {
                                size: 12
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                size: 12
                            }
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush
