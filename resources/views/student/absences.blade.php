@extends('layouts.app')

@section('title', 'Mes Absences')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-clock me-2"></i>
        Mes Absences
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Retour au Dashboard
        </a>
    </div>
</div>

<!-- Statistiques -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body text-center">
                <i class="fas fa-list fa-2x mb-2"></i>
                <h4>{{ $statistiques['total_absences'] }}</h4>
                <p class="mb-0">Total Absences</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <i class="fas fa-check fa-2x mb-2"></i>
                <h4>{{ $statistiques['absences_justifiees'] }}</h4>
                <p class="mb-0">Justifiées</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body text-center">
                <i class="fas fa-times fa-2x mb-2"></i>
                <h4>{{ $statistiques['absences_non_justifiees'] }}</h4>
                <p class="mb-0">Non Justifiées</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <i class="fas fa-calendar fa-2x mb-2"></i>
                <h4>{{ $statistiques['absences_ce_mois'] }}</h4>
                <p class="mb-0">Ce Mois</p>
            </div>
        </div>
    </div>
</div>

<!-- Liste des absences -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-list me-2"></i>
            Historique de mes absences
        </h5>
    </div>
    <div class="card-body">
        @if($absences->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Date</th>
                            <th>Matière</th>
                            <th>Heure</th>
                            <th>Statut</th>
                            <th>Motif</th>
                            <th>Enregistré par</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($absences as $absence)
                        <tr>
                            <td>{{ $absence->date_absence->format('d/m/Y') }}</td>
                            <td>
                                @if($absence->matiere)
                                    <span class="badge" style="background-color: {{ $absence->matiere->couleur ?? '#007bff' }}">
                                        {{ $absence->matiere->nom }}
                                    </span>
                                @else
                                    <span class="text-muted">Général</span>
                                @endif
                            </td>
                            <td>
                                @if($absence->heure_debut && $absence->heure_fin)
                                    {{ $absence->heure_debut->format('H:i') }} - {{ $absence->heure_fin->format('H:i') }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($absence->statut == 'justifiee')
                                    <span class="badge bg-success">
                                        <i class="fas fa-check me-1"></i>Justifiée
                                    </span>
                                @elseif($absence->statut == 'non_justifiee')
                                    <span class="badge bg-danger">
                                        <i class="fas fa-times me-1"></i>Non Justifiée
                                    </span>
                                @else
                                    <span class="badge bg-warning">
                                        <i class="fas fa-clock me-1"></i>En Attente
                                    </span>
                                @endif
                            </td>
                            <td>{{ $absence->motif ?? '-' }}</td>
                            <td>{{ $absence->saisiPar->name }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center">
                {{ $absences->links() }}
            </div>
        @else
            <div class="text-center text-muted py-5">
                <i class="fas fa-check-circle fa-3x mb-3"></i>
                <h5>Aucune absence</h5>
                <p>Félicitations ! Vous n'avez aucune absence enregistrée.</p>
            </div>
        @endif
    </div>
</div>

<!-- Actions -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <a href="{{ route('student.emploi-temps') }}" class="btn btn-primary btn-block">
                            <i class="fas fa-calendar me-2"></i>
                            Mon Emploi du Temps
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('student.notes') }}" class="btn btn-success btn-block">
                            <i class="fas fa-chart-line me-2"></i>
                            Mes Notes
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('student.bulletin') }}" class="btn btn-info btn-block">
                            <i class="fas fa-file-alt me-2"></i>
                            Mon Bulletin
                        </a>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-warning btn-block" onclick="window.print()">
                            <i class="fas fa-print me-2"></i>
                            Imprimer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.btn-block { width: 100%; }
@media print {
    .btn-toolbar, .card-header, .btn {
        display: none !important;
    }
}
</style>
@endpush
@endsection




