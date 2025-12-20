@extends('layouts.app')

@section('title', 'Mes Notes')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-chart-line me-2"></i>
        Mes Notes
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Retour au Dashboard
        </a>
    </div>
</div>

<!-- Statistiques -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <i class="fas fa-list fa-2x mb-2"></i>
                <h4>{{ $statistiques['total_notes'] }}</h4>
                <p class="mb-0">Total Notes</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <i class="fas fa-calculator fa-2x mb-2"></i>
                <h4>{{ number_format($statistiques['moyenne_generale'], 2) }}</h4>
                <p class="mb-0">Moyenne Générale</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <i class="fas fa-trophy fa-2x mb-2"></i>
                <h4>{{ $statistiques['meilleure_note'] ?? 'N/A' }}</h4>
                <p class="mb-0">Meilleure Note</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body text-center">
                <i class="fas fa-clock fa-2x mb-2"></i>
                <h4>{{ $statistiques['derniere_note']->note_finale ?? 'N/A' }}</h4>
                <p class="mb-0">Dernière Note</p>
            </div>
        </div>
    </div>
</div>

<!-- Liste des notes -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-list me-2"></i>
            Toutes mes notes
        </h5>
    </div>
    <div class="card-body">
        @if($notes->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Date</th>
                                    <th>Matière</th>
                                    <th>Type d'évaluation</th>
                                    @if($notes->isNotEmpty() && !$notes->first()->eleve->classe->isPrimaire())
                                    <th>Note Cours</th>
                                    @endif
                                    <th>Note Comp.</th>
                                    <th>Note Finale</th>
                                    <th>Enseignant</th>
                                    <th>Commentaire</th>
                                </tr>
                            </thead>
                    <tbody>
                        @foreach($notes as $note)
                        <tr>
                            <td>{{ $note->date_evaluation->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge" style="background-color: {{ $note->matiere->couleur ?? '#007bff' }}">
                                    {{ $note->matiere->nom }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $note->type_evaluation == 'devoir' ? 'primary' : ($note->type_evaluation == 'examen' ? 'danger' : 'info') }}">
                                    {{ ucfirst($note->type_evaluation) }}
                                </span>
                            </td>
                            @if(!$note->eleve->classe->isPrimaire())
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
                            @endif
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
                            <td>{{ $note->commentaire ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center">
                {{ $notes->links() }}
            </div>
        @else
            <div class="text-center text-muted py-5">
                <i class="fas fa-chart-line fa-3x mb-3"></i>
                <h5>Aucune note trouvée</h5>
                <p>Vous n'avez pas encore de notes enregistrées.</p>
            </div>
        @endif
    </div>
</div>

<!-- Actions -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <a href="{{ route('student.emploi-temps') }}" class="btn btn-primary btn-block">
                            <i class="fas fa-calendar me-2"></i>
                            Mon Emploi du Temps
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
                        <button type="button" class="btn btn-success btn-block" onclick="window.print()">
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
@media print {
    .btn-toolbar, .card-header, .btn {
        display: none !important;
    }
}
</style>
@endpush
@endsection




