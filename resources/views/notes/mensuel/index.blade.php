@extends('layouts.app')

@section('title', 'Tests Mensuels')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-calendar-alt me-2"></i>
        Tests Mensuels
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
                    Sélectionner une classe
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
                                    {{ $classe->effectif_actuel }} élèves
                                </p>
                                <div class="mt-auto">
                                    <div class="btn-group w-100" role="group">
                                        @if(!auth()->user()->isTeacher())
                                            <a href="{{ route('notes.mensuel.classe', $classe->id) }}" 
                                               class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-eye me-1"></i>
                                                Voir
                                            </a>
                                        @endif
                                        <a href="{{ route('notes.mensuel.saisir', $classe->id) }}" 
                                           class="btn btn-outline-success btn-sm">
                                            <i class="fas fa-plus me-1"></i>
                                            Saisir
                                        </a>
                                        @if(!auth()->user()->isTeacher())
                                            <a href="{{ route('notes.mensuel.modifier', $classe->id) }}" 
                                               class="btn btn-outline-warning btn-sm">
                                                <i class="fas fa-edit me-1"></i>
                                                Modifier
                                            </a>
                                            <a href="{{ route('notes.mensuel.resultats', $classe->id) }}" 
                                               class="btn btn-outline-info btn-sm">
                                                <i class="fas fa-chart-line me-1"></i>
                                                Résultats
                                            </a>
                                        @endif
                                    </div>
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
                    <p class="text-muted">Vous n'avez accès à aucune classe pour les tests mensuels.</p>
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
                    Informations sur les tests mensuels
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="fas fa-check-circle text-success me-2"></i>Caractéristiques :</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>Une seule note par matière par mois</li>
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>Note sur 20 avec coefficient</li>
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>Calcul automatique de la moyenne</li>
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>Classement des élèves</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="fas fa-print text-info me-2"></i>Fonctionnalités :</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>Fiche de résultats imprimable</li>
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>Format portrait pour l'impression</li>
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>Matricule, nom, prénom, moyenne et rang</li>
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>Pas de bulletin détaillé</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
