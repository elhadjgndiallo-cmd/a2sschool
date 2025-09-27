@extends('layouts.app')

@section('title', 'Calendrier des Événements')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-calendar mr-2"></i>
                        Calendrier des Événements
                    </h3>
                    <div>
                        <a href="{{ route('evenements.index') }}" class="btn btn-secondary">
                            <i class="fas fa-list mr-1"></i>
                            Liste des événements
                        </a>
                        <a href="{{ route('evenements.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus mr-1"></i>
                            Nouvel Événement
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Navigation du calendrier -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h4>{{ \Carbon\Carbon::create($annee, $mois, 1)->locale('fr')->translatedFormat('F Y') }}</h4>
                        </div>
                        <div class="col-md-6 text-right">
                            <div class="btn-group" role="group">
                                <a href="{{ route('evenements.calendrier', ['mois' => $mois == 1 ? 12 : $mois - 1, 'annee' => $mois == 1 ? $annee - 1 : $annee]) }}" 
                                   class="btn btn-outline-primary">
                                    <i class="fas fa-chevron-left"></i>
                                    Mois précédent
                                </a>
                                <a href="{{ route('evenements.calendrier') }}" class="btn btn-outline-info">
                                    <i class="fas fa-calendar-day"></i>
                                    Aujourd'hui
                                </a>
                                <a href="{{ route('evenements.calendrier', ['mois' => $mois == 12 ? 1 : $mois + 1, 'annee' => $mois == 12 ? $annee + 1 : $annee]) }}" 
                                   class="btn btn-outline-primary">
                                    Mois suivant
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Calendrier -->
                    <div class="calendar-container">
                        <div class="calendar-grid">
                            <!-- En-têtes des jours -->
                            <div class="calendar-header">
                                <div class="calendar-day-header">Lun</div>
                                <div class="calendar-day-header">Mar</div>
                                <div class="calendar-day-header">Mer</div>
                                <div class="calendar-day-header">Jeu</div>
                                <div class="calendar-day-header">Ven</div>
                                <div class="calendar-day-header">Sam</div>
                                <div class="calendar-day-header">Dim</div>
                            </div>

                            <!-- Jours du mois -->
                            <div class="calendar-body">
                                @php
                                    $firstDay = \Carbon\Carbon::create($annee, $mois, 1);
                                    $lastDay = $firstDay->copy()->endOfMonth();
                                    $startDate = $firstDay->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
                                    $endDate = $lastDay->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);
                                    $currentDate = $startDate->copy();
                                @endphp

                                @while($currentDate <= $endDate)
                                    <div class="calendar-day {{ $currentDate->month != $mois ? 'other-month' : '' }} {{ $currentDate->isToday() ? 'today' : '' }}">
                                        <div class="calendar-day-number">{{ $currentDate->day }}</div>
                                        <div class="calendar-events">
                                            @foreach($evenements as $evenement)
                                                @if($currentDate->between($evenement->date_debut, $evenement->date_fin))
                                                    <div class="calendar-event" 
                                                         style="background-color: {{ $evenement->couleur }}; color: white;"
                                                         title="{{ $evenement->titre }} - {{ $evenement->type }}">
                                                        <a href="{{ route('evenements.show', $evenement->id) }}" 
                                                           class="text-white text-decoration-none">
                                                            {{ Str::limit($evenement->titre, 20) }}
                                                        </a>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                    @php $currentDate->addDay(); @endphp
                                @endwhile
                            </div>
                        </div>
                    </div>

                    <!-- Légende -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6>Légende des types d'événements :</h6>
                            <div class="d-flex flex-wrap">
                                <span class="badge badge-primary mr-2 mb-1">Cours</span>
                                <span class="badge badge-danger mr-2 mb-1">Examen</span>
                                <span class="badge badge-info mr-2 mb-1">Réunion</span>
                                <span class="badge badge-warning mr-2 mb-1">Congé</span>
                                <span class="badge badge-secondary mr-2 mb-1">Autre</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.calendar-container {
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
}

.calendar-header {
    display: contents;
}

.calendar-day-header {
    background: #f8f9fa;
    padding: 12px 8px;
    text-align: center;
    font-weight: bold;
    border-bottom: 1px solid #dee2e6;
    color: #495057;
}

.calendar-body {
    display: contents;
}

.calendar-day {
    min-height: 120px;
    border-right: 1px solid #dee2e6;
    border-bottom: 1px solid #dee2e6;
    padding: 8px;
    position: relative;
    background: #fff;
}

.calendar-day.other-month {
    background: #f8f9fa;
    color: #6c757d;
}

.calendar-day.today {
    background: #e3f2fd;
}

.calendar-day-number {
    font-weight: bold;
    margin-bottom: 4px;
    color: #495057;
}

.calendar-events {
    position: absolute;
    top: 30px;
    left: 4px;
    right: 4px;
    bottom: 4px;
    overflow: hidden;
}

.calendar-event {
    font-size: 11px;
    padding: 2px 4px;
    margin-bottom: 2px;
    border-radius: 3px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    color: #ffffff !important;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.7);
    font-weight: 500;
}

.calendar-event:hover {
    opacity: 0.8;
}

@media (max-width: 768px) {
    .calendar-day {
        min-height: 80px;
    }
    
    .calendar-event {
        font-size: 10px;
        padding: 1px 2px;
    }
}

/* Amélioration de la lisibilité des textes */
.calendar-event a {
    color: #ffffff !important;
    text-decoration: none !important;
    font-weight: 500;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.8);
}

.calendar-event a:hover {
    color: #ffffff !important;
    text-decoration: none !important;
    opacity: 0.9;
}
</style>
@endsection
