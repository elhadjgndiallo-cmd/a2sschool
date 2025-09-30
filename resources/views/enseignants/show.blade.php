@extends('layouts.app')

@section('title', 'Détails Enseignant')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-user-circle me-2"></i>
        Détails de l'Enseignant
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('enseignants.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Retour
        </a>
        <a href="{{ route('enseignants.edit', $enseignant->id) }}" class="btn btn-outline-primary ms-2">
            <i class="fas fa-edit me-1"></i>
            Modifier
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
    <!-- Informations personnelles -->
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Informations Personnelles</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-12 text-center mb-4">
                        @if($enseignant->utilisateur->photo_profil && Storage::disk('public')->exists($enseignant->utilisateur->photo_profil)
                            <div class="mb-3">
                                <img src="{{ asset('storage/' . $enseignant->utilisateur->photo_profil) }}" alt="Photo de profil" class="img-thumbnail rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                                <form action="{{ route('enseignants.delete-photo', $enseignant->id) }}" method="POST" class="mt-2">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette photo de profil ?')">
                                        <i class="fas fa-trash-alt me-1"></i> Supprimer la photo
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="avatar-lg mx-auto">
                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 100px; height: 100px; font-size: 2.5rem;">
                                    {{ substr($enseignant->utilisateur->prenom, 0, 1) }}{{ substr($enseignant->utilisateur->nom, 0, 1) }}
                                </div>
                            </div>
                        @endif
                        <h4 class="mt-3">{{ $enseignant->utilisateur->nom }} {{ $enseignant->utilisateur->prenom }}</h4>
                        <p class="text-muted">
                            <span class="badge bg-{{ $enseignant->actif ? 'success' : 'danger' }}">
                                {{ $enseignant->actif ? 'Actif' : 'Inactif' }}
                            </span>
                            <span class="badge bg-info ms-2">{{ ucfirst($enseignant->statut) }}</span>
                        </p>
                    </div>
                </div>
                
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Numéro Employé:</div>
                    <div class="col-md-8">{{ $enseignant->numero_employe }}</div>
                </div>
                
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Email:</div>
                    <div class="col-md-8">{{ $enseignant->utilisateur->email }}</div>
                </div>
                
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Téléphone:</div>
                    <div class="col-md-8">{{ $enseignant->utilisateur->telephone }}</div>
                </div>
                
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Adresse:</div>
                    <div class="col-md-8">{{ $enseignant->utilisateur->adresse }}</div>
                </div>
                
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Date de naissance:</div>
                    <div class="col-md-8">{{ \Carbon\Carbon::parse($enseignant->utilisateur->date_naissance)->format('d/m/Y') }}</div>
                </div>
                
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Lieu de naissance:</div>
                    <div class="col-md-8">{{ $enseignant->utilisateur->lieu_naissance }}</div>
                </div>
                
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Sexe:</div>
                    <div class="col-md-8">{{ $enseignant->utilisateur->sexe == 'M' ? 'Masculin' : 'Féminin' }}</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Informations professionnelles -->
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Informations Professionnelles</h5>
            </div>
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Spécialité:</div>
                    <div class="col-md-8">{{ $enseignant->specialite }}</div>
                </div>
                
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Diplôme:</div>
                    <div class="col-md-8">{{ $enseignant->diplome }}</div>
                </div>
                
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Date d'embauche:</div>
                    <div class="col-md-8">{{ \Carbon\Carbon::parse($enseignant->date_embauche)->format('d/m/Y') }}</div>
                </div>
                
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Statut:</div>
                    <div class="col-md-8">
                        <span class="badge bg-info">{{ ucfirst($enseignant->statut) }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Matières enseignées -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Matières Enseignées</h5>
            </div>
            <div class="card-body">
                @if($enseignant->matieres->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Code</th>
                                <th>Coefficient</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($enseignant->matieres as $matiere)
                            <tr>
                                <td>{{ $matiere->nom }}</td>
                                <td>{{ $matiere->code }}</td>
                                <td>{{ $matiere->coefficient }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-muted text-center">
                    <i class="fas fa-info-circle me-1"></i>
                    Aucune matière assignée à cet enseignant.
                </p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Emploi du temps -->
<div class="card mb-4">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0">Emploi du Temps</h5>
    </div>
    <div class="card-body">
        @if($enseignant->emploisTemps->count() > 0)
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Jour</th>
                        <th>Heure Début</th>
                        <th>Heure Fin</th>
                        <th>Matière</th>
                        <th>Classe</th>
                        <th>Salle</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($enseignant->emploisTemps as $emploi)
                    <tr>
                        <td>{{ ucfirst($emploi->jour_semaine) }}</td>
                        <td>{{ $emploi->heure_debut }}</td>
                        <td>{{ $emploi->heure_fin }}</td>
                        <td>{{ $emploi->matiere->nom }}</td>
                        <td>{{ $emploi->classe->nom }}</td>
                        <td>{{ $emploi->salle }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-muted text-center">
            <i class="fas fa-info-circle me-1"></i>
            Aucun emploi du temps défini pour cet enseignant.
        </p>
        @endif
    </div>
</div>
@endsection