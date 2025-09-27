@extends('layouts.app')

@section('title', 'Saisir Absences')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-user-times me-2"></i>
        Saisir Absences
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('teacher.mes-eleves') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Retour
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

<!-- Sélection de classe -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Sélectionner une classe</h5>
    </div>
    <div class="card-body">
        <div class="row">
            @foreach($classes as $classe)
            <div class="col-md-4 mb-3">
                <div class="card border-primary">
                    <div class="card-body text-center">
                        <i class="fas fa-users fa-2x text-primary mb-3"></i>
                        <h5>{{ $classe->nom }}</h5>
                        <p class="text-muted">
                            {{ $classe->eleves->count() }} élèves
                            <br>
                            <small>Niveau: {{ $classe->niveau }}</small>
                        </p>
                        <a href="{{ route('teacher.absences.classe', $classe->id) }}" 
                           class="btn btn-primary btn-sm">
                            <i class="fas fa-user-times me-1"></i>
                            Saisir Absences
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        
        @if($classes->isEmpty())
        <div class="text-center text-muted">
            <i class="fas fa-info-circle fa-2x mb-3"></i>
            <p>Aucune classe disponible. Vous n'êtes pas encore assigné à des classes.</p>
        </div>
        @endif
    </div>
</div>

<!-- Statistiques des absences récentes -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Statistiques des absences récentes</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <div class="text-center">
                    <h4 class="text-warning">0</h4>
                    <small>Absences aujourd'hui</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center">
                    <h4 class="text-danger">0</h4>
                    <small>Absences non justifiées</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center">
                    <h4 class="text-success">0</h4>
                    <small>Présents aujourd'hui</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center">
                    <h4 class="text-info">0</h4>
                    <small>Total élèves</small>
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