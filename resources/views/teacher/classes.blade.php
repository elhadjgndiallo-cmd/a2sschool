@extends('layouts.app')

@section('title', 'Mes Classes')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-chalkboard-teacher me-2"></i>
        Mes Classes
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

<!-- Statistiques -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <i class="fas fa-chalkboard-teacher fa-2x mb-2"></i>
                <h4>{{ $classes->count() }}</h4>
                <p class="mb-0">Classes assignées</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <i class="fas fa-users fa-2x mb-2"></i>
                <h4>{{ $classes->sum(function($classe) { return $classe->eleves->count(); }) }}</h4>
                <p class="mb-0">Total élèves</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <i class="fas fa-book fa-2x mb-2"></i>
                <h4>{{ auth()->user()->enseignant->matieres->count() }}</h4>
                <p class="mb-0">Matières enseignées</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body text-center">
                <i class="fas fa-calendar fa-2x mb-2"></i>
                <h4>{{ now()->format('d/m/Y') }}</h4>
                <p class="mb-0">Aujourd'hui</p>
            </div>
        </div>
    </div>
</div>

<!-- Liste des classes -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-list me-2"></i>
            Mes classes assignées ({{ $classes->count() }} classes)
        </h5>
    </div>
    <div class="card-body">
        @if($classes->count() > 0)
            <div class="row">
                @foreach($classes as $classe)
                <div class="col-md-4 mb-4">
                    <div class="card border-primary h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-users fa-3x text-primary mb-3"></i>
                            <h5 class="card-title">{{ $classe->nom }}</h5>
                            <p class="text-muted">
                                <strong>{{ $classe->eleves->count() }}</strong> élèves
                                <br>
                                <small>Niveau: {{ $classe->niveau }}</small>
                                <br>
                                <small>Effectif: {{ $classe->effectif_max ?? 'Non défini' }}</small>
                            </p>
                            
                            <!-- Matières enseignées dans cette classe -->
                            <div class="mb-3">
                                <small class="text-muted">Matières enseignées :</small>
                                <div class="mt-1">
                                    @php
                                        $matieresClasse = $classe->emploisTemps()
                                            ->where('enseignant_id', auth()->user()->enseignant->id)
                                            ->with('matiere')
                                            ->get()
                                            ->pluck('matiere.nom')
                                            ->unique()
                                            ->take(3);
                                    @endphp
                                    @foreach($matieresClasse as $matiere)
                                        <span class="badge bg-secondary me-1 mb-1">{{ $matiere }}</span>
                                    @endforeach
                                    @if($matieresClasse->count() > 3)
                                        <span class="badge bg-light text-dark">+{{ $matieresClasse->count() - 3 }} autres</span>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Actions -->
                            <div class="btn-group-vertical w-100" role="group">
                                <a href="{{ route('teacher.eleves-classe', $classe->id) }}" 
                                   class="btn btn-outline-primary btn-sm mb-2">
                                    <i class="fas fa-users me-1"></i>
                                    Voir les élèves
                                </a>
                                <a href="{{ route('teacher.absences.classe', $classe->id) }}" 
                                   class="btn btn-outline-warning btn-sm mb-2">
                                    <i class="fas fa-user-times me-1"></i>
                                    Saisir absences
                                </a>
                                <a href="{{ route('teacher.notes.classe', $classe->id) }}" 
                                   class="btn btn-outline-success btn-sm mb-2">
                                    <i class="fas fa-edit me-1"></i>
                                    Saisir notes
                                </a>
                                <a href="{{ route('teacher.emploi-temps') }}" 
                                   class="btn btn-outline-info btn-sm">
                                    <i class="fas fa-calendar me-1"></i>
                                    Emploi du temps
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="text-center text-muted py-5">
                <i class="fas fa-chalkboard-teacher fa-3x mb-3"></i>
                <h5>Aucune classe assignée</h5>
                <p>Vous n'êtes pas encore assigné à des classes. Contactez l'administrateur.</p>
            </div>
        @endif
    </div>
</div>

@push('styles')
<style>
.btn-group-vertical .btn {
    margin-bottom: 0.25rem;
}
.btn-group-vertical .btn:last-child {
    margin-bottom: 0;
}
</style>
@endpush
@endsection