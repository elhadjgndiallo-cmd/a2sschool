@extends('layouts.app')

@section('title', 'Année Scolaire - ' . $anneesScolaire->nom)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="fas fa-calendar-alt text-primary me-2"></i>
            {{ $anneesScolaire->nom }}
            @if($anneesScolaire->active)
                <span class="badge bg-success ms-2">ACTIVE</span>
            @endif
        </h1>
        <a href="{{ route('annees-scolaires.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Retour
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informations</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Date de début</strong><br>
                            <i class="fas fa-calendar-check text-success me-1"></i>
                            {{ $anneesScolaire->date_debut->format('d/m/Y') }}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Date de fin</strong><br>
                            <i class="fas fa-calendar-times text-danger me-1"></i>
                            {{ $anneesScolaire->date_fin->format('d/m/Y') }}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Statut calendaire</strong><br>
                            @php
                                $statut = $anneesScolaire->statut;
                                $badgeClass = match($statut) {
                                    'en_cours' => 'bg-primary',
                                    'à_venir' => 'bg-info',
                                    'terminee' => 'bg-secondary',
                                    default => 'bg-light text-dark'
                                };
                                $statutText = match($statut) {
                                    'en_cours' => 'En cours',
                                    'à_venir' => 'À venir',
                                    'terminee' => 'Terminée',
                                    default => 'Indéterminé'
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ $statutText }}</span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>État système</strong><br>
                            @if($anneesScolaire->active)
                                <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Année active</span>
                            @else
                                <span class="badge bg-secondary"><i class="fas fa-pause-circle me-1"></i>Inactive</span>
                            @endif
                        </div>
                        @if($anneesScolaire->description)
                        <div class="col-12">
                            <strong>Description</strong>
                            <p class="text-muted mb-0 mt-1">{{ $anneesScolaire->description }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Statistiques</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2 d-flex justify-content-between">
                        <span>Élèves inscrits</span>
                        <strong>{{ $stats['eleves'] }}</strong>
                    </p>
                    <p class="mb-2 d-flex justify-content-between">
                        <span>Élèves actifs</span>
                        <strong class="text-success">{{ $stats['eleves_actifs'] }}</strong>
                    </p>
                    <p class="mb-2 d-flex justify-content-between">
                        <span>Enseignants</span>
                        <strong>{{ $stats['enseignants'] }}</strong>
                    </p>
                    <p class="mb-0 d-flex justify-content-between">
                        <span>Factures</span>
                        <strong>{{ $stats['factures'] }}</strong>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4 no-print">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Actions</h5>
        </div>
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2">
                @if(auth()->user()->hasPermission('annees_scolaires.edit'))
                <a href="{{ route('annees-scolaires.edit', $anneesScolaire) }}" class="btn btn-warning">
                    <i class="fas fa-edit me-1"></i> Modifier
                </a>
                @endif

                @if(!$anneesScolaire->active && auth()->user()->hasPermission('annees_scolaires.edit'))
                <form method="POST" action="{{ route('annees-scolaires.activer', $anneesScolaire) }}" class="d-inline"
                      onsubmit="return confirm('Activer l\'année scolaire {{ $anneesScolaire->nom }} ?\n\nL\'année actuellement active sera désactivée.')">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-1"></i> Activer cette année
                    </button>
                </form>
                @endif

                @if(!$anneesScolaire->active && auth()->user()->hasPermission('annees_scolaires.delete'))
                <form method="POST" action="{{ route('annees-scolaires.destroy', $anneesScolaire) }}" class="d-inline"
                      onsubmit="return confirm('Supprimer définitivement l\'année scolaire {{ $anneesScolaire->nom }} ?\n\nCette action est irréversible.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="fas fa-trash me-1"></i> Supprimer
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
