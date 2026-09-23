@extends('layouts.app')

@section('title', 'Statistiques absences enseignants')

@section('content')
<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom flex-wrap gap-2">
    <h1 class="h4 mb-0">
        <i class="fas fa-chart-bar me-2"></i>
        Statistiques des absences
    </h1>
    <div class="d-flex flex-wrap align-items-center gap-2">
        <div class="btn-group">
            <a href="{{ route('absences-enseignants.statistiques', ['vue' => 'mois', 'mois' => $mois ?: now()->format('Y-m')]) }}"
               class="btn {{ $vue === 'mois' ? 'btn-primary' : 'btn-outline-primary' }}">
                Un mois
            </a>
            <a href="{{ route('absences-enseignants.statistiques', ['vue' => 'annee']) }}"
               class="btn {{ $vue === 'annee' ? 'btn-primary' : 'btn-outline-primary' }}">
                Année
            </a>
        </div>
        <a href="{{ route('absences-enseignants.index') }}" class="btn btn-outline-secondary">
            Retour à la liste
        </a>
    </div>
</div>

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div class="d-flex flex-wrap align-items-center gap-2">
        <strong>{{ $periodeLabel }}</strong>
        @if($vue === 'mois')
        <form method="GET" action="{{ route('absences-enseignants.statistiques') }}" class="d-flex align-items-center gap-1 mb-0">
            <input type="hidden" name="vue" value="mois">
            <a href="{{ route('absences-enseignants.statistiques', ['vue' => 'mois', 'mois' => $moisPrecedent]) }}" class="btn btn-outline-secondary btn-sm" title="Mois précédent">
                <i class="fas fa-chevron-left"></i>
            </a>
            <select name="mois" id="mois" class="form-select form-select-sm" style="min-width: 170px;" onchange="this.form.submit()">
                @foreach($moisOptions as $option)
                    <option value="{{ $option['valeur'] }}" @selected($mois === $option['valeur'])>{{ $option['label'] }}</option>
                @endforeach
                @if($mois && collect($moisOptions)->pluck('valeur')->doesntContain($mois))
                    <option value="{{ $mois }}" selected>{{ $periodeLabel }}</option>
                @endif
            </select>
            <a href="{{ route('absences-enseignants.statistiques', ['vue' => 'mois', 'mois' => $moisSuivant]) }}" class="btn btn-outline-secondary btn-sm" title="Mois suivant">
                <i class="fas fa-chevron-right"></i>
            </a>
        </form>
        @endif
    </div>
    <div class="text-muted">
        Total : <strong>{{ \App\Models\AbsenceEnseignant::formatDureeMinutes($totalMinutes) }}</strong>
        chômées {{ $vue === 'annee' ? 'cette année' : 'ce mois' }}
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Prénom</th>
                        <th>Nom</th>
                        <th>Spécialité</th>
                        <th>Nombre d'heures chômées</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lignes as $ligne)
                        <tr>
                            <td>{{ $ligne['prenom'] ?: '—' }}</td>
                            <td>{{ $ligne['nom'] ?: '—' }}</td>
                            <td class="text-muted">{{ $ligne['specialite'] }}</td>
                            <td>
                                @if($ligne['total_minutes'] > 0)
                                    <span class="badge bg-danger">{{ \App\Models\AbsenceEnseignant::formatDureeMinutes($ligne['total_minutes']) }}</span>
                                @else
                                    <span class="text-muted">0h</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Aucun enseignant.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
