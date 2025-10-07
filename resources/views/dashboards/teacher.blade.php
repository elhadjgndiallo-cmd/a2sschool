@extends('layouts.app')

@section('title', 'Dashboard Enseignant')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center pt-3 pb-2 mb-3 border-bottom gap-2">
    <h1 class="h2 mb-0">Dashboard Enseignant</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('teacher.classes') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-chalkboard-teacher me-1"></i>
                <span class="d-none d-sm-inline">Mes Classes</span>
                <span class="d-sm-none">Classes</span>
            </a>
            <a href="{{ route('teacher.emploi-temps') }}" class="btn btn-sm btn-info">
                <i class="fas fa-calendar-alt me-1"></i>
                <span class="d-none d-sm-inline">Emploi du Temps</span>
                <span class="d-sm-none">EDT</span>
            </a>
        </div>
    </div>
</div>

<!-- Informations enseignant -->
<div class="row mb-4 g-3">
    <div class="col-12 col-md-4">
        <div class="card">
            <div class="card-body text-center">
                @if($enseignant->utilisateur->photo_profil)
                    <img src="{{ Storage::url($enseignant->utilisateur->photo_profil) }}" 
                         alt="Photo de {{ $enseignant->utilisateur->prenom }} {{ $enseignant->utilisateur->nom }}" 
                         class="rounded-circle mb-3" 
                         style="width: 100px; height: 100px; object-fit: cover;">
                @else
                    <i class="fas fa-user-tie fa-3x text-primary mb-3"></i>
                @endif
                <h5>{{ $enseignant->utilisateur->prenom }} {{ $enseignant->utilisateur->nom }}</h5>
                <p class="text-muted">{{ $enseignant->specialite ?? 'Enseignant' }}</p>
                <span class="badge bg-success">{{ ucfirst($enseignant->statut) }}</span>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-md-8">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Emploi du temps d'aujourd'hui - {{ now()->locale('fr')->isoFormat('dddd D MMMM YYYY') }}</h6>
            </div>
            <div class="card-body">
                @if($emploisDuJour->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Heure</th>
                                    <th>Classe</th>
                                    <th>Matière</th>
                                    <th>Salle</th>
                                    <th>Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($emploisDuJour as $emploi)
                                <tr>
                                    <td>{{ $emploi->heure_debut->format('H:i') }} - {{ $emploi->heure_fin->format('H:i') }}</td>
                                    <td>{{ $emploi->classe->nom }}</td>
                                    <td>{{ $emploi->matiere->nom }}</td>
                                    <td>{{ $emploi->salle ?? 'Non définie' }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ ucfirst($emploi->type_cours) }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center text-muted">
                        <i class="fas fa-calendar-times fa-2x mb-3"></i>
                        <p>Aucun cours programmé aujourd'hui</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Événements à venir -->
@if($evenements->count() > 0)
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="fas fa-calendar-check me-2"></i>
                    Événements à venir
                </h6>
                <a href="{{ route('evenements.index') }}" class="btn btn-sm btn-outline-primary">
                    Voir tous les événements
                </a>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($evenements as $evenement)
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card border-left-primary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="card-title mb-0">{{ $evenement->titre }}</h6>
                                    <span class="badge" style="background-color: {{ $evenement->couleur ?? '#3788d8' }}">
                                        {{ ucfirst($evenement->type) }}
                                    </span>
                                </div>
                                @if($evenement->description)
                                    <p class="card-text text-muted small">{{ Str::limit($evenement->description, 100) }}</p>
                                @endif
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            {{ \Carbon\Carbon::parse($evenement->date_debut)->format('d/m/Y') }}
                                        </small>
                                        @if($evenement->heure_debut)
                                            <br>
                                            <small class="text-muted">
                                                <i class="fas fa-clock me-1"></i>
                                                {{ \Carbon\Carbon::parse($evenement->heure_debut)->format('H:i') }}
                                            </small>
                                        @endif
                                        @if($evenement->lieu)
                                            <br>
                                            <small class="text-info">
                                                <i class="fas fa-map-marker-alt me-1"></i>
                                                {{ $evenement->lieu }}
                                            </small>
                                        @endif
                                    </div>
                                    @if($evenement->classe)
                                        <span class="badge bg-secondary">{{ $evenement->classe->nom }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Actions rapides -->
<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Actions Rapides</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <a href="{{ route('teacher.mes-eleves') }}" class="btn btn-info btn-block">
                            <i class="fas fa-users me-2"></i>
                            Voir mes élèves
                        </a>
                    </div>
                    <div class="col-md-6 mb-3">
                        <a href="{{ route('teacher.emploi-temps') }}" class="btn btn-info btn-block">
                            <i class="fas fa-calendar me-2"></i>
                            Emploi du Temps
                        </a>
                    </div>
                    <div class="col-md-6 mb-3">
                        <a href="{{ route('teacher.classes') }}" class="btn btn-success btn-block">
                            <i class="fas fa-users me-2"></i>
                            Mes Classes
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Notifications</h6>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-bell text-info me-2"></i>
                            Réunion pédagogique demain
                        </div>
                        <small class="text-muted">14h00</small>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-clipboard-check text-success me-2"></i>
                            Notes à saisir - Classe 6ème A
                        </div>
                        <small class="text-muted">Urgent</small>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                            Absence non justifiée signalée
                        </div>
                        <small class="text-muted">Hier</small>
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
