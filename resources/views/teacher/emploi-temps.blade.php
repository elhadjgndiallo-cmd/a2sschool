@extends('layouts.app')

@section('title', 'Mon Emploi du Temps')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-calendar-alt me-2"></i>
        Mon Emploi du Temps
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('teacher.dashboard') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Retour au Dashboard
        </a>
    </div>
</div>

<!-- Messages de session -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($emploisTemps->isEmpty())
<div class="alert alert-info">
    <i class="fas fa-info-circle me-2"></i>
    <strong>Aucun emploi du temps trouvé.</strong> 
    Veuillez contacter l'administration pour configurer votre emploi du temps.
</div>
@endif

<!-- Statistiques -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <i class="fas fa-calendar fa-2x mb-2"></i>
                <h4>{{ $emploisTemps->count() }}</h4>
                <p class="mb-0">Cours programmés</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <i class="fas fa-chalkboard-teacher fa-2x mb-2"></i>
                <h4>{{ $emploisTemps->pluck('classe.nom')->unique()->count() }}</h4>
                <p class="mb-0">Classes</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <i class="fas fa-book fa-2x mb-2"></i>
                <h4>{{ $emploisTemps->pluck('matiere.nom')->unique()->count() }}</h4>
                <p class="mb-0">Matières</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body text-center">
                <i class="fas fa-clock fa-2x mb-2"></i>
                <h4>{{ $emploisTemps->sum(function($emploi) { return $emploi->heure_fin->diffInMinutes($emploi->heure_debut); }) / 60 }}</h4>
                <p class="mb-0">Heures/semaine</p>
            </div>
        </div>
    </div>
</div>

<!-- Emploi du temps par jour -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-calendar-week me-2"></i>
            Emploi du temps hebdomadaire
        </h5>
    </div>
    <div class="card-body">
        @if($emploisTemps->count() > 0)
            @php
                $jours = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
                $emploisParJour = $emploisTemps->groupBy('jour_semaine');
            @endphp
            
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th width="15%">Heure</th>
                            @foreach($jours as $jour)
                            <th class="text-center">{{ ucfirst($jour) }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $heures = [];
                            foreach($emploisTemps as $emploi) {
                                $heure = $emploi->heure_debut->format('H:i');
                                if (!in_array($heure, $heures)) {
                                    $heures[] = $heure;
                                }
                            }
                            sort($heures);
                        @endphp
                        
                        @foreach($heures as $heure)
                        <tr>
                            <td class="fw-bold">{{ $heure }}</td>
                            @foreach($jours as $jour)
                            <td class="text-center">
                                @php
                                    $emploisDuJour = $emploisParJour->get($jour, collect())
                                        ->filter(function($emploi) use ($heure) {
                                            return $emploi->heure_debut->format('H:i') === $heure;
                                        });
                                @endphp
                                
                                @if($emploisDuJour->count() > 0)
                                    @if($emploisDuJour->count() > 1)
                                        <div class="alert alert-warning p-2 mb-2">
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                            <small><strong>Conflit détecté:</strong> {{ $emploisDuJour->count() }} cours programmés simultanément</small>
                                        </div>
                                    @endif
                                    @foreach($emploisDuJour as $emploi)
                                    <div class="card mb-2 {{ $emploisDuJour->count() > 1 ? 'border-warning' : '' }}">
                                        <div class="card-body p-2">
                                            <div class="fw-bold text-primary">{{ $emploi->classe->nom }}</div>
                                            <div class="text-muted small">{{ $emploi->matiere->nom }}</div>
                                            <div class="text-muted small">
                                                <i class="fas fa-clock me-1"></i>
                                                {{ $emploi->heure_debut->format('H:i') }} - {{ $emploi->heure_fin->format('H:i') }}
                                            </div>
                                            @if($emploi->salle)
                                            <div class="text-muted small">
                                                <i class="fas fa-door-open me-1"></i>
                                                {{ $emploi->salle }}
                                            </div>
                                            @endif
                                            <div class="mt-1">
                                                <span class="badge bg-{{ $emploi->type_cours == 'cours' ? 'primary' : 'secondary' }}">
                                                    {{ ucfirst($emploi->type_cours) }}
                                                </span>
                                                @if($emploisDuJour->count() > 1)
                                                    <span class="badge bg-warning text-dark ms-1">
                                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                                        Conflit
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center text-muted py-5">
                <i class="fas fa-calendar-times fa-3x mb-3"></i>
                <h5>Aucun cours programmé</h5>
                <p>Vous n'avez pas encore d'emploi du temps assigné. Contactez l'administrateur.</p>
            </div>
        @endif
    </div>
</div>

<!-- Actions -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Actions</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <button type="button" class="btn btn-info btn-block" onclick="imprimerEmploi()">
                            <i class="fas fa-print me-2"></i>
                            Imprimer
                        </button>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-success btn-block" onclick="exporterEmploi()">
                            <i class="fas fa-download me-2"></i>
                            Exporter PDF
                        </button>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-warning btn-block" onclick="partagerEmploi()">
                            <i class="fas fa-share me-2"></i>
                            Partager
                        </button>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('teacher.classes') }}" class="btn btn-primary btn-block">
                            <i class="fas fa-chalkboard-teacher me-2"></i>
                            Mes Classes
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function imprimerEmploi() {
    window.print();
}

function exporterEmploi() {
    alert('Fonction d\'export PDF en cours de développement');
}

function partagerEmploi() {
    alert('Fonction de partage en cours de développement');
}

// Initialiser DataTables si nécessaire
$(document).ready(function() {
    // Aucune initialisation DataTables nécessaire pour cette page
});
</script>
@endpush

@push('styles')
<style>
.btn-block { width: 100%; }
.card .card-body {
    font-size: 0.9rem;
}
@media print {
    .btn-toolbar, .card-header, .btn {
        display: none !important;
    }
}
</style>
@endpush
@endsection