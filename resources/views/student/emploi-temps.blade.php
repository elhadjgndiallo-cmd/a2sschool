@php use Illuminate\Support\Facades\Storage; @endphp
@extends('layouts.app')

@section('title', 'Mon Emploi du Temps')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-calendar-alt me-2"></i>
        Mon Emploi du Temps
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Retour au Dashboard
        </a>
    </div>
</div>

<!-- Informations élève -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">
                    @php
                        $hasPhoto = false;
                        if ($eleve->utilisateur && $eleve->utilisateur->photo_profil && Storage::disk('public')->exists($eleve->utilisateur->photo_profil)) {
                            $hasPhoto = true;
                        }
                    @endphp
                    @if($hasPhoto)
                        <img src="{{ asset('storage/' . $eleve->utilisateur->photo_profil) }}" 
                             alt="Photo de {{ $eleve->nom_complet }}" 
                             class="rounded-circle me-2" 
                             style="width: 24px; height: 24px; object-fit: cover;">
                    @else
                        <i class="fas fa-user-graduate me-2"></i>
                    @endif
                    Informations
                </h5>
                <p><strong>Nom :</strong> {{ $eleve->nom_complet }}</p>
                <p><strong>Matricule :</strong> {{ $eleve->numero_etudiant }}</p>
                @if($eleve->classe)
                    <p><strong>Classe :</strong> <span class="badge bg-primary">{{ $eleve->classe->nom }}</span></p>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="fas fa-info-circle me-2"></i>
                    Statistiques
                </h5>
                <p><strong>Total cours :</strong> {{ $emploisTemps->count() }}</p>
                <p><strong>Matières :</strong> {{ $emploisTemps->pluck('matiere.nom')->unique()->count() }}</p>
                <p><strong>Enseignants :</strong> {{ $emploisTemps->pluck('enseignant.utilisateur.name')->unique()->count() }}</p>
            </div>
        </div>
    </div>
</div>

@if($emploisTemps->count() > 0)
    @php
        $jours = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
        $emploisParJour = $emploisTemps->groupBy('jour_semaine');
    @endphp
    
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-calendar-week me-2"></i>
                Emploi du temps hebdomadaire
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th width="15%">Heure</th>
                            @foreach($jours as $jour)
                            <th class="text-center">{{ ucfirst($jour) }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $heures = [];
                            foreach($emploisTemps as $emploi) {
                                $heure = $emploi->heure_debut->format('H:i');
                                if (!in_array($heure, $heures)) {
                                    $heures[] = $heure;
                                }
                            }
                            sort($heures);
                        @endphp
                        
                        @foreach($heures as $heure)
                        <tr>
                            <td class="fw-bold">{{ $heure }}</td>
                            @foreach($jours as $jour)
                            <td class="text-center">
                                @php
                                    $emploisDuJour = $emploisParJour->get($jour, collect())
                                        ->filter(function($emploi) use ($heure) {
                                            return $emploi->heure_debut->format('H:i') === $heure;
                                        });
                                @endphp
                                
                                @if($emploisDuJour->count() > 0)
                                    @foreach($emploisDuJour as $emploi)
                                    <div class="card mb-2">
                                        <div class="card-body p-2">
                                            <div class="fw-bold text-primary">{{ $emploi->matiere->nom }}</div>
                                            <div class="text-muted small">{{ $emploi->enseignant->utilisateur->name }}</div>
                                            <div class="text-muted small">
                                                <i class="fas fa-clock me-1"></i>
                                                {{ $emploi->heure_debut->format('H:i') }} - {{ $emploi->heure_fin->format('H:i') }}
                                            </div>
                                            @if($emploi->salle)
                                            <div class="text-muted small">
                                                <i class="fas fa-door-open me-1"></i>
                                                {{ $emploi->salle }}
                                            </div>
                                            @endif
                                            <div class="mt-1">
                                                <span class="badge bg-{{ $emploi->type_cours == 'cours' ? 'primary' : 'secondary' }}">
                                                    {{ ucfirst($emploi->type_cours) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@else
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        <strong>Aucun emploi du temps trouvé.</strong> 
        Votre classe n'a pas encore d'emploi du temps configuré.
    </div>
@endif

<!-- Actions -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <a href="{{ route('student.notes') }}" class="btn btn-success btn-block">
                            <i class="fas fa-chart-line me-2"></i>
                            Mes Notes
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('student.absences') }}" class="btn btn-warning btn-block">
                            <i class="fas fa-clock me-2"></i>
                            Mes Absences
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('student.bulletin') }}" class="btn btn-info btn-block">
                            <i class="fas fa-file-alt me-2"></i>
                            Mon Bulletin
                        </a>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-primary btn-block" onclick="window.print()">
                            <i class="fas fa-print me-2"></i>
                            Imprimer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.btn-block { width: 100%; }
.card .card-body {
    font-size: 0.9rem;
}
@media print {
    .btn-toolbar, .card-header, .btn {
        display: none !important;
    }
}
</style>
@endpush
@endsection
