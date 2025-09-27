@extends('layouts.app')

@section('title', 'Détails de la Classe')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-school me-2"></i>
        Classe: {{ $classe->nom }}
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('classes.edit', $classe->id) }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-edit me-1"></i>
                Modifier
            </a>
            <a href="{{ route('emplois-temps.show', $classe->id) }}" class="btn btn-sm btn-outline-info">
                <i class="fas fa-calendar-alt me-1"></i>
                Emploi du temps
            </a>
        </div>
        <a href="{{ route('classes.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Retour à la liste
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row">
    <!-- Informations générales -->
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Informations générales</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tbody>
                        <tr>
                            <th style="width: 40%">Nom:</th>
                            <td>{{ $classe->nom }}</td>
                        </tr>
                        <tr>
                            <th>Niveau:</th>
                            <td>{{ $classe->niveau }}</td>
                        </tr>
                        <tr>
                            <th>Section:</th>
                            <td>{{ $classe->section }}</td>
                        </tr>
                        <tr>
                            <th>Effectif:</th>
                            <td>
                                <span class="badge bg-{{ $classe->effectif_actuel >= $classe->effectif_max ? 'danger' : 'success' }}">
                                    {{ $classe->effectif_actuel }} / {{ $classe->effectif_max }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Statut:</th>
                            <td>
                                <span class="badge bg-{{ $classe->actif ? 'success' : 'danger' }}">
                                    {{ $classe->actif ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
                @if($classe->description)
                <div class="mt-3">
                    <h6>Description:</h6>
                    <p class="text-muted">{{ $classe->description }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Liste des élèves -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Élèves inscrits</h5>
                <span class="badge bg-primary">{{ count($classe->eleves) }} élèves</span>
            </div>
            <div class="card-body p-0">
                @if(count($classe->eleves) > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Matricule</th>
                                <th scope="col">Nom</th>
                                <th scope="col">Prénom</th>
                                <th scope="col">Genre</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($classe->eleves as $eleve)
                            <tr>
                                <td>{{ $eleve->matricule }}</td>
                                <td>{{ $eleve->nom }}</td>
                                <td>{{ $eleve->prenom }}</td>
                                <td>{{ $eleve->genre }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('eleves.show', $eleve->id) }}" class="btn btn-outline-primary" title="Voir détails">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('eleves.edit', $eleve->id) }}" class="btn btn-outline-secondary" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4">
                    <p class="text-muted mb-0">
                        <i class="fas fa-user-graduate me-1"></i>
                        Aucun élève inscrit dans cette classe
                    </p>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Matières et enseignants -->
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Matières et enseignants</h5>
            </div>
            <div class="card-body p-0">
                @if(count($classe->emploiTemps) > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Matière</th>
                                <th scope="col">Enseignant</th>
                                <th scope="col">Heures/semaine</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $matieres = [];
                                foreach($classe->emploiTemps as $cours) {
                                    if (!isset($matieres[$cours->matiere_id])) {
                                        $matieres[$cours->matiere_id] = [
                                            'nom' => $cours->matiere->nom,
                                            'enseignant' => $cours->enseignant->nom . ' ' . $cours->enseignant->prenom,
                                            'heures' => 1
                                        ];
                                    } else {
                                        $matieres[$cours->matiere_id]['heures']++;
                                    }
                                }
                            @endphp
                            
                            @foreach($matieres as $matiere)
                            <tr>
                                <td>{{ $matiere['nom'] }}</td>
                                <td>{{ $matiere['enseignant'] }}</td>
                                <td>{{ $matiere['heures'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4">
                    <p class="text-muted mb-0">
                        <i class="fas fa-book me-1"></i>
                        Aucune matière assignée à cette classe
                    </p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection