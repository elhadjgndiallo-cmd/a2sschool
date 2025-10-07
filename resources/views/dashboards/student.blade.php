@php use Illuminate\Support\Facades\Storage; @endphp
@extends('layouts.app')

@section('title', 'Dashboard Élève')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Mon Espace Élève</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-print me-1"></i>
                Imprimer Bulletin
            </button>
        </div>
    </div>
</div>

<!-- Informations élève -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                @php
                    use App\Helpers\ImageHelper;
                    $photoPath = $eleve->utilisateur->photo_profil ?? null;
                    $name = $eleve->nom_complet;
                @endphp
                {!! ImageHelper::profileImage($photoPath, $name, [
                    'class' => 'rounded-circle mb-3',
                    'style' => 'width: 80px; height: 80px; object-fit: cover;'
                ]) !!}
                <h5>{{ $eleve->nom_complet }}</h5>
                <p class="text-muted">{{ $eleve->numero_etudiant }}</p>
                @if($eleve->classe)
                    <span class="badge bg-primary">{{ $eleve->classe->nom }}</span>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Emploi du temps d'aujourd'hui - {{ now()->locale('fr')->isoFormat('dddd D MMMM YYYY') }}</h6>
            </div>
            <div class="card-body">
                @if(count($emploisDuJour) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Heure</th>
                                    <th>Matière</th>
                                    <th>Enseignant</th>
                                    <th>Salle</th>
                                    <th>Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($emploisDuJour as $emploi)
                                <tr>
                                    <td>{{ $emploi->heure_debut->format('H:i') }} - {{ $emploi->heure_fin->format('H:i') }}</td>
                                    <td>
                                        <span class="badge" style="background-color: {{ $emploi->matiere->couleur }}">
                                            {{ $emploi->matiere->nom }}
                                        </span>
                                    </td>
                                    <td>{{ $emploi->enseignant->nom_complet }}</td>
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

<!-- Dernières notes et actions -->
<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Mes Dernières Notes</h6>
                <a href="#" class="btn btn-sm btn-outline-primary">Voir toutes</a>
            </div>
            <div class="card-body">
                @if($dernieresNotes->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Matière</th>
                                    <th>Évaluation</th>
                                    <th>Note Cours</th>
                                    <th>Note Comp.</th>
                                    <th>Note Finale</th>
                                    <th>Enseignant</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dernieresNotes as $note)
                                <tr>
                                    <td>{{ $note->date_evaluation->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge" style="background-color: {{ $note->matiere->couleur }}">
                                            {{ $note->matiere->nom }}
                                        </span>
                                    </td>
                                    <td>{{ $note->titre ?? ucfirst($note->type_evaluation) }}</td>
                                    <td class="text-center">
                                        @if($note->note_cours !== null)
                                            @php
                                                $appreciationCours = $note->eleve->classe->getAppreciation($note->note_cours);
                                            @endphp
                                            <span class="badge bg-{{ $appreciationCours['color'] }}">
                                                {{ number_format($note->note_cours, 2) }}/{{ $note->eleve->classe->note_max }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($note->note_composition !== null)
                                            @php
                                                $appreciationComposition = $note->eleve->classe->getAppreciation($note->note_composition);
                                            @endphp
                                            <span class="badge bg-{{ $appreciationComposition['color'] }}">
                                                {{ number_format($note->note_composition, 2) }}/{{ $note->eleve->classe->note_max }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $noteFinale = $note->note_finale ?? 0;
                                            $appreciation = $note->eleve->classe->getAppreciation($noteFinale);
                                        @endphp
                                        <span class="badge bg-{{ $appreciation['color'] }}">
                                            {{ number_format($noteFinale, 2) }}/{{ $note->eleve->classe->note_max }}
                                        </span>
                                    </td>
                                    <td>{{ $note->enseignant->utilisateur->name }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center text-muted">
                        <i class="fas fa-chart-line fa-2x mb-3"></i>
                        <p>Aucune note disponible</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-4">
        <!-- Événements à venir -->
        @if($evenements->count() > 0)
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-calendar-check me-2"></i>
                    Événements à venir
                </h6>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    @foreach($evenements as $evenement)
                    <div class="list-group-item px-0">
                        <div class="d-flex w-100 justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $evenement->titre }}</h6>
                                <p class="mb-1 text-muted">{{ $evenement->description }}</p>
                                @if($evenement->lieu)
                                    <small class="text-info">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        {{ $evenement->lieu }}
                                    </small>
                                @endif
                            </div>
                            <div class="text-end">
                                <small class="text-muted">
                                    {{ \Carbon\Carbon::parse($evenement->date_debut)->format('d/m/Y') }}
                                </small>
                                @if($evenement->heure_debut)
                                    <br>
                                    <small class="text-muted">
                                        {{ \Carbon\Carbon::parse($evenement->heure_debut)->format('H:i') }}
                                    </small>
                                @endif
                            </div>
                        </div>
                        <div class="mt-2">
                            <span class="badge" style="background-color: {{ $evenement->couleur ?? '#3788d8' }}">
                                {{ ucfirst($evenement->type) }}
                            </span>
                            @if($evenement->classe)
                                <span class="badge bg-secondary">{{ $evenement->classe->nom }}</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="text-center mt-3">
                    <a href="{{ route('evenements.index') }}" class="btn btn-sm btn-outline-primary">
                        Voir tous les événements
                    </a>
                </div>
            </div>
        </div>
        @endif

        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">Actions Rapides</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('student.emploi-temps') }}" class="btn btn-primary">
                        <i class="fas fa-calendar me-2"></i>
                        Emploi du Temps Complet
                    </a>
                    <a href="{{ route('student.notes') }}" class="btn btn-success">
                        <i class="fas fa-chart-line me-2"></i>
                        Toutes mes Notes
                    </a>
                    <a href="{{ route('student.bulletin') }}" class="btn btn-info">
                        <i class="fas fa-file-alt me-2"></i>
                        Mon Bulletin
                    </a>
                    <a href="{{ route('student.absences') }}" class="btn btn-warning">
                        <i class="fas fa-clock me-2"></i>
                        Mes Absences
                    </a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Informations</h6>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-info-circle text-info me-2"></i>
                            Prochain contrôle
                        </div>
                        <small class="text-muted">Lundi</small>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-calendar-check text-success me-2"></i>
                            Vacances scolaires
                        </div>
                        <small class="text-muted">Dans 2 semaines</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
