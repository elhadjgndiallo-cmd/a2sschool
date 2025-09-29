@extends('layouts.app')

@section('title', 'Rapports de Comptabilité')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-chart-line text-primary me-2"></i>
                    Rapports de Comptabilité
                </h2>
                <div class="btn-group">
                    <button class="btn btn-outline-primary" onclick="window.print()">
                        <i class="fas fa-print me-1"></i>Imprimer
                    </button>
                    <button class="btn btn-outline-success" onclick="exportToExcel()">
                        <i class="fas fa-file-excel me-1"></i>Exporter Excel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-filter me-2"></i>Filtres
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('comptabilite.rapports') }}">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="date_debut" class="form-label">Date de début</label>
                                <input type="date" class="form-control" id="date_debut" name="date_debut" 
                                       value="{{ request('date_debut', $dateDebut->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="date_fin" class="form-label">Date de fin</label>
                                <input type="date" class="form-control" id="date_fin" name="date_fin" 
                                       value="{{ request('date_fin', $dateFin->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-4 mb-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-search me-1"></i>Filtrer
                                </button>
                                <a href="{{ route('comptabilite.rapports') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-refresh me-1"></i>Réinitialiser
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Résumé financier -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ number_format($rapports['total_revenus'], 0, ',', ' ') }}</h3>
                    <small>Total Revenus (GNF)</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ number_format($rapports['total_depenses'], 0, ',', ' ') }}</h3>
                    <small>Total Dépenses (GNF)</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card {{ $rapports['benefice'] >= 0 ? 'bg-primary' : 'bg-warning' }} text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ number_format($rapports['benefice'], 0, ',', ' ') }}</h3>
                    <small>Bénéfice (GNF)</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie me-2"></i>Revenus par Type
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="revenusChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Dépenses par Catégorie
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="depensesChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Évolution temporelle -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>Évolution des Revenus et Dépenses
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="evolutionChart" width="800" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Top 5 des classes -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-trophy me-2"></i>Top 5 des Classes par Revenus
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($topClasses as $index => $classe)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <span class="badge bg-primary me-2">{{ $index + 1 }}</span>
                                <strong>{{ $classe->nom }}</strong>
                            </div>
                            <span class="text-success fw-bold">
                                {{ number_format($classe->total, 0, ',', ' ') }} GNF
                            </span>
                        </div>
                    @empty
                        <p class="text-muted">Aucune donnée disponible</p>
                    @endforelse
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-credit-card me-2"></i>Paiements par Mode
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($rapports['paiements_par_mode'] as $paiement)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <i class="fas fa-credit-card me-2"></i>
                                <strong>{{ ucfirst($paiement->mode_paiement) }}</strong>
                            </div>
                            <span class="text-primary fw-bold">
                                {{ number_format($paiement->total, 0, ',', ' ') }} GNF
                            </span>
                        </div>
                    @empty
                        <p class="text-muted">Aucune donnée disponible</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau détaillé -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-table me-2"></i>Détail des Revenus par Type
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                    <tr>
                                        <th>Source</th>
                                        <th class="text-end">Montant (GNF)</th>
                                        <th class="text-end">Pourcentage</th>
                                    </tr>
                            </thead>
                            <tbody>
                                @foreach($rapports['revenus_par_type'] as $revenu)
                                    <tr>
                                        <td>
                                            <i class="fas fa-arrow-up text-success me-2"></i>
                                            {{ $revenu->source }}
                                        </td>
                                        <td class="text-end">
                                            <strong>{{ number_format($revenu->total, 0, ',', ' ') }}</strong>
                                        </td>
                                        <td class="text-end">
                                            <span class="badge bg-primary">
                                                {{ $rapports['total_revenus'] > 0 ? number_format(($revenu->total / $rapports['total_revenus']) * 100, 1) : 0 }}%
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Graphique des revenus par type
    const revenusCtx = document.getElementById('revenusChart').getContext('2d');
    new Chart(revenusCtx, {
        type: 'pie',
        data: {
            labels: {!! json_encode($rapports['revenus_par_type']->pluck('source')) !!},
            datasets: [{
                data: {!! json_encode($rapports['revenus_par_type']->pluck('total')) !!},
                backgroundColor: [
                    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Graphique des dépenses par catégorie
    const depensesCtx = document.getElementById('depensesChart').getContext('2d');
    new Chart(depensesCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($rapports['depenses_par_categorie']->pluck('type_depense')) !!},
            datasets: [{
                label: 'Montant (GNF)',
                data: {!! json_encode($rapports['depenses_par_categorie']->pluck('total')) !!},
                backgroundColor: '#FF6384'
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Graphique d'évolution
    const evolutionCtx = document.getElementById('evolutionChart').getContext('2d');
    new Chart(evolutionCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($evolutionRevenus->map(function($item) { return $item->annee . '-' . str_pad($item->mois, 2, '0', STR_PAD_LEFT); })) !!},
            datasets: [{
                label: 'Revenus',
                data: {!! json_encode($evolutionRevenus->pluck('total')) !!},
                borderColor: '#36A2EB',
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                tension: 0.1
            }, {
                label: 'Dépenses',
                data: {!! json_encode($evolutionDepenses->pluck('total')) !!},
                borderColor: '#FF6384',
                backgroundColor: 'rgba(255, 99, 132, 0.1)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    function exportToExcel() {
        // Fonction pour exporter vers Excel
        alert('Fonction d\'export Excel à implémenter');
    }
</script>
@endpush
@endsection
