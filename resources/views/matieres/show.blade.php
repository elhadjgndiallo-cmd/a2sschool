@extends('layouts.app')

@section('title', 'Détails Matière - ' . $matiere->nom)

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <span class="badge me-2" style="background-color: {{ $matiere->couleur }}; color: white;">{{ $matiere->code }}</span>
        {{ $matiere->nom }}
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('matieres.edit', $matiere) }}" class="btn btn-primary">
                <i class="fas fa-edit me-1"></i>Modifier
            </a>
            <a href="{{ route('matieres.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Retour
            </a>
        </div>
    </div>
</div>

<!-- Statistiques -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $statistiques['total_enseignants'] }}</h4>
                        <p class="mb-0">Enseignants</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-chalkboard-teacher fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $statistiques['total_notes'] }}</h4>
                        <p class="mb-0">Notes Saisies</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-clipboard-list fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ round($statistiques['moyenne_generale'], 2) }}/20</h4>
                        <p class="mb-0">Moyenne</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-chart-bar fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $statistiques['classes_enseignees'] }}</h4>
                        <p class="mb-0">Classes</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Informations détaillées -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Informations Détaillées</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Code:</strong></td>
                        <td>{{ $matiere->code }}</td>
                    </tr>
                    <tr>
                        <td><strong>Coefficient:</strong></td>
                        <td><span class="badge bg-secondary fs-6">{{ $matiere->coefficient }}</span></td>
                    </tr>
                    <tr>
                        <td><strong>Couleur:</strong></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div style="width: 20px; height: 20px; background-color: {{ $matiere->couleur }}; border: 1px solid #ddd; border-radius: 3px; margin-right: 10px;"></div>
                                {{ $matiere->couleur }}
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Statut:</strong></td>
                        <td>
                            @if($matiere->actif)
                            <span class="badge bg-success">Actif</span>
                            @else
                            <span class="badge bg-secondary">Inactif</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Description:</strong></td>
                        <td>{{ $matiere->description ?: 'Aucune description' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Enseignants assignés -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Enseignants Assignés</h5>
            </div>
            <div class="card-body">
                @if($matiere->enseignants->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($matiere->enseignants as $enseignant)
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <div>
                            <strong>{{ $enseignant->utilisateur->name }}</strong>
                            <br><small class="text-muted">{{ $enseignant->specialite }}</small>
                        </div>
                        <span class="badge bg-primary">{{ $enseignant->statut }}</span>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-3">
                    <i class="fas fa-user-slash fa-2x text-muted mb-2"></i>
                    <p class="text-muted">Aucun enseignant assigné</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Classes où la matière est enseignée -->
@if($matiere->emploisTemps->count() > 0)
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Classes où cette matière est enseignée</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Classe</th>
                                <th>Niveau</th>
                                <th>Enseignant</th>
                                <th>Jour</th>
                                <th>Heure</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($matiere->emploisTemps as $emploi)
                            <tr>
                                <td><strong>{{ $emploi->classe->nom }}</strong></td>
                                <td>{{ $emploi->classe->niveau }}</td>
                                <td>{{ $emploi->enseignant->utilisateur->name }}</td>
                                <td>{{ ucfirst($emploi->jour) }}</td>
                                <td>{{ $emploi->heure_debut }} - {{ $emploi->heure_fin }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@endsection
