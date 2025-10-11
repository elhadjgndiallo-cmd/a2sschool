@extends('layouts.app')

@section('title', 'Rapport des Absences - ' . $classe->nom)

@php
use App\Helpers\SchoolHelper;

$schoolInfo = SchoolHelper::getDocumentInfo();
@endphp

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-file-alt me-2"></i>
        Rapport des Absences - {{ $classe->nom }}
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('absences.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Retour
        </a>
        <button onclick="window.print()" class="btn btn-outline-primary ms-2">
            <i class="fas fa-print me-1"></i>
            Imprimer
        </button>
    </div>
</div>

<!-- Filtres de date -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-filter me-2"></i>
            Filtres de Période
        </h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('absences.rapport', $classe->id) }}" class="row g-3">
            <div class="col-md-4">
                <label for="date_debut" class="form-label">Date de début</label>
                <input type="date" class="form-control" id="date_debut" name="date_debut" 
                       value="{{ $dateDebut }}" required>
            </div>
            <div class="col-md-4">
                <label for="date_fin" class="form-label">Date de fin</label>
                <input type="date" class="form-control" id="date_fin" name="date_fin" 
                       value="{{ $dateFin }}" required>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i>
                    Filtrer
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Statistiques générales -->
<div class="row mb-4">
    <div class="col-md-2">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0">{{ $statistiques['total_eleves'] }}</h4>
                        <p class="mb-0">Total élèves</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0">{{ $statistiques['eleves_presents'] }}</h4>
                        <p class="mb-0">Présents</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0">{{ $statistiques['eleves_avec_absences'] }}</h4>
                        <p class="mb-0">Avec absences</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-user-times fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0">{{ $statistiques['total_non_justifiees'] }}</h4>
                        <p class="mb-0">Non justifiées</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0">{{ $statistiques['total_retards'] }}</h4>
                        <p class="mb-0">Retards</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-secondary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0">{{ $statistiques['total_absences'] }}</h4>
                        <p class="mb-0">Total absences</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-list fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Rapport détaillé -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-list me-2"></i>
            Rapport Détaillé - Période du {{ \Carbon\Carbon::parse($dateDebut)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($dateFin)->format('d/m/Y') }}
        </h5>
    </div>
    <div class="card-body">
        @if($rapportComplet->count() > 0)
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Élève</th>
                            <th>Statut</th>
                            <th>Total Absences</th>
                            <th>Non Justifiées</th>
                            <th>Retards</th>
                            <th>Détails</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rapportComplet as $index => $data)
                        <tr class="{{ $data['statut'] == 'present' ? 'table-success' : '' }}">
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm {{ $data['statut'] == 'present' ? 'bg-success' : 'bg-primary' }} text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                        {{ substr($data['eleve']->utilisateur->nom, 0, 1) }}{{ substr($data['eleve']->utilisateur->prenom, 0, 1) }}
                                    </div>
                                    <div>
                                        <strong>{{ $data['eleve']->utilisateur->nom }} {{ $data['eleve']->utilisateur->prenom }}</strong>
                                        <br>
                                        <small class="text-muted">N° Étudiant: {{ $data['eleve']->numero_etudiant }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($data['statut'] == 'present')
                                    <span class="badge bg-success fs-6">
                                        <i class="fas fa-check me-1"></i>Présent
                                    </span>
                                @else
                                    <span class="badge bg-warning fs-6">
                                        <i class="fas fa-user-times me-1"></i>Avec absences
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($data['total_absences'] > 0)
                                    <span class="badge bg-warning fs-6">{{ $data['total_absences'] }}</span>
                                @else
                                    <span class="badge bg-success fs-6">0</span>
                                @endif
                            </td>
                            <td>
                                @if($data['non_justifiees'] > 0)
                                    <span class="badge bg-danger fs-6">{{ $data['non_justifiees'] }}</span>
                                @else
                                    <span class="badge bg-success fs-6">0</span>
                                @endif
                            </td>
                            <td>
                                @if($data['retards'] > 0)
                                    <span class="badge bg-info fs-6">{{ $data['retards'] }}</span>
                                @else
                                    <span class="badge bg-success fs-6">0</span>
                                @endif
                            </td>
                            <td>
                                @if($data['total_absences'] > 0)
                                    <button class="btn btn-sm btn-outline-primary" 
                                            data-bs-toggle="collapse" 
                                            data-bs-target="#details-{{ $data['eleve']->id }}" 
                                            aria-expanded="false">
                                        <i class="fas fa-eye me-1"></i>
                                        Voir détails
                                    </button>
                                @else
                                    <span class="text-muted">Aucune absence</span>
                                @endif
                            </td>
                        </tr>
                        @if($data['total_absences'] > 0)
                        <tr class="collapse" id="details-{{ $data['eleve']->id }}">
                            <td colspan="7">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <i class="fas fa-calendar-alt me-2"></i>
                                            Détail des absences
                                        </h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Matière</th>
                                                        <th>Type</th>
                                                        <th>Statut</th>
                                                        <th>Justification</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($data['absences'] as $absence)
                                                    <tr>
                                                        <td>{{ \Carbon\Carbon::parse($absence->date_absence)->format('d/m/Y') }}</td>
                                                        <td>
                                                            @if($absence->matiere)
                                                                {{ $absence->matiere->nom }}
                                                            @else
                                                                <span class="text-muted">Toutes matières</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($absence->type == 'retard')
                                                                <span class="badge bg-info">Retard</span>
                                                            @else
                                                                <span class="badge bg-warning">Absence</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($absence->statut == 'justifiee')
                                                                <span class="badge bg-success">Justifiée</span>
                                                            @else
                                                                <span class="badge bg-danger">Non justifiée</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($absence->motif)
                                                                {{ $absence->motif }}
                                                            @else
                                                                <span class="text-muted">Aucune</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-users fa-3x text-primary mb-3"></i>
                <h4 class="text-muted">Aucun élève dans cette classe</h4>
                <p class="text-muted">Cette classe ne contient aucun élève pour le moment.</p>
            </div>
        @endif
    </div>
</div>

<!-- Informations de l'école pour l'impression -->
<div class="d-none d-print-block mt-4">
    <div class="text-center">
        <h5>{{ $schoolInfo['school_name'] }}</h5>
        <p class="text-muted">
            Rapport généré le {{ \Carbon\Carbon::now()->format('d/m/Y à H:i') }}
        </p>
    </div>
</div>

<style>
@media print {
    .btn-toolbar, .card-header, .btn, .collapse {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    
    .table {
        font-size: 12px;
    }
    
    .badge {
        font-size: 10px;
    }
    
    .avatar-sm {
        width: 30px !important;
        height: 30px !important;
        font-size: 10px !important;
    }
}

.avatar-sm {
    width: 40px;
    height: 40px;
    font-size: 14px;
    font-weight: bold;
}
</style>
@endsection

