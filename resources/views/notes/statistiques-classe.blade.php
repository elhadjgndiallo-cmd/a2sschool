@extends('layouts.app')

@section('title', 'Statistiques des Notes - ' . $classe->nom)

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-chart-bar me-2"></i>
        Statistiques des Notes - {{ $classe->nom }}
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('notes.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Retour
        </a>
        <a href="{{ route('notes.statistiques.classe.imprimer', $classe->id) }}?periode={{ $periode }}" 
           class="btn btn-success ms-2" target="_blank">
            <i class="fas fa-print me-1"></i>
            Imprimer
        </a>
    </div>
</div>

<!-- Sélection de la période -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Filtres</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('notes.statistiques', $classe->id) }}" class="row g-3">
            <div class="col-md-4">
                <label for="periode" class="form-label">Période</label>
                <select class="form-select" id="periode" name="periode" onchange="this.form.submit()">
                    <option value="trimestre1" {{ $periode == 'trimestre1' ? 'selected' : '' }}>Trimestre 1</option>
                    <option value="trimestre2" {{ $periode == 'trimestre2' ? 'selected' : '' }}>Trimestre 2</option>
                </select>
            </div>
        </form>
    </div>
</div>

<!-- Statistiques générales -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-users fa-2x text-primary mb-2"></i>
                <h4 class="text-primary">{{ $statistiques->count() }}</h4>
                <small>Nombre d'élèves</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-chart-line fa-2x text-success mb-2"></i>
                <h4 class="text-success">
                    {{ $statistiques->count() > 0 ? number_format($statistiques->pluck('moyenne')->avg(), 2) : '0.00' }}
                </h4>
                <small>Moyenne de classe</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-trophy fa-2x text-warning mb-2"></i>
                <h4 class="text-warning">
                    {{ $statistiques->count() > 0 ? number_format($statistiques->pluck('moyenne')->max(), 2) : '0.00' }}
                </h4>
                <small>Meilleure moyenne</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-exclamation-triangle fa-2x text-danger mb-2"></i>
                <h4 class="text-danger">
                    {{ $statistiques->count() > 0 ? number_format($statistiques->pluck('moyenne')->min(), 2) : '0.00' }}
                </h4>
                <small>Plus faible moyenne</small>
            </div>
        </div>
    </div>
</div>

<!-- Classement des élèves -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-list-ol me-2"></i>
            Classement des élèves - {{ ucfirst(str_replace('trimestre', 'Trimestre ', $periode)) }}
        </h5>
    </div>
    <div class="card-body">
        @if($statistiques->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th width="12%">Matricule</th>
                        <th width="20%">Nom</th>
                        <th width="20%">Prénom</th>
                        <th width="12%">Moyenne</th>
                        <th width="8%">Rang</th>
                        <th width="15%">Appréciation</th>
                        <th width="13%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($statistiques as $stat)
                    <tr>
                        <td>
                            <span class="text-muted fw-bold">{{ $stat['eleve']->numero_etudiant }}</span>
                        </td>
                        <td>
                            <strong>{{ $stat['eleve']->utilisateur->nom }}</strong>
                        </td>
                        <td>
                            <strong>{{ $stat['eleve']->utilisateur->prenom }}</strong>
                        </td>
                        <td class="text-center">
                            <span class="badge 
                                @if($stat['moyenne'] >= 16) bg-success
                                @elseif($stat['moyenne'] >= 14) bg-primary
                                @elseif($stat['moyenne'] >= 12) bg-info
                                @elseif($stat['moyenne'] >= 10) bg-warning
                                @else bg-danger
                                @endif fs-6">
                                {{ number_format($stat['moyenne'], 2) }}/20
                            </span>
                        </td>
                        <td class="text-center">
                            @if($stat['rang'] == 1)
                                <span class="badge bg-warning text-dark">
                                    <i class="fas fa-crown me-1"></i>{{ $stat['rang'] }}er
                                </span>
                            @elseif($stat['rang'] == 2)
                                <span class="badge bg-secondary">
                                    <i class="fas fa-medal me-1"></i>{{ $stat['rang'] }}ème
                                </span>
                            @elseif($stat['rang'] == 3)
                                <span class="badge bg-warning">
                                    <i class="fas fa-award me-1"></i>{{ $stat['rang'] }}ème
                                </span>
                            @else
                                <span class="badge bg-light text-dark">{{ $stat['rang'] }}ème</span>
                            @endif
                        </td>
                        <td>
                            @if($stat['moyenne'] >= 16)
                                <span class="text-success"><i class="fas fa-star me-1"></i>Excellent</span>
                            @elseif($stat['moyenne'] >= 14)
                                <span class="text-primary"><i class="fas fa-thumbs-up me-1"></i>Très bien</span>
                            @elseif($stat['moyenne'] >= 12)
                                <span class="text-info"><i class="fas fa-check me-1"></i>Bien</span>
                            @elseif($stat['moyenne'] >= 10)
                                <span class="text-warning"><i class="fas fa-exclamation me-1"></i>Assez bien</span>
                            @else
                                <span class="text-danger"><i class="fas fa-times me-1"></i>Insuffisant</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('notes.eleve', $stat['eleve']->id) }}" 
                                   class="btn btn-outline-info" 
                                   title="Voir le bulletin">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('notes.saisir', $classe->id) }}" 
                                   class="btn btn-outline-primary" 
                                   title="Saisir des notes">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center text-muted py-5">
            <i class="fas fa-chart-bar fa-3x mb-3"></i>
            <h5>Aucune note trouvée</h5>
            <p>Il n'y a pas encore de notes saisies pour cette période.</p>
            <a href="{{ route('notes.saisir', $classe->id) }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>
                Saisir des notes
            </a>
        </div>
        @endif
    </div>
</div>

<!-- Graphique des moyennes (optionnel) -->
@if($statistiques->count() > 0)
<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-chart-pie me-2"></i>
            Répartition des moyennes
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3 text-center">
                <div class="border rounded p-3">
                    <h4 class="text-success">{{ $statistiques->where('moyenne', '>=', 16)->count() }}</h4>
                    <small>Excellent (≥16)</small>
                </div>
            </div>
            <div class="col-md-3 text-center">
                <div class="border rounded p-3">
                    <h4 class="text-primary">{{ $statistiques->whereBetween('moyenne', [14, 15.99])->count() }}</h4>
                    <small>Très bien (14-16)</small>
                </div>
            </div>
            <div class="col-md-3 text-center">
                <div class="border rounded p-3">
                    <h4 class="text-info">{{ $statistiques->whereBetween('moyenne', [12, 13.99])->count() }}</h4>
                    <small>Bien (12-14)</small>
                </div>
            </div>
            <div class="col-md-3 text-center">
                <div class="border rounded p-3">
                    <h4 class="text-warning">{{ $statistiques->whereBetween('moyenne', [10, 11.99])->count() }}</h4>
                    <small>Assez bien (10-12)</small>
                </div>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-6 text-center">
                <div class="border rounded p-3">
                    <h4 class="text-danger">{{ $statistiques->where('moyenne', '<', 10)->count() }}</h4>
                    <small>Insuffisant (<10)</small>
                </div>
            </div>
            <div class="col-md-6 text-center">
                <div class="border rounded p-3">
                    <h4 class="text-success">{{ $statistiques->where('moyenne', '>=', 10)->count() }}</h4>
                    <small>Admis (≥10)</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@push('styles')
<style>
.badge.fs-6 {
    font-size: 0.875rem !important;
}
</style>
@endpush
@endsection
