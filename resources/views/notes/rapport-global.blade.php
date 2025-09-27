@extends('layouts.app')

@section('title', 'Rapport Global des Notes')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-chart-line me-2"></i>Rapport Global des Notes</h2>
                <a href="{{ route('notes.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour
                </a>
            </div>

            <!-- Statistiques générales -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4>{{ $statistiques['total_notes'] }}</h4>
                                    <p class="mb-0">Total Notes</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-clipboard-list fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4>{{ round($statistiques['moyenne_generale'], 2) }}/20</h4>
                                    <p class="mb-0">Moyenne Générale</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-chart-bar fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4>{{ $statistiques['notes_ce_mois'] }}</h4>
                                    <p class="mb-0">Notes ce mois</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-calendar fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4>{{ $statistiques['classes_actives'] }}</h4>
                                    <p class="mb-0">Classes Actives</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-users fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rapport par classe -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Rapport par Classe</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Classe</th>
                                    <th>Niveau</th>
                                    <th>Nb Élèves</th>
                                    <th>Nb Notes</th>
                                    <th>Moyenne Classe</th>
                                    <th>Note Min</th>
                                    <th>Note Max</th>
                                    <th>Taux Réussite</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($classes as $classe)
                                @php
                                    // Récupérer toutes les notes des élèves de cette classe
                                    $notesClasse = $classe->eleves->flatMap(function($eleve) {
                                        return $eleve->notes;
                                    });
                                    
                                    $moyenneClasse = $notesClasse->avg('note_finale') ?? 0;
                                    $noteMin = $notesClasse->min('note_finale') ?? 0;
                                    $noteMax = $notesClasse->max('note_finale') ?? 0;
                                    $notesReussies = $notesClasse->where('note_finale', '>=', 10)->count();
                                    $tauxReussite = $notesClasse->count() > 0 ? 
                                        round(($notesReussies / $notesClasse->count()) * 100, 1) : 0;
                                @endphp
                                <tr>
                                    <td><strong>{{ $classe->nom }}</strong></td>
                                    <td>{{ $classe->niveau }}</td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $classe->eleves->count() }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ $notesClasse->count() }}</span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $moyenneClasse >= 10 ? 'bg-success' : 'bg-danger' }}">
                                            {{ round($moyenneClasse, 2) }}/20
                                        </span>
                                    </td>
                                    <td>{{ round($noteMin, 2) }}</td>
                                    <td>{{ round($noteMax, 2) }}</td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar {{ $tauxReussite >= 50 ? 'bg-success' : 'bg-danger' }}" 
                                                 style="width: {{ $tauxReussite }}%">
                                                {{ $tauxReussite }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('notes.saisir', $classe->id) }}" 
                                               class="btn btn-outline-primary" title="Saisir notes">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="{{ route('notes.bulletins', $classe->id) }}" 
                                               class="btn btn-outline-success" title="Bulletins">
                                                <i class="fas fa-file-alt"></i>
                                            </a>
                                            <a href="{{ route('notes.statistiques', $classe->id) }}" 
                                               class="btn btn-outline-info" title="Statistiques">
                                                <i class="fas fa-chart-bar"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Graphiques -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Répartition des Notes</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="notesChart" height="200"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Évolution Mensuelle</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="evolutionChart" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Graphique de répartition des notes
const notesCtx = document.getElementById('notesChart').getContext('2d');
new Chart(notesCtx, {
    type: 'doughnut',
    data: {
        labels: ['0-5', '5-10', '10-15', '15-20'],
        datasets: [{
            data: [15, 25, 45, 15], // Données d'exemple
            backgroundColor: [
                '#dc3545',
                '#fd7e14', 
                '#28a745',
                '#007bff'
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

// Graphique d'évolution mensuelle
const evolutionCtx = document.getElementById('evolutionChart').getContext('2d');
new Chart(evolutionCtx, {
    type: 'line',
    data: {
        labels: ['Sep', 'Oct', 'Nov', 'Déc', 'Jan', 'Fév'],
        datasets: [{
            label: 'Moyenne Générale',
            data: [12.5, 13.2, 12.8, 13.5, 13.1, 13.8],
            borderColor: '#007bff',
            backgroundColor: 'rgba(0, 123, 255, 0.1)',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                max: 20
            }
        }
    }
});
</script>
@endpush
@endsection
