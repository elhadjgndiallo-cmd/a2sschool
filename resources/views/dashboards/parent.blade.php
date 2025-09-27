@extends('layouts.app')

@section('title', 'Espace Parent')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center pt-3 pb-2 mb-3 border-bottom gap-2">
    <div>
        <h1 class="h2 mb-0">
            <i class="fas fa-user-friends text-primary me-2"></i>
            Espace Parent
        </h1>
        <p class="text-muted mb-0">Bienvenue {{ $parent->utilisateur->nom }} {{ $parent->utilisateur->prenom }}</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('parent.notes.index') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-chart-line me-1"></i>
                <span class="d-none d-sm-inline">Voir Notes</span>
            </a>
            <a href="{{ route('parent.absences.index') }}" class="btn btn-sm btn-warning">
                <i class="fas fa-exclamation-triangle me-1"></i>
                <span class="d-none d-sm-inline">Voir Absences</span>
            </a>
        </div>
    </div>
</div>

<!-- Statistiques parent -->
<div class="row mb-4 g-3">
    <div class="col-12 col-sm-6 col-md-3">
        <div class="card bg-primary text-white h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-child fa-2x"></i>
                </div>
                <div class="flex-grow-1 ms-3">
                    <h3 class="mb-0">{{ $stats['total_enfants'] }}</h3>
                    <p class="mb-0">Enfant(s)</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3">
        <div class="card bg-success text-white h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-chart-line fa-2x"></i>
                </div>
                <div class="flex-grow-1 ms-3">
                    <h3 class="mb-0">{{ $stats['total_notes'] }}</h3>
                    <p class="mb-0">Notes</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3">
        <div class="card bg-warning text-white h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                </div>
                <div class="flex-grow-1 ms-3">
                    <h3 class="mb-0">{{ $stats['total_absences'] }}</h3>
                    <p class="mb-0">Absences</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3">
        <div class="card bg-info text-white h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-star fa-2x"></i>
                </div>
                <div class="flex-grow-1 ms-3">
                    <h3 class="mb-0">{{ number_format($stats['moyenne_generale'], 2) }}</h3>
                    <p class="mb-0">Moyenne</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Informations parent -->
<div class="row mb-4 g-3">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row align-items-center g-3">
                    <div class="col-12 col-md-2 text-center">
                        <i class="fas fa-user-friends fa-3x text-primary mb-2"></i>
                        <h6>{{ $parent->nom_complet }}</h6>
                        <small class="text-muted">{{ ucfirst($parent->lien_parente) }}</small>
                    </div>
                    <div class="col-12 col-md-10">
                        <h5>Mes Enfants ({{ $enfants->count() }})</h5>
                        <div class="row g-3">
                            @foreach($enfants as $enfant)
                            <div class="col-12 col-sm-6 col-md-4">
                                <div class="card border-primary">
                                    <div class="card-body text-center">
                                        <i class="fas fa-child fa-2x text-primary mb-2"></i>
                                        <h6>{{ $enfant->nom_complet }}</h6>
                                        @if($enfant->classe)
                                            <span class="badge bg-primary">{{ $enfant->classe->nom }}</span>
                                        @endif
                                        <div class="mt-2">
                                            <small class="text-muted">{{ $enfant->numero_etudiant }}</small>
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
    </div>
</div>

