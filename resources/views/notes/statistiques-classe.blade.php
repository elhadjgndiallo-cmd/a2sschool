@extends('layouts.app')

@section('title', 'Statistiques des Notes - ' . $classe->nom)

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-chart-bar me-2"></i>
        Statistiques des Notes - {{ $classe->nom }}
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('notes.statistiques') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Retour
        </a>
    </div>
</div>

<!-- Sélection de la période -->
<form method="GET" action="{{ route('notes.statistiques.classe', $classe->id) }}" class="mb-3">
    <div class="row g-2">
        @if(isset($anneeScolaireActive))
        <div class="col-12 col-sm-6 col-md-4">
            <input type="text" class="form-control" value="{{ $anneeScolaireActive->nom }}" readonly title="Année scolaire">
        </div>
        @endif
        <div class="col-12 col-sm-6 col-md-4">
            <select class="form-select" id="periode" name="periode" onchange="this.form.submit()" title="Période">
                @foreach(\App\Helpers\PeriodeHelper::options($classe ?? null) as $code => $libelle)
                    <option value="{{ $code }}" {{ $periode == $code ? 'selected' : '' }}>{{ $libelle }}</option>
                @endforeach
            </select>
        </div>
    </div>
</form>

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
            Classement des élèves - {{ \App\Helpers\PeriodeHelper::libelle($periode, $classe ?? null) }}
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
                        <th width="15%">Mention</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($statistiques as $stat)
                    <tr class="table-row-clickable" data-href="{{ route('notes.eleve', $stat['eleve']->id) }}?periode={{ urlencode($periode) }}" role="button" tabindex="0">
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
                            @php
                                $appreciation = $classe->getAppreciation($stat['moyenne']);
                            @endphp
                            <span class="badge bg-{{ $appreciation['color'] }} fs-6">
                                {{ number_format($stat['moyenne'], 2) }}/{{ $classe->note_max }}
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
                            @php
                                $appreciation = $classe->getAppreciation($stat['moyenne']);
                            @endphp
                            <span class="text-{{ $appreciation['color'] }}">
                                @if($appreciation['label'] == 'Excellent')
                                    <i class="fas fa-star me-1"></i>
                                @elseif($appreciation['label'] == 'Très bien')
                                    <i class="fas fa-thumbs-up me-1"></i>
                                @elseif($appreciation['label'] == 'Bien')
                                    <i class="fas fa-check me-1"></i>
                                @elseif($appreciation['label'] == 'Assez bien')
                                    <i class="fas fa-exclamation me-1"></i>
                                @elseif($appreciation['label'] == 'Passable')
                                    <i class="fas fa-minus me-1"></i>
                                @else
                                    <i class="fas fa-times me-1"></i>
                                @endif
                                {{ $appreciation['label'] }}
                            </span>
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
        @php
            $seuils = $classe->seuils_appreciation;
        @endphp
        
        <div class="row">
            @php
                // Déterminer les clés selon le niveau
                if ($classe->isPrimaire()) {
                    $keys = ['tres_bien', 'bien', 'assez_bien', 'passable'];
                } else {
                    $keys = ['excellent', 'tres_bien', 'bien', 'assez_bien'];
                }
            @endphp
            @foreach($keys as $key)
                @php
                    if (isset($seuils[$key])) {
                        $seuil = $seuils[$key];
                        $count = $statistiques->whereBetween('moyenne', [$seuil['min'], $seuil['max']])->count();
                    } else {
                        continue;
                    }
                @endphp
                <div class="col-md-3 text-center">
                    <div class="border rounded p-3">
                        <h4 class="text-{{ $seuil['color'] }}">{{ $count }}</h4>
                        <small>{{ $seuil['label'] }} ({{ $seuil['min'] }}-{{ $seuil['max'] }})</small>
                    </div>
                </div>
            @endforeach
        </div>
        
        <div class="row mt-3">
            @php
                if (isset($seuils['passable'])) {
                    $seuilPassable = $seuils['passable'];
                    $countPassable = $statistiques->whereBetween('moyenne', [$seuilPassable['min'], $seuilPassable['max']])->count();
                } else {
                    $countPassable = 0;
                }
                
                if (isset($seuils['insuffisant'])) {
                    $seuilInsuffisant = $seuils['insuffisant'];
                    $countInsuffisant = $statistiques->whereBetween('moyenne', [$seuilInsuffisant['min'], $seuilInsuffisant['max']])->count();
                } else {
                    $countInsuffisant = 0;
                }
                
                // Pour primaire, ajouter les autres catégories
                if ($classe->isPrimaire()) {
                    $countMal = isset($seuils['mal']) ? $statistiques->whereBetween('moyenne', [$seuils['mal']['min'], $seuils['mal']['max']])->count() : 0;
                    $countMediocre = isset($seuils['mediocre']) ? $statistiques->whereBetween('moyenne', [$seuils['mediocre']['min'], $seuils['mediocre']['max']])->count() : 0;
                }
                
                $countAdmis = $statistiques->where('moyenne', '>=', $classe->seuil_reussite)->count();
            @endphp
            
            @if(isset($seuilPassable))
            <div class="col-md-{{ $classe->isPrimaire() ? '3' : '4' }} text-center">
                <div class="border rounded p-3">
                    <h4 class="text-{{ $seuilPassable['color'] }}">{{ $countPassable }}</h4>
                    <small>{{ $seuilPassable['label'] }} ({{ $seuilPassable['min'] }}-{{ $seuilPassable['max'] }})</small>
                </div>
            </div>
            @endif
            @if(isset($seuilInsuffisant))
            <div class="col-md-{{ $classe->isPrimaire() ? '3' : '4' }} text-center">
                <div class="border rounded p-3">
                    <h4 class="text-{{ $seuilInsuffisant['color'] }}">{{ $countInsuffisant }}</h4>
                    <small>{{ $seuilInsuffisant['label'] }} ({{ $seuilInsuffisant['min'] }}-{{ $seuilInsuffisant['max'] }})</small>
                </div>
            </div>
            @endif
            @if($classe->isPrimaire() && isset($seuils['mal']))
            <div class="col-md-3 text-center">
                <div class="border rounded p-3">
                    <h4 class="text-{{ $seuils['mal']['color'] }}">{{ $countMal }}</h4>
                    <small>{{ $seuils['mal']['label'] }} ({{ $seuils['mal']['min'] }}-{{ $seuils['mal']['max'] }})</small>
                </div>
            </div>
            @endif
            @if($classe->isPrimaire() && isset($seuils['mediocre']))
            <div class="col-md-3 text-center">
                <div class="border rounded p-3">
                    <h4 class="text-{{ $seuils['mediocre']['color'] }}">{{ $countMediocre }}</h4>
                    <small>{{ $seuils['mediocre']['label'] }} ({{ $seuils['mediocre']['min'] }}-{{ $seuils['mediocre']['max'] }})</small>
                </div>
            </div>
            @endif
            <div class="col-md-{{ $classe->isPrimaire() ? '3' : '4' }} text-center">
                <div class="border rounded p-3">
                    <h4 class="text-success">{{ $countAdmis }}</h4>
                    <small>Admis (≥{{ $classe->seuil_reussite }})</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="card mb-4 no-print">
    <div class="card-header bg-dark text-white">
        <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Actions</h5>
    </div>
    <div class="card-body">
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('notes.statistiques.classe.imprimer', $classe->id) }}?periode={{ $periode }}"
               class="btn btn-success" target="_blank">
                <i class="fas fa-print me-1"></i> Imprimer
            </a>
            <a href="{{ route('notes.saisir', $classe->id) }}" class="btn btn-primary">
                <i class="fas fa-edit me-1"></i> Saisir des notes
            </a>
        </div>
    </div>
</div>

@push('styles')
<style>
.badge.fs-6 {
    font-size: 0.875rem !important;
}
</style>
@endpush
@endsection
