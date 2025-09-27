@extends('layouts.app')

@section('title', 'Statistiques Financières')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">
                    <i class="fas fa-credit-card me-2"></i>Statistiques Financières
                </h4>
                <div class="page-title-right">
                    <button class="btn btn-primary" onclick="window.print()">
                        <i class="fas fa-print me-1"></i> Imprimer
                    </button>
                    <button class="btn btn-success" onclick="exportToPDF()">
                        <i class="fas fa-file-pdf me-1"></i> Exporter PDF
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques financières principales -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-arrow-up fa-2x me-3"></i>
                        <div>
                            <h6 class="card-title text-white mb-1">Total Entrées</h6>
                            <h3 class="mt-2 mb-0 text-white">{{ number_format($statsFinancieres['totalEntrees'] ?? 0, 0, ',', ' ') }} GNF</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-arrow-down fa-2x me-3"></i>
                        <div>
                            <h6 class="card-title text-white mb-1">Total Sorties</h6>
                            <h3 class="mt-2 mb-0 text-white">{{ number_format($statsFinancieres['totalSorties'] ?? 0, 0, ',', ' ') }} GNF</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-balance-scale fa-2x me-3"></i>
                        <div>
                            <h6 class="card-title text-white mb-1">Solde</h6>
                            <h3 class="mt-2 mb-0 text-white {{ ($statsFinancieres['solde'] ?? 0) >= 0 ? 'text-white' : 'text-warning' }}">
                                {{ number_format($statsFinancieres['solde'] ?? 0, 0, ',', ' ') }} GNF
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-graduation-cap fa-2x me-3"></i>
                        <div>
                            <h6 class="card-title text-white mb-1">Paiements Scolaires</h6>
                            <h3 class="mt-2 mb-0 text-white">{{ number_format($statsFinancieres['totalPaiementsFrais'] ?? 0, 0, ',', ' ') }} GNF</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques financiers -->
    <div class="row mb-4">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-line me-2"></i>Évolution des Paiements (12 derniers mois)
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="paiementsChart" height="100"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-pie me-2"></i>Répartition des Entrées
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="entreesChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Détails des paiements -->
    <div class="row">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list me-2"></i>Derniers Paiements
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Élève</th>
                                    <th>Montant</th>
                                    <th>Date</th>
                                    <th>Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($derniersPaiements ?? [] as $paiement)
                                <tr>
                                    <td>{{ $paiement->fraisScolarite->eleve->utilisateur->prenom ?? 'N/A' }} {{ $paiement->fraisScolarite->eleve->utilisateur->nom ?? 'N/A' }}</td>
                                    <td>{{ number_format($paiement->montant_paye, 0, ',', ' ') }} GNF</td>
                                    <td>{{ $paiement->date_paiement->format('d/m/Y') }}</td>
                                    <td><span class="badge bg-success">{{ ucfirst($paiement->mode_paiement) }}</span></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">Aucun paiement récent</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Paiements par Mois
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Mois</th>
                                    <th>Montant</th>
                                    <th>Nombre</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($paiementsParMois ?? [] as $paiement)
                                <tr>
                                    <td>{{ \Carbon\Carbon::create()->month($paiement->mois)->format('F') }}</td>
                                    <td>{{ number_format($paiement->total, 0, ',', ' ') }} GNF</td>
                                    <td><span class="badge bg-primary">{{ $paiement->count ?? 0 }}</span></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center">Aucune donnée disponible</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Graphique des paiements par mois
    const paiementsCtx = document.getElementById('paiementsChart').getContext('2d');
    const paiementsData = @json($paiementsParMois ?? []);
    
    new Chart(paiementsCtx, {
        type: 'line',
        data: {
            labels: paiementsData.map(item => {
                const mois = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 
                             'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];
                return mois[item.mois - 1] || `Mois ${item.mois}`;
            }),
            datasets: [{
                label: 'Paiements (GNF)',
                data: paiementsData.map(item => item.total / 1000000), // Convertir en millions
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value + 'M GNF';
                        }
                    }
                }
            }
        }
    });

    // Graphique des entrées
    const entreesCtx = document.getElementById('entreesChart').getContext('2d');
    
    new Chart(entreesCtx, {
        type: 'doughnut',
        data: {
            labels: ['Paiements Scolaires', 'Entrées Manuelles'],
            datasets: [{
                data: [
                    {{ $statsFinancieres['totalPaiementsFrais'] ?? 0 }},
                    {{ $statsFinancieres['totalEntreesManuelles'] ?? 0 }}
                ],
                backgroundColor: [
                    '#28a745',
                    '#17a2b8'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
</script>
@endsection







