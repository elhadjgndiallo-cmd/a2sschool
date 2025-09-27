@extends('layouts.app')

@section('title', 'Statistiques d\'Absences')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>Statistiques d'Absences
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

    <!-- Statistiques d'absences principales -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                        <div>
                            <h6 class="card-title text-white mb-1">Total Absences</h6>
                            <h3 class="mt-2 mb-0 text-white">{{ $statsAbsences['total'] ?? 0 }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-clock fa-2x me-3"></i>
                        <div>
                            <h6 class="card-title text-white mb-1">Non Justifiées</h6>
                            <h3 class="mt-2 mb-0 text-white">{{ $statsAbsences['non_justifiees'] ?? 0 }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle fa-2x me-3"></i>
                        <div>
                            <h6 class="card-title text-white mb-1">Justifiées</h6>
                            <h3 class="mt-2 mb-0 text-white">{{ $statsAbsences['justifiees'] ?? 0 }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-percentage fa-2x me-3"></i>
                        <div>
                            <h6 class="card-title text-white mb-1">Taux d'Absentéisme</h6>
                            <h3 class="mt-2 mb-0 text-white">{{ $statsAbsences['taux_absenteisme'] ?? 0 }}%</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques d'absences -->
    <div class="row mb-4">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-line me-2"></i>Évolution des Absences (12 derniers mois)
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="absencesChart" height="100"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-pie me-2"></i>Types d'Absences
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="typesAbsencesChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Détails des absences -->
    <div class="row">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list me-2"></i>Dernières Absences
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Élève</th>
                                    <th>Matière</th>
                                    <th>Date</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dernieresAbsences ?? [] as $absence)
                                <tr>
                                    <td>{{ $absence->eleve->utilisateur->prenom }} {{ $absence->eleve->utilisateur->nom }}</td>
                                    <td>{{ $absence->matiere ? $absence->matiere->nom : 'Toutes matières' }}</td>
                                    <td>{{ $absence->date_absence->format('d/m/Y') }}</td>
                                    <td>
                                        @if($absence->statut == 'justifiee')
                                            <span class="badge bg-success">Justifiée</span>
                                        @elseif($absence->statut == 'en_attente')
                                            <span class="badge bg-warning">En attente</span>
                                        @else
                                            <span class="badge bg-danger">Non justifiée</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">Aucune absence récente</td>
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
                        <i class="fas fa-chart-bar me-2"></i>Top 5 Classes avec Plus d'Absences
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Classe</th>
                                    <th>Absences</th>
                                    <th>Taux</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topAbsencesParClasse ?? [] as $classe)
                                <tr>
                                    <td>{{ $classe->nom }}</td>
                                    <td><span class="badge bg-danger">{{ $classe->total }}</span></td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-warning" style="width: {{ $classe->taux_absenteisme ?? 0 }}%">
                                                {{ $classe->taux_absenteisme ?? 0 }}%
                                            </div>
                                        </div>
                                    </td>
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
    // Graphique des absences par mois
    const absencesCtx = document.getElementById('absencesChart').getContext('2d');
    const absencesData = @json($absencesParMois ?? []);
    
    new Chart(absencesCtx, {
        type: 'line',
        data: {
            labels: absencesData.map(item => {
                const mois = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 
                             'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];
                return mois[item.mois - 1] || `Mois ${item.mois}`;
            }),
            datasets: [{
                label: 'Absences',
                data: absencesData.map(item => item.total),
                borderColor: 'rgb(220, 53, 69)',
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Graphique des types d'absences
    const typesCtx = document.getElementById('typesAbsencesChart').getContext('2d');
    const typesData = @json($typesAbsences ?? []);
    
    new Chart(typesCtx, {
        type: 'doughnut',
        data: {
            labels: typesData.map(item => {
                const labels = {
                    'absence': 'Absence',
                    'retard': 'Retard',
                    'sortie_anticipee': 'Sortie Anticipée'
                };
                return labels[item.type] || item.type;
            }),
            datasets: [{
                data: typesData.map(item => item.total),
                backgroundColor: [
                    '#dc3545',
                    '#ffc107',
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







