@extends('layouts.app')

@section('title', 'Résultats Annuels')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-trophy me-2"></i>
        Résultats Annuels
    </h1>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="fas fa-chalkboard-teacher me-2"></i>
                    Sélectionner une classe - {{ $anneeScolaireActive->nom }}
                </h5>
            </div>
            <div class="card-body">
                @if($classes->count() > 0)
                <div class="row">
                    @foreach($classes as $classe)
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card h-100 border">
                            <div class="card-body d-flex flex-column">
                                <h6 class="card-title">
                                    <i class="fas fa-school me-2"></i>
                                    {{ $classe->nom }}
                                </h6>
                                <p class="card-text text-muted small">
                                    <i class="fas fa-users me-1"></i>
                                    {{ $classe->eleves->count() }} élèves
                                </p>
                                <div class="mt-auto">
                                    <a href="{{ route('notes.annuel.resultats', $classe->id) }}" 
                                       class="btn btn-primary w-100">
                                        <i class="fas fa-chart-line me-1"></i>
                                        Voir les résultats
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-5">
                    <i class="fas fa-chalkboard-teacher fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Aucune classe disponible</h5>
                    <p class="text-muted">Vous n'avez accès à aucune classe pour les résultats annuels.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Informations sur les résultats annuels
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="fas fa-check-circle text-success me-2"></i>Contenu des résultats :</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>Matricule de l'élève</li>
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>Nom et prénom complets</li>
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>Rang {{ \App\Helpers\PeriodeHelper::libelle('trimestre1') }}</li>
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>Rang {{ \App\Helpers\PeriodeHelper::libelle('trimestre2') }}</li>
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>Rang Annuel</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="fas fa-print text-info me-2"></i>Fonctionnalités :</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>Vue synthétique des classements</li>
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>Impression du détail des notes par matière</li>
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>Note annuelle moyenne par matière</li>
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>Export PDF disponible</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
