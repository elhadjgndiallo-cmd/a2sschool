@extends('layouts.app')

@section('title', 'Bulletin - ' . $eleve->utilisateur->nom . ' ' . $eleve->utilisateur->prenom)

@section('content')
@php
    $schoolInfo = \App\Helpers\SchoolHelper::getSchoolInfo();
    $logoUrl = $schoolInfo && isset($schoolInfo->logo) ? asset('storage/' . $schoolInfo->logo) : null;
    $schoolName = $schoolInfo && isset($schoolInfo->nom) ? $schoolInfo->nom : 'École';
@endphp

<!-- En-tête avec logo et nom de l'école pour l'impression -->
<div class="school-header-section d-none d-print-block" style="background: #f8f9fa; padding: 15px; border-bottom: 2px solid #2c3e50; margin-bottom: 20px;">
    <div class="row align-items-center">
        @if($logoUrl && file_exists(public_path('storage/' . $schoolInfo->logo)))
        <div class="col-md-2 text-center">
            <img src="{{ $logoUrl }}" alt="Logo de l'école" style="max-width: 70px; max-height: 70px; object-fit: contain;">
        </div>
        @endif
        <div class="{{ $logoUrl && file_exists(public_path('storage/' . $schoolInfo->logo)) ? 'col-md-8' : 'col-md-10' }} text-center">
            <h2 class="mb-1" style="font-weight: 700; color: #2c3e50; text-transform: uppercase; letter-spacing: 1px; font-size: 1.5rem;">
                {{ $schoolName }}
            </h2>
            @if($schoolInfo && isset($schoolInfo->slogan) && $schoolInfo->slogan)
            <p class="mb-0" style="font-style: italic; color: #6c757d; font-size: 0.85rem;">"{{ $schoolInfo->slogan }}"</p>
            @endif
            @if($schoolInfo && isset($schoolInfo->adresse) && $schoolInfo->adresse)
            <p class="mb-0" style="color: #495057; font-size: 0.75rem; margin-top: 3px;">
                <i class="fas fa-map-marker-alt"></i> {{ $schoolInfo->adresse }}
            </p>
            @endif
        </div>
        <div class="col-md-2 text-center">
            @if($logoUrl && file_exists(public_path('storage/' . $schoolInfo->logo)))
            <img src="{{ $logoUrl }}" alt="Logo de l'école" style="max-width: 70px; max-height: 70px; object-fit: contain;">
            @endif
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-file-alt text-primary me-2"></i>
                        Bulletin - {{ $eleve->utilisateur->nom }} {{ $eleve->utilisateur->prenom }}
                    </h1>
                    <p class="text-muted mb-0">
                        {{ $eleve->classe->nom ?? 'Classe non assignée' }} - 
                        {{ ucfirst(str_replace('trimestre', 'Trimestre ', $periode)) }}
                    </p>
                </div>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button onclick="window.print()" class="btn btn-outline-primary me-2">
                        <i class="fas fa-print me-1"></i>
                        Imprimer
                    </button>
                    <a href="{{ route('parent.notes.show', $eleve) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        Retour aux notes
                    </a>
                </div>
            </div>

            @if($notes->count() > 0)
                <!-- Informations de l'élève -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted mb-1">Nom complet</h6>
                                <p class="mb-3">{{ $eleve->utilisateur->nom }} {{ $eleve->utilisateur->prenom }}</p>
                                
                                <h6 class="text-muted mb-1">Classe</h6>
                                <p class="mb-3">{{ $eleve->classe->nom ?? 'Non assignée' }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted mb-1">Période</h6>
                                <p class="mb-3">{{ ucfirst(str_replace('trimestre', 'Trimestre ', $periode)) }}</p>
                                
                                <h6 class="text-muted mb-1">Année scolaire</h6>
                                <p class="mb-3">{{ $eleve->anneeScolaire->nom ?? 'Non définie' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Moyennes par matière -->
                @if($moyennesParMatiere->count() > 0)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-bar me-2"></i>
                                Moyennes par matière
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Matière</th>
                                            <th class="text-center">Moyenne</th>
                                            <th class="text-center">Coefficient</th>
                                            <th class="text-center">Nombre de notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($moyennesParMatiere as $matiere => $data)
                                            <tr>
                                                <td>
                                                    <strong>{{ $matiere }}</strong>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge {{ $data['moyenne'] >= 10 ? 'bg-success' : 'bg-danger' }} fs-6">
                                                        {{ number_format($data['moyenne'], 2) }}/20
                                                    </span>
                                                </td>
                                                <td class="text-center">{{ $data['coefficient'] }}</td>
                                                <td class="text-center">{{ $data['notes'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Détail des notes -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>
                            Détail des notes
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Matière</th>
                                        <th>Type d'évaluation</th>
                                        <th class="text-center">Note</th>
                                        <th>Enseignant</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($notes as $note)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($note->date_evaluation)->format('d/m/Y') }}</td>
                                            <td>{{ $note->matiere->nom }}</td>
                                            <td>
                                                <span class="badge bg-info">{{ ucfirst($note->type_evaluation) }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge {{ $note->note_sur >= 10 ? 'bg-success' : 'bg-danger' }}">
                                                    {{ number_format($note->note_sur, 2) }}/20
                                                </span>
                                            </td>
                                            <td>{{ $note->enseignant->utilisateur->nom ?? 'N/A' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Résumé -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h4>{{ $notes->count() }}</h4>
                                <p class="mb-0">Total des notes</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h4>{{ number_format($notes->avg('note_sur'), 2) }}</h4>
                                <p class="mb-0">Moyenne générale</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h4>{{ $notes->where('note_sur', '>=', 10)->count() }}</h4>
                                <p class="mb-0">Notes ≥ 10</p>
                            </div>
                        </div>
                    </div>
                </div>

            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Aucune note trouvée pour cette période.
                </div>
            @endif
        </div>
    </div>
</div>

<style>
@media print {
    .btn-toolbar, .btn {
        display: none !important;
    }
    
    .card {
        border: 1px solid #000 !important;
        break-inside: avoid;
    }
    
    .table {
        font-size: 12px;
    }
}
</style>
@endsection
