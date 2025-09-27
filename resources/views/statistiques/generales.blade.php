@extends('layouts.app')

@section('title', 'Statistiques Générales')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Statistiques Générales</h4>
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

    <!-- Statistiques de base -->
    <div class="row">
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-user-graduate fa-2x me-3"></i>
                        <div>
                            <h6 class="card-title text-white mb-1">Élèves</h6>
                            <h3 class="mt-2 mb-0 text-white">{{ $stats['eleves'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-chalkboard-teacher fa-2x me-3"></i>
                        <div>
                            <h6 class="card-title text-white mb-1">Enseignants</h6>
                            <h3 class="mt-2 mb-0 text-white">{{ $stats['enseignants'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-users fa-2x me-3"></i>
                        <div>
                            <h6 class="card-title text-white mb-1">Parents</h6>
                            <h3 class="mt-2 mb-0 text-white">{{ $stats['parents'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-chalkboard fa-2x me-3"></i>
                        <div>
                            <h6 class="card-title text-white mb-1">Classes</h6>
                            <h3 class="mt-2 mb-0 text-white">{{ $stats['classes'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-book fa-2x me-3"></i>
                        <div>
                            <h6 class="card-title text-white mb-1">Matières</h6>
                            <h3 class="mt-2 mb-0 text-white">{{ $stats['matieres'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card bg-dark text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-chart-line fa-2x me-3"></i>
                        <div>
                            <h6 class="card-title text-white mb-1">Total</h6>
                            <h3 class="mt-2 mb-0 text-white">{{ array_sum($stats) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques financières -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body d-flex flex-column">
                    <div class="text-center mb-3">
                        <h2 class="card-title mb-0 font-weight-bold">{{ number_format($statsFinancieres['totalEntrees'], 0, ',', ' ') }} GNF</h2>
                        <p class="card-text mb-2">Total Entrées</p>
                    </div>
                    <div class="mt-auto">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small class="text-light">Manuelles:</small>
                            <small class="text-light font-weight-bold">{{ number_format($statsFinancieres['totalEntreesManuelles'], 0, ',', ' ') }} GNF</small>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-light">Scolarité:</small>
                            <small class="text-light font-weight-bold">{{ number_format($statsFinancieres['totalPaiementsFrais'], 0, ',', ' ') }} GNF</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white h-100">
                <div class="card-body d-flex flex-column">
                    <div class="text-center mb-3">
                        <h2 class="card-title mb-0 font-weight-bold">{{ number_format($statsFinancieres['totalSorties'], 0, ',', ' ') }} GNF</h2>
                        <p class="card-text mb-2">Total Sorties</p>
                    </div>
                    <div class="mt-auto">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small class="text-light">Manuelles:</small>
                            <small class="text-light font-weight-bold">{{ number_format($statsFinancieres['totalSortiesManuelles'], 0, ',', ' ') }} GNF</small>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-light">Salaires:</small>
                            <small class="text-light font-weight-bold">{{ number_format($statsFinancieres['totalSalairesEnseignants'], 0, ',', ' ') }} GNF</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body d-flex flex-column justify-content-center text-center">
                    <h2 class="card-title mb-0 font-weight-bold">{{ number_format($statsFinancieres['totalPaiementsFrais'], 0, ',', ' ') }} GNF</h2>
                    <p class="card-text">Frais de Scolarité</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card {{ $statsFinancieres['solde'] >= 0 ? 'bg-primary' : 'bg-warning' }} text-white h-100">
                <div class="card-body d-flex flex-column">
                    <div class="text-center mb-3">
                        <h2 class="card-title mb-0 font-weight-bold">{{ number_format($statsFinancieres['solde'], 0, ',', ' ') }} GNF</h2>
                        <p class="card-text mb-2">Solde</p>
                    </div>
                    <div class="mt-auto text-center">
                        <small class="text-light">Entrées - Sorties</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques -->
    <div class="row">
        <!-- Répartition des élèves par classe -->
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Répartition des élèves par classe</h5>
                </div>
                <div class="card-body">
                    <canvas id="chartElevesParClasse" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Répartition des élèves par sexe -->
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Répartition des élèves par sexe</h5>
                </div>
                <div class="card-body">
                    <canvas id="chartElevesParSexe" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Évolution des paiements -->
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Évolution des paiements (12 derniers mois)</h5>
                </div>
                <div class="card-body">
                    <canvas id="chartPaiementsParMois" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Évolution des absences -->
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Évolution des absences (12 derniers mois)</h5>
                </div>
                <div class="card-body">
                    <canvas id="chartAbsencesParMois" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Top 5 des classes avec le plus d'absences -->
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Top 5 des classes avec le plus d'absences (6 derniers mois)</h5>
                </div>
                <div class="card-body">
                    <canvas id="chartTopAbsences" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Répartition des types d'absences -->
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Répartition des types d'absences (6 derniers mois)</h5>
                </div>
                <div class="card-body">
                    <canvas id="chartTypesAbsences" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableaux détaillés -->
    <div class="row">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Détail des élèves par classe</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Classe</th>
                                    <th class="text-center">Nombre d'élèves</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($elevesParClasse as $classe)
                                <tr>
                                    <td>{{ $classe->nom }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-primary">{{ $classe->total }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Moyennes par matière</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Matière</th>
                                    <th class="text-center">Moyenne</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($moyennesParMatiere as $matiere)
                                <tr>
                                    <td>{{ $matiere->nom }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ $matiere->moyenne >= 10 ? 'bg-success' : 'bg-danger' }}">
                                            {{ number_format($matiere->moyenne, 2) }}/20
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted">Aucune note enregistrée</td>
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

@push('scripts')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configuration des couleurs
    const colors = {
        primary: '#007bff',
        success: '#28a745',
        info: '#17a2b8',
        warning: '#ffc107',
        danger: '#dc3545',
        secondary: '#6c757d',
        light: '#f8f9fa',
        dark: '#343a40'
    };

    // Graphique des élèves par classe
    const ctxElevesParClasse = document.getElementById('chartElevesParClasse').getContext('2d');
    new Chart(ctxElevesParClasse, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($elevesParClasse->pluck('nom')) !!},
            datasets: [{
                data: {!! json_encode($elevesParClasse->pluck('total')) !!},
                backgroundColor: [
                    colors.primary,
                    colors.success,
                    colors.info,
                    colors.warning,
                    colors.danger,
                    colors.secondary
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

    // Graphique des élèves par sexe
    const ctxElevesParSexe = document.getElementById('chartElevesParSexe').getContext('2d');
    new Chart(ctxElevesParSexe, {
        type: 'pie',
        data: {
            labels: {!! json_encode($elevesParSexe->pluck('sexe')) !!},
            datasets: [{
                data: {!! json_encode($elevesParSexe->pluck('total')) !!},
                backgroundColor: [colors.info, colors.warning]
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

    // Graphique des paiements par mois
    const ctxPaiementsParMois = document.getElementById('chartPaiementsParMois').getContext('2d');
    new Chart(ctxPaiementsParMois, {
        type: 'line',
        data: {
            labels: {!! json_encode($paiementsParMois->map(function($item) { return date('M Y', mktime(0, 0, 0, $item->mois, 1, $item->annee)); })) !!},
            datasets: [{
                label: 'Paiements (GNF)',
                data: {!! json_encode($paiementsParMois->pluck('total')) !!},
                borderColor: colors.success,
                backgroundColor: colors.success + '20',
                tension: 0.4
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
                            return new Intl.NumberFormat('fr-FR').format(value) + ' GNF';
                        }
                    }
                }
            }
        }
    });

    // Graphique des absences par mois
    const ctxAbsencesParMois = document.getElementById('chartAbsencesParMois').getContext('2d');
    new Chart(ctxAbsencesParMois, {
        type: 'bar',
        data: {
            labels: {!! json_encode($absencesParMois->map(function($item) { return date('M Y', mktime(0, 0, 0, $item->mois, 1, $item->annee)); })) !!},
            datasets: [{
                label: 'Nombre d\'absences',
                data: {!! json_encode($absencesParMois->pluck('total')) !!},
                backgroundColor: colors.danger + '80',
                borderColor: colors.danger,
                borderWidth: 1
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

    // Graphique top absences par classe
    const ctxTopAbsences = document.getElementById('chartTopAbsences').getContext('2d');
    new Chart(ctxTopAbsences, {
        type: 'horizontalBar',
        data: {
            labels: {!! json_encode($topAbsencesParClasse->pluck('nom')) !!},
            datasets: [{
                label: 'Nombre d\'absences',
                data: {!! json_encode($topAbsencesParClasse->pluck('total')) !!},
                backgroundColor: colors.warning + '80',
                borderColor: colors.warning,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    beginAtZero: true
                }
            }
        }
    });

    // Graphique des types d'absences
    const ctxTypesAbsences = document.getElementById('chartTypesAbsences').getContext('2d');
    new Chart(ctxTypesAbsences, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($typesAbsences->pluck('type')) !!},
            datasets: [{
                data: {!! json_encode($typesAbsences->pluck('total')) !!},
                backgroundColor: [
                    colors.danger,
                    colors.warning,
                    colors.info
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
});

// Fonction d'export PDF
function exportToPDF() {
    window.print();
}
</script>
@endpush
