@extends('layouts.app')

@section('title', 'Détails Tests Mensuels - ' . $eleve->utilisateur->prenom . ' ' . $eleve->utilisateur->nom)

@section('content')
<div class="container-fluid">
    <!-- En-tête -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-user-graduate me-2"></i>
            Détails Tests Mensuels - {{ $eleve->utilisateur->prenom }} {{ $eleve->utilisateur->nom }}
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('notes.mensuel.eleve.create', $eleve->id) }}" class="btn btn-success me-2">
                <i class="fas fa-plus me-1"></i>
                Ajouter une note
            </a>
            <a href="{{ route('notes.mensuel.resultats', $eleve->classe_id) }}?mois={{ $mois }}&annee={{ $annee }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Retour
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Informations de l'élève -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Informations de l'élève</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <p class="mb-1"><strong>Nom complet:</strong> {{ $eleve->utilisateur->prenom }} {{ $eleve->utilisateur->nom }}</p>
                </div>
                <div class="col-md-3">
                    <p class="mb-1"><strong>Numéro étudiant:</strong> {{ $eleve->numero_etudiant }}</p>
                </div>
                <div class="col-md-3">
                    <p class="mb-1"><strong>Classe:</strong> {{ $eleve->classe->nom }}</p>
                </div>
                <div class="col-md-3">
                    <div class="text-center p-3 bg-light rounded">
                        <h5 class="mb-0 text-primary">Moyenne</h5>
                        <h3 class="mb-0 text-primary">{{ number_format($moyenne, 2) }}/20</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">
                <i class="fas fa-filter me-2"></i>
                Période
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('notes.mensuel.eleve.details', $eleve->id) }}">
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

    <!-- Détails des tests mensuels -->
    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>
                Tests Mensuels - {{ $moisListe[$mois] }} {{ $annee }}
            </h5>
            <span class="badge bg-primary">{{ count($tests) }} test(s)</span>
        </div>
        <div class="card-body p-0">
            @if(count($tests) > 0)
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Matière</th>
                            <th scope="col" class="text-center">Note</th>
                            <th scope="col" class="text-center">Coefficient</th>
                            <th scope="col">Enseignant</th>
                            <th scope="col" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tests as $test)
                        <tr>
                            <td><strong>{{ $test->matiere->nom }}</strong></td>
                            <td class="text-center">
                                <span class="badge bg-{{ $test->note >= 10 ? 'success' : ($test->note >= 5 ? 'warning' : 'danger') }} fs-6">
                                    {{ number_format($test->note, 2) }}/20
                                </span>
                            </td>
                            <td class="text-center">{{ $test->coefficient }}</td>
                            <td>{{ $test->enseignant->utilisateur->prenom }} {{ $test->enseignant->utilisateur->nom }}</td>
                            <td class="text-center">
                                @if(auth()->user()->hasPermission('notes.edit'))
                                <a href="{{ route('notes.mensuel.modifier', $eleve->classe_id) }}?mois={{ $mois }}&annee={{ $annee }}" 
                                   class="btn btn-sm btn-outline-warning" 
                                   title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="2"><strong>MOYENNE GÉNÉRALE</strong></th>
                            <th class="text-center">
                                <span class="badge bg-primary fs-5">
                                    {{ number_format($moyenne, 2) }}/20
                                </span>
                            </th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Aucun test mensuel</h5>
                <p class="text-muted">Aucun test mensuel n'a été enregistré pour {{ $moisListe[$mois] }} {{ $annee }}.</p>
                <a href="{{ route('notes.mensuel.eleve.create', $eleve->id) }}" class="btn btn-success">
                    <i class="fas fa-plus me-2"></i>
                    Ajouter une note
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

