@extends('layouts.app')

@section('title', 'Résultats Tests Mensuels - ' . $classe->nom)

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-chart-line me-2"></i>
        Résultats Tests Mensuels - {{ $classe->nom }}
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('notes.mensuel.classe', $classe->id) }}?mois={{ $mois }}&annee={{ $annee }}" 
           class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Retour
        </a>
    </div>
</div>

<!-- Filtres -->
<form method="GET" action="{{ route('notes.mensuel.resultats', $classe->id) }}" class="mb-3">
    <div class="row g-2">
        @if(isset($anneeScolaireActive))
        <div class="col-12 col-sm-6 col-md-3">
            <input type="text" class="form-control" value="{{ $anneeScolaireActive->nom }}" readonly title="Année scolaire">
        </div>
        @endif
        <div class="col-12 col-sm-6 col-md-3">
            <select name="mois" id="mois" class="form-select" title="Mois">
                @foreach($moisListe as $num => $nom)
                <option value="{{ $num }}" {{ $mois == $num ? 'selected' : '' }}>
                    {{ $nom }}
                </option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <select name="annee" id="annee" class="form-select" title="Année">
                @foreach(($anneesDisponibles ?? [date('Y')]) as $anneeOption)
                <option value="{{ $anneeOption }}" {{ $annee == $anneeOption ? 'selected' : '' }}>
                    {{ $anneeOption }}
                </option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="d-flex gap-1">
                <button type="submit" class="btn btn-primary flex-fill">
                    <i class="fas fa-search"></i>
                    <span class="d-none d-sm-inline">Filtrer</span>
                </button>
                <a href="{{ route('notes.mensuel.resultats', $classe->id) }}" class="btn btn-outline-secondary" title="Réinitialiser">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </div>
    </div>
</form>

@if(isset($stats))
<!-- Tableau statistique -->
<div class="card mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">
            <i class="fas fa-table me-2"></i>
            Tableau statistique - {{ $moisListe[$mois] }} {{ $annee }}
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-striped mb-0 text-center align-middle">
                <thead class="table-light">
                    <tr>
                        <th rowspan="2" class="text-start">Statistiques</th>
                        <th colspan="2">Effectifs</th>
                        <th colspan="2">Composés</th>
                        <th colspan="2">Non composés</th>
                        <th colspan="4">Moyennant</th>
                        <th colspan="4">Non moyennant</th>
                    </tr>
                    <tr>
                        <th>Total</th>
                        <th>Filles</th>
                        <th>Total</th>
                        <th>Filles</th>
                        <th>Total</th>
                        <th>Filles</th>
                        <th>Total</th>
                        <th>Filles</th>
                        <th>% Total</th>
                        <th>% Filles</th>
                        <th>Total</th>
                        <th>Filles</th>
                        <th>% Total</th>
                        <th>% Filles</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-start fw-bold">Classe</td>
                        <td>{{ $stats['effectifs']['total'] }}</td>
                        <td>{{ $stats['effectifs']['filles'] }}</td>
                        <td>{{ $stats['composes']['total'] }}</td>
                        <td>{{ $stats['composes']['filles'] }}</td>
                        <td>{{ $stats['non_composes']['total'] }}</td>
                        <td>{{ $stats['non_composes']['filles'] }}</td>
                        <td class="text-success fw-bold">{{ $stats['moyennant']['total'] }}</td>
                        <td class="text-success fw-bold">{{ $stats['moyennant']['filles'] }}</td>
                        <td class="text-success">{{ $stats['moyennant']['pct_total'] }}%</td>
                        <td class="text-success">{{ $stats['moyennant']['pct_filles'] }}%</td>
                        <td class="text-danger fw-bold">{{ $stats['non_moyennant']['total'] }}</td>
                        <td class="text-danger fw-bold">{{ $stats['non_moyennant']['filles'] }}</td>
                        <td class="text-danger">{{ $stats['non_moyennant']['pct_total'] }}%</td>
                        <td class="text-danger">{{ $stats['non_moyennant']['pct_filles'] }}%</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<!-- Résultats -->
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-trophy me-2"></i>
            Classement - {{ $moisListe[$mois] }} {{ $annee }}
        </h5>
        <span class="badge bg-primary">{{ count($resultats) }} élèves classés</span>
    </div>
    <div class="card-body p-0">
        @if(count($resultats) > 0)
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col" class="text-center">Rang</th>
                        <th scope="col">Matricule</th>
                        <th scope="col">Nom</th>
                        <th scope="col">Prénom</th>
                        <th scope="col" class="text-center">Moyenne</th>
                        <th scope="col" class="text-center">Mention</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($resultats as $resultat)
                    @php
                        $eleve = $resultat['eleve'];
                        $moyenne = $resultat['moyenne'];
                        $rang = $resultat['rang'];
                        
                        // Déterminer l'appréciation selon la moyenne
                        if ($moyenne >= 16) {
                            $appreciation = 'Excellent';
                            $color = 'success';
                        } elseif ($moyenne >= 14) {
                            $appreciation = 'Très bien';
                            $color = 'primary';
                        } elseif ($moyenne >= 12) {
                            $appreciation = 'Bien';
                            $color = 'info';
                        } elseif ($moyenne >= 10) {
                            $appreciation = 'Assez bien';
                            $color = 'warning';
                        } elseif ($moyenne >= 8) {
                            $appreciation = 'Passable';
                            $color = 'secondary';
                        } else {
                            $appreciation = 'Insuffisant';
                            $color = 'danger';
                        }
                    @endphp
                    <tr class="table-row-clickable" data-href="{{ route('notes.mensuel.eleve.details', $eleve->id) }}?mois={{ $mois }}&annee={{ $annee }}" role="button" tabindex="0">
                        <td class="text-center">
                            @if($rang <= 3)
                                <span class="badge bg-{{ $rang == 1 ? 'warning' : ($rang == 2 ? 'secondary' : 'success') }}">
                                    {{ $rang }}{{ $rang == 1 ? 'er' : 'ème' }}
                                </span>
                            @else
                                <span class="badge bg-light text-dark">{{ $rang }}ème</span>
                            @endif
                        </td>
                        <td class="fw-bold">{{ $eleve->numero_etudiant }}</td>
                        <td>{{ $eleve->utilisateur->nom }}</td>
                        <td>{{ $eleve->utilisateur->prenom }}</td>
                        <td class="text-center">
                            <span class="badge bg-{{ $moyenne >= $classe->seuil_reussite ? 'success' : ($moyenne >= ($classe->seuil_reussite / 2) ? 'warning' : 'danger') }} fs-6">
                                @if($moyenne == 0.00)
                                    00/{{ $classe->note_max }}
                                @else
                                    {{ number_format($moyenne, 2) }}/{{ $classe->note_max }}
                                @endif
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-{{ $color }}">{{ $appreciation }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-4">
            <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">Aucun résultat disponible</h5>
            <p class="text-muted">Aucun test mensuel n'a été enregistré pour {{ $moisListe[$mois] }} {{ $annee }}.</p>
            <a href="{{ route('notes.mensuel.saisir', $classe->id) }}?mois={{ $mois }}&annee={{ $annee }}" 
               class="btn btn-success">
                <i class="fas fa-plus me-1"></i>
                Saisir les tests
            </a>
        </div>
        @endif
    </div>
