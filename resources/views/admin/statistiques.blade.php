@extends('layouts.app')

@section('title', 'Statistiques du système')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Statistiques du système</h4>
                <div class="page-title-right">
                    <button class="btn btn-primary" onclick="window.print()">
                        <i class="fas fa-print me-1"></i> Imprimer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques des élèves par classe -->
    <div class="row">
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

        <!-- Statistiques des paiements par mois -->
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Paiements par mois ({{ date('Y') }})</h5>
                </div>
                <div class="card-body">
                    <canvas id="chartPaiementsParMois" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Statistiques des absences par classe -->
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Absences par classe</h5>
                </div>
                <div class="card-body">
                    <canvas id="chartAbsencesParClasse" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Statistiques des notes moyennes par matière -->
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Notes moyennes par matière</h5>
                </div>
                <div class="card-body">
                    <canvas id="chartNotesParMatiere" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableaux détaillés -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Détails des statistiques</h5>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="eleves-tab" data-bs-toggle="tab" data-bs-target="#eleves" type="button" role="tab" aria-controls="eleves" aria-selected="true">Élèves</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="paiements-tab" data-bs-toggle="tab" data-bs-target="#paiements" type="button" role="tab" aria-controls="paiements" aria-selected="false">Paiements</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="absences-tab" data-bs-toggle="tab" data-bs-target="#absences" type="button" role="tab" aria-controls="absences" aria-selected="false">Absences</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="notes-tab" data-bs-toggle="tab" data-bs-target="#notes" type="button" role="tab" aria-controls="notes" aria-selected="false">Notes</button>
                        </li>
                    </ul>
                    <div class="tab-content pt-3" id="myTabContent">
                        <!-- Tab Élèves -->
                        <div class="tab-pane fade show active" id="eleves" role="tabpanel" aria-labelledby="eleves-tab">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Classe</th>
                                            <th>Nombre d'élèves</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($elevesParClasse as $classe)
                                        <tr>
                                            <td>{{ $classe->nom }}</td>
                                            <td>{{ $classe->total }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Tab Paiements -->
                        <div class="tab-pane fade" id="paiements" role="tabpanel" aria-labelledby="paiements-tab">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Mois</th>
                                            <th>Montant total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($paiementsParMois as $paiement)
                                        <tr>
                                            <td>{{ date('F', mktime(0, 0, 0, $paiement->mois, 1)) }}</td>
                                            <td>{{ number_format($paiement->total, 0, ',', ' ') }} GNF</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Tab Absences -->
                        <div class="tab-pane fade" id="absences" role="tabpanel" aria-labelledby="absences-tab">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Classe</th>
                                            <th>Nombre d'absences</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($absencesParClasse as $absence)
                                        <tr>
                                            <td>{{ $absence->nom }}</td>
                                            <td>{{ $absence->total }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Tab Notes -->
                        <div class="tab-pane fade" id="notes" role="tabpanel" aria-labelledby="notes-tab">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Matière</th>
                                            <th>Note moyenne</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($notesParMatiere as $note)
                                        <tr>
                                            <td>{{ $note->nom }}</td>
                                            <td>{{ number_format($note->moyenne, 2, ',', ' ') }}/20</td>
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
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Configuration des couleurs
        const colors = [
            'rgba(75, 192, 192, 0.6)',
            'rgba(54, 162, 235, 0.6)',
            'rgba(255, 206, 86, 0.6)',
            'rgba(255, 99, 132, 0.6)',
            'rgba(153, 102, 255, 0.6)',
            'rgba(255, 159, 64, 0.6)',
            'rgba(199, 199, 199, 0.6)',
            'rgba(83, 102, 255, 0.6)',
            'rgba(40, 159, 64, 0.6)',
            'rgba(210, 199, 199, 0.6)'
        ];

        // Graphique des élèves par classe
        const ctxEleves = document.getElementById('chartElevesParClasse').getContext('2d');
        new Chart(ctxEleves, {
            type: 'bar',
            data: {
                labels: {!! json_encode($elevesParClasse->pluck('nom')) !!},
                datasets: [{
                    label: 'Nombre d\'élèves',
                    data: {!! json_encode($elevesParClasse->pluck('total')) !!},
                    backgroundColor: colors,
                    borderColor: colors.map(color => color.replace('0.6', '1')),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Répartition des élèves par classe'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Graphique des paiements par mois
        const ctxPaiements = document.getElementById('chartPaiementsParMois').getContext('2d');
        new Chart(ctxPaiements, {
            type: 'line',
            data: {
                labels: {!! json_encode($paiementsParMois->map(function($item) { return date('F', mktime(0, 0, 0, $item->mois, 1)); })) !!},
                datasets: [{
                    label: 'Montant total (GNF)',
                    data: {!! json_encode($paiementsParMois->pluck('total')) !!},
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Évolution des paiements par mois'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Graphique des absences par classe
        const ctxAbsences = document.getElementById('chartAbsencesParClasse').getContext('2d');
        new Chart(ctxAbsences, {
            type: 'pie',
            data: {
                labels: {!! json_encode($absencesParClasse->pluck('nom')) !!},
                datasets: [{
                    label: 'Nombre d\'absences',
                    data: {!! json_encode($absencesParClasse->pluck('total')) !!},
                    backgroundColor: colors,
                    borderColor: colors.map(color => color.replace('0.6', '1')),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right',
                    },
                    title: {
                        display: true,
                        text: 'Répartition des absences par classe'
                    }
                }
            }
        });

        // Graphique des notes par matière
        const ctxNotes = document.getElementById('chartNotesParMatiere').getContext('2d');
        new Chart(ctxNotes, {
            type: 'radar',
            data: {
                labels: {!! json_encode($notesParMatiere->pluck('nom')) !!},
                datasets: [{
                    label: 'Note moyenne /20',
                    data: {!! json_encode($notesParMatiere->pluck('moyenne')) !!},
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 2,
                    pointBackgroundColor: 'rgba(255, 99, 132, 1)',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgba(255, 99, 132, 1)'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Notes moyennes par matière'
                    }
                },
                scales: {
                    r: {
                        min: 0,
                        max: 20,
                        ticks: {
                            stepSize: 5
                        }
                    }
                }
            }
        });
    });
</script>
@endpush