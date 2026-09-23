@extends('layouts.app')

@section('title', 'Classement fusionné - Tests mensuels')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-trophy me-2"></i>
        Classement fusionné
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0 gap-2">
        @if(count($resultats) > 0)
        <a href="{{ route('notes.mensuel.resultats-fusion.imprimer') }}?{{ $queryFusion }}"
           class="btn btn-sm btn-success" target="_blank">
            <i class="fas fa-print me-1"></i>
            Imprimer
        </a>
        @endif
        <a href="{{ route('notes.mensuel.fusion') }}?{{ $queryFusion }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Retour
        </a>
    </div>
</div>

<form method="GET" action="{{ route('notes.mensuel.resultats-fusion') }}" class="mb-3">
    @foreach($classes as $classe)
        <input type="hidden" name="classes[]" value="{{ $classe->id }}">
    @endforeach
    <div class="row g-2">
        @if(isset($anneeScolaireActive))
        <div class="col-12 col-sm-6 col-md-3">
            <input type="text" class="form-control" value="{{ $anneeScolaireActive->nom }}" readonly title="Année scolaire">
        </div>
        @endif
        <div class="col-12 col-sm-6 col-md-3">
            <select name="mois" id="mois" class="form-select" title="Mois">
                @foreach($moisListe as $num => $nom)
                <option value="{{ $num }}" {{ $mois == $num ? 'selected' : '' }}>{{ $nom }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <select name="annee" id="annee" class="form-select" title="Année">
                @foreach(($anneesDisponibles ?? [date('Y')]) as $anneeOption)
                <option value="{{ $anneeOption }}" {{ $annee == $anneeOption ? 'selected' : '' }}>{{ $anneeOption }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <button type="submit" class="btn btn-primary w-100">
                <i class="fas fa-search"></i>
                <span class="d-none d-sm-inline">Filtrer</span>
            </button>
        </div>
    </div>
</form>

<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="mb-0">
            <i class="fas fa-object-group me-2"></i>
            {{ $nomsClasses }} — {{ $moisListe[$mois] }} {{ $annee }}
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
                        <th scope="col">Classe</th>
                        <th scope="col" class="text-center">Moyenne</th>
                        <th scope="col" class="text-center">Appréciation</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($resultats as $resultat)
                    @php
                        $eleve = $resultat['eleve'];
                        $moyenne = $resultat['moyenne'];
                        $rang = $resultat['rang'];
                        $classeEleve = $eleve->classe;
                        $noteMax = $classeEleve->note_max ?? 20;
                        $seuil = $classeEleve->seuil_reussite ?? 10;

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
                    <tr>
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
                        <td>{{ $eleve->nom }}</td>
                        <td>{{ $eleve->prenom }}</td>
                        <td>{{ $classeEleve->nom ?? '—' }}</td>
                        <td class="text-center">
                            <span class="badge bg-{{ $moyenne >= $seuil ? 'success' : ($moyenne >= ($seuil / 2) ? 'warning' : 'danger') }} fs-6">
                                @if($moyenne == 0.00)
                                    00/{{ $noteMax }}
                                @else
                                    {{ number_format($moyenne, 2) }}/{{ $noteMax }}
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
            <p class="text-muted">Aucun élève trouvé pour les classes sélectionnées.</p>
        </div>
        @endif
    </div>
</div>
@endsection
