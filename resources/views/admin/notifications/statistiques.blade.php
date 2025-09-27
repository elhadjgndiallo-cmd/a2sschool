@extends('layouts.app')

@section('title', 'Statistiques des Messages')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center pt-3 pb-2 mb-3 border-bottom gap-2">
    <div>
        <h1 class="h2 mb-0">
            <i class="fas fa-chart-bar text-primary me-2"></i>
            Statistiques des Messages
        </h1>
        <p class="text-muted mb-0">Analyse des communications avec les parents</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('admin.notifications.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                <span class="d-none d-sm-inline">Retour aux Messages</span>
            </a>
        </div>
    </div>
</div>

<!-- Statistiques générales -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0">{{ $stats['total_messages'] }}</h4>
                        <p class="mb-0">Total Messages</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-envelope fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0">{{ $stats['messages_non_lus'] }}</h4>
                        <p class="mb-0">Non Lus</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-envelope-open fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0">{{ $stats['messages_par_type']->sum() }}</h4>
                        <p class="mb-0">Types Différents</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-tags fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0">{{ $stats['messages_par_priorite']->sum() }}</h4>
                        <p class="mb-0">Priorités</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-flag fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Messages par type -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-chart-pie me-2"></i>
                    Messages par Type
                </h6>
            </div>
            <div class="card-body">
                @if($stats['messages_par_type']->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Nombre</th>
                                    <th>Pourcentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stats['messages_par_type'] as $type => $count)
                                    <tr>
                                        <td>
                                            <span class="badge bg-{{ $type == 'question' ? 'info' : ($type == 'demande' ? 'primary' : ($type == 'plainte' ? 'danger' : 'secondary')) }}">
                                                {{ ucfirst($type) }}
                                            </span>
                                        </td>
                                        <td>{{ $count }}</td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-{{ $type == 'question' ? 'info' : ($type == 'demande' ? 'primary' : ($type == 'plainte' ? 'danger' : 'secondary')) }}" 
                                                     style="width: {{ $stats['total_messages'] > 0 ? ($count / $stats['total_messages']) * 100 : 0 }}%">
                                                    {{ $stats['total_messages'] > 0 ? round(($count / $stats['total_messages']) * 100, 1) : 0 }}%
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-3">
                        <i class="fas fa-chart-pie fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">Aucune donnée disponible</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Messages par priorité -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-flag me-2"></i>
                    Messages par Priorité
                </h6>
            </div>
            <div class="card-body">
                @if($stats['messages_par_priorite']->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Priorité</th>
                                    <th>Nombre</th>
                                    <th>Pourcentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stats['messages_par_priorite'] as $priorite => $count)
                                    <tr>
                                        <td>
                                            <span class="badge bg-{{ $priorite == 'urgente' ? 'danger' : ($priorite == 'haute' ? 'warning' : ($priorite == 'moyenne' ? 'info' : 'secondary')) }}">
                                                {{ ucfirst($priorite) }}
                                            </span>
                                        </td>
                                        <td>{{ $count }}</td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-{{ $priorite == 'urgente' ? 'danger' : ($priorite == 'haute' ? 'warning' : ($priorite == 'moyenne' ? 'info' : 'secondary')) }}" 
                                                     style="width: {{ $stats['total_messages'] > 0 ? ($count / $stats['total_messages']) * 100 : 0 }}%">
                                                    {{ $stats['total_messages'] > 0 ? round(($count / $stats['total_messages']) * 100, 1) : 0 }}%
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-3">
                        <i class="fas fa-flag fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">Aucune donnée disponible</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Graphique des tendances (placeholder) -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-chart-line me-2"></i>
                    Tendance des Messages (Derniers 30 jours)
                </h6>
            </div>
            <div class="card-body">
                <div class="text-center py-5">
                    <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Graphique en cours de développement</h5>
                    <p class="text-muted">Les graphiques de tendances seront bientôt disponibles.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