</div>

<!-- Détails des notes -->
@if(count($resultats) > 0)
<div class="card mt-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">
            <i class="fas fa-list me-2"></i>
            Détail des notes
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col">Matricule</th>
                        <th scope="col">Nom</th>
                        <th scope="col">Prénom</th>
                        <th scope="col">Matière</th>
                        <th scope="col" class="text-center">Note</th>
                        <th scope="col" class="text-center">Coefficient</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tests as $test)
                    <tr>
                        <td class="fw-bold">{{ $test->eleve->numero_etudiant }}</td>
                        <td>{{ $test->eleve->utilisateur->nom }}</td>
                        <td>{{ $test->eleve->utilisateur->prenom }}</td>
                        <td>{{ $test->matiere->nom }}</td>
                        <td class="text-center">
                            <span class="badge bg-{{ $test->note >= 10 ? 'success' : ($test->note >= 5 ? 'warning' : 'danger') }}">
                                {{ number_format($test->note, 2) }}/{{ $classe->note_max }}
                            </span>
                        </td>
                        <td class="text-center">{{ $test->coefficient }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<div class="card mb-4 no-print">
    <div class="card-header bg-dark text-white">
        <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Actions</h5>
    </div>
    <div class="card-body">
        <div class="d-flex flex-wrap gap-2">
            @if(auth()->user()->hasPermission('notes.edit'))
            <a href="{{ route('notes.mensuel.modifier', $classe->id) }}?mois={{ $mois }}&annee={{ $annee }}" class="btn btn-warning">
                <i class="fas fa-edit me-1"></i> Modifier les notes
            </a>
            @endif
            <a href="{{ route('notes.mensuel.resultats.imprimer', $classe->id) }}?mois={{ $mois }}&annee={{ $annee }}" class="btn btn-primary" target="_blank">
                <i class="fas fa-print me-1"></i> Imprimer
            </a>
            <a href="{{ route('notes.mensuel.detail-notes.imprimer', $classe->id) }}?mois={{ $mois }}&annee={{ $annee }}" class="btn btn-info" target="_blank">
                <i class="fas fa-file-alt me-1"></i> Imprimer le détail des notes
            </a>
        </div>
    </div>
</div>
@endsection
