@extends('layouts.app')

@section('title', 'Résultats Tests Mensuels - ' . $classe->nom)

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-chart-line me-2"></i>
        Résultats Tests Mensuels - {{ $classe->nom }}
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('notes.mensuel.modifier', $classe->id) }}?mois={{ $mois }}&annee={{ $annee }}" 
               class="btn btn-sm btn-warning">
                <i class="fas fa-edit me-1"></i>
                Modifier Notes
            </a>
            <a href="{{ route('notes.mensuel.resultats.imprimer', $classe->id) }}?mois={{ $mois }}&annee={{ $annee }}" 
               class="btn btn-sm btn-primary" target="_blank">
                <i class="fas fa-print me-1"></i>
                Imprimer
            </a>
        </div>
        <a href="{{ route('notes.mensuel.classe', $classe->id) }}?mois={{ $mois }}&annee={{ $annee }}" 
           class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Retour
        </a>
    </div>
</div>

<!-- Filtres -->
<div class="card mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">
            <i class="fas fa-filter me-2"></i>
            Filtres
        </h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('notes.mensuel.resultats', $classe->id) }}">
            <div class="row">
                <div class="col-md-4">
                    <label for="mois" class="form-label">Mois</label>
                    <select name="mois" id="mois" class="form-select">
                        @foreach($moisListe as $num => $nom)
                        <option value="{{ $num }}" {{ $mois == $num ? 'selected' : '' }}>
                            {{ $nom }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="annee" class="form-label">Année</label>
                    <select name="annee" id="annee" class="form-select">
                        @for($i = date('Y') - 2; $i <= date('Y') + 2; $i++)
                        <option value="{{ $i }}" {{ $annee == $i ? 'selected' : '' }}>
                            {{ $i }}
                        </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i>
                        Filtrer
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

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
                        <th scope="col" class="text-center">Actions</th>
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
                        <td class="text-center">
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('notes.mensuel.eleve.details', $eleve->id) }}?mois={{ $mois }}&annee={{ $annee }}" 
                                   class="btn btn-outline-info" 
                                   title="Voir les détails">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if(auth()->user()->hasPermission('notes.edit'))
                                <a href="{{ route('notes.mensuel.modifier', $classe->id) }}?mois={{ $mois }}&annee={{ $annee }}" 
                                   class="btn btn-outline-warning" 
                                   title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endif
                            </div>
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
@endsection
