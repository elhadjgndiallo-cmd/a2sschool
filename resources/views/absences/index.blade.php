@extends('layouts.app')

@section('title', 'Gestion des Absences')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-user-times me-2"></i>
        Gestion des Absences
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('absences.statistiques') }}" class="btn btn-outline-info">
            <i class="fas fa-chart-bar me-1"></i>
            Statistiques
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Sélectionner une classe pour saisir les absences</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($classes as $classe)
                    <div class="col-md-4 mb-3">
                        <div class="card border-warning">
                            <div class="card-body text-center">
                                <i class="fas fa-users fa-2x text-warning mb-3"></i>
                                <h5>{{ $classe->nom }}</h5>
                                <p class="text-muted">
                                    {{ $classe->eleves->count() }} élèves
                                    <br>
                                    <small>Niveau: {{ $classe->niveau }}</small>
                                </p>
                                <div class="btn-group-vertical w-100">
                                    <a href="{{ route('absences.saisir', $classe->id) }}" 
                                       class="btn btn-warning btn-sm">
                                        <i class="fas fa-user-times me-1"></i>
                                        Saisir Absences
                                    </a>
                                    <a href="{{ route('absences.rapport', $classe->id) }}" 
                                       class="btn btn-outline-info btn-sm">
                                        <i class="fas fa-file-alt me-1"></i>
                                        Rapport
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                @if($classes->isEmpty())
                <div class="text-center text-muted">
                    <i class="fas fa-info-circle fa-2x mb-3"></i>
                    <p>Aucune classe disponible. Veuillez d'abord créer des classes.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Résumé du jour -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Résumé d'aujourd'hui</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h3 class="text-danger">0</h3>
                                <small>Absences</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h3 class="text-warning">0</h3>
                                <small>Retards</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h3 class="text-info">0</h3>
                                <small>Sorties anticipées</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h3 class="text-success">0</h3>
                                <small>Justifiées</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.btn-block { width: 100%; }
</style>
@endpush
@endsection