<!-- Détails par enfant -->
@foreach($enfants as $enfant)
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0">
                    <i class="fas fa-child me-2"></i>
                    {{ $enfant->nom_complet }} - {{ $enfant->classe->nom ?? 'Classe non assignée' }}
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Dernières notes -->
                    <div class="col-lg-6 mb-3">
                        <h6 class="text-primary">
                            <i class="fas fa-chart-line me-2"></i>
                            Dernières Notes
                        </h6>
                        @if($enfant->notes->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Matière</th>
                                            <th>Note</th>
                                            <th>Type</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($enfant->notes->take(3) as $note)
                                        <tr>
                                            <td>{{ $note->date_evaluation->format('d/m') }}</td>
                                            <td>{{ $note->matiere->nom }}</td>
                                            <td>
                                                <span class="badge {{ $note->note_sur >= 10 ? 'bg-success' : 'bg-danger' }}">
                                                    {{ number_format($note->note_sur, 2) }}/20
                                                </span>
                                            </td>
                                            <td>{{ ucfirst($note->type_evaluation) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <a href="{{ route('parent.notes.show', $enfant) }}" class="btn btn-sm btn-outline-primary">Voir toutes les notes</a>
                        @else
                            <p class="text-muted">Aucune note disponible</p>
                        @endif
                    </div>

                    <!-- Absences récentes -->
                    <div class="col-lg-6 mb-3">
                        <h6 class="text-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Absences Récentes
                        </h6>
                        @if($enfant->absences->count() > 0)
                            <div class="list-group">
                                @foreach($enfant->absences->take(3) as $absence)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $absence->date_absence->format('d/m/Y') }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            {{ $absence->matiere->nom ?? 'Journée complète' }}
                                        </small>
                                    </div>
                                    <span class="badge {{ $absence->statut == 'justifiee' ? 'bg-success' : ($absence->statut == 'non_justifiee' ? 'bg-danger' : 'bg-warning') }}">
                                        {{ ucfirst(str_replace('_', ' ', $absence->statut)) }}
                                    </span>
                                </div>
                                @endforeach
                            </div>
                            <a href="{{ route('parent.absences.show', $enfant) }}" class="btn btn-sm btn-outline-warning mt-2">Voir toutes les absences</a>
                        @else
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                Aucune absence récente
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endforeach

<!-- Dernières activités -->
@if(isset($dernieresActivites) && $dernieresActivites->count() > 0)
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-clock me-2"></i>
                    Dernières Activités
                </h6>
            </div>
            <div class="card-body">
                <div class="timeline">
                    @foreach($dernieresActivites as $activite)
                        <div class="timeline-item d-flex align-items-start mb-3">
                            <div class="flex-shrink-0">
                                <div class="bg-{{ $activite['couleur'] }} bg-opacity-10 rounded-circle p-2">
                                    <i class="{{ $activite['icone'] }} text-{{ $activite['couleur'] }}"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">
                                            @if($activite['type'] == 'note')
                                                Nouvelle note en {{ $activite['contenu']->matiere->nom }}
                                            @else
                                                Absence en {{ $activite['contenu']->matiere->nom ?? 'Journée complète' }}
                                            @endif
                                        </h6>
                                        <p class="mb-1 text-muted">
                                            {{ $activite['enfant']->utilisateur->nom }} {{ $activite['enfant']->utilisateur->prenom }}
                                            - {{ $activite['enfant']->classe->nom ?? 'N/A' }}
                                        </p>
                                        @if($activite['type'] == 'note')
                                            <span class="badge bg-{{ $activite['contenu']->note_sur >= 10 ? 'success' : 'danger' }}">
                                                {{ number_format($activite['contenu']->note_sur, 2) }}/20
                                            </span>
                                        @else
                                            <span class="badge bg-{{ $activite['contenu']->statut == 'justifiee' ? 'success' : 'warning' }}">
                                                {{ ucfirst(str_replace('_', ' ', $activite['contenu']->statut)) }}
                                            </span>
                                        @endif
                                    </div>
                                    <small class="text-muted">
                                        {{ $activite['date']->format('d/m/Y') }}
                                    </small>
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
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Actions Rapides</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('parent.notes.index') }}" class="btn btn-primary btn-block">
                            <i class="fas fa-chart-line me-2"></i>
                            Notes
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('parent.absences.index') }}" class="btn btn-warning btn-block">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Absences
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('parent.paiements.index') }}" class="btn btn-info btn-block">
                            <i class="fas fa-credit-card me-2"></i>
                            Paiements
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('parent.notifications.index') }}" class="btn btn-success btn-block">
                            <i class="fas fa-envelope me-2"></i>
                            Messages
                        </a>
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
