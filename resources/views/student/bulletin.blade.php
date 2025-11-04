@extends('layouts.app')

@section('title', 'Mon Bulletin')

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

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-file-alt me-2"></i>
        Mon Bulletin
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Retour au Dashboard
        </a>
    </div>
</div>

<!-- Informations élève -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="fas fa-user-graduate me-2"></i>
                    Informations Élève
                </h5>
                <p><strong>Nom :</strong> {{ $eleve->nom_complet }}</p>
                <p><strong>Matricule :</strong> {{ $eleve->numero_etudiant }}</p>
                <p><strong>Date de naissance :</strong> {{ $eleve->utilisateur->date_naissance ? \Carbon\Carbon::parse($eleve->utilisateur->date_naissance)->format('d/m/Y') : 'Non renseignée' }}</p>
                @if($eleve->classe)
                    <p><strong>Classe :</strong> <span class="badge bg-primary">{{ $eleve->classe->nom }}</span></p>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="fas fa-info-circle me-2"></i>
                    Période
                </h5>
                <form method="GET" action="{{ route('student.bulletin') }}" class="row g-2 align-items-end">
                    <div class="col-12 col-sm-6">
                        <label for="periode" class="form-label">Choisir la période</label>
                        <select id="periode" name="periode" class="form-select" onchange="this.form.submit()">
                            <option value="trimestre1" {{ ($periode ?? 'trimestre1') == 'trimestre1' ? 'selected' : '' }}>Trimestre 1</option>
                            <option value="trimestre2" {{ ($periode ?? 'trimestre1') == 'trimestre2' ? 'selected' : '' }}>Trimestre 2</option>
                            <option value="trimestre3" {{ ($periode ?? 'trimestre1') == 'trimestre3' ? 'selected' : '' }}>Trimestre 3</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label">Infos</label>
                        <div>
                            <small class="text-muted d-block"><strong>Date d'édition :</strong> {{ now()->format('d/m/Y') }}</small>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@if(count($moyennesParMatiere) > 0)
    <!-- Bulletin par matière -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-chart-bar me-2"></i>
                Notes par matière
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Matière</th>
                            <th>Nombre de notes</th>
                            <th>Moyenne</th>
                            <th>Détail des notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($moyennesParMatiere as $matiere => $data)
                        <tr>
                            <td>
                                <strong>{{ $matiere }}</strong>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $data['total_notes'] }}</span>
                            </td>
                            <td>
                                @php
                                    $appreciation = $eleve->classe->getAppreciation($data['moyenne']);
                                @endphp
                                <span class="badge bg-{{ $appreciation['color'] }}">
                                    {{ number_format($data['moyenne'], 2) }}/{{ $eleve->classe->note_max }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($data['notes'] as $note)
                                        @php
                                            $noteAppreciation = $eleve->classe->getAppreciation($note->note_finale ?? 0);
                                        @endphp
                                        <span class="badge bg-{{ $noteAppreciation['color'] }}">
                                            {{ $note->note_finale ?? 'N/A' }}/{{ $eleve->classe->note_max }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Moyenne générale -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <i class="fas fa-calculator fa-3x mb-3"></i>
                    <h3>
                        @php
                            $moyenneGenerale = collect($moyennesParMatiere)->avg('moyenne');
                        @endphp
                        {{ number_format($moyenneGenerale, 2) }}/{{ $eleve->classe->note_max }}
                    </h3>
                    <p class="mb-0">Moyenne Générale</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-{{ $moyenneGenerale >= 16 ? 'success' : ($moyenneGenerale >= 12 ? 'warning' : 'danger') }} text-white">
                <div class="card-body text-center">
                    <i class="fas fa-trophy fa-3x mb-3"></i>
                    <h3>
                        @if($moyenneGenerale >= 16)
                            Excellent
                        @elseif($moyenneGenerale >= 14)
                            Très Bien
                        @elseif($moyenneGenerale >= 12)
                            Bien
                        @elseif($moyenneGenerale >= 10)
                            Assez Bien
                        @else
                            Insuffisant
                        @endif
                    </h3>
                    <p class="mb-0">Appréciation</p>
                </div>
            </div>
        </div>
    </div>
@else
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        <strong>Aucune note trouvée.</strong> 
        Vous n'avez pas encore de notes enregistrées pour générer un bulletin.
    </div>
@endif

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
                        <a href="{{ route('student.notes') }}" class="btn btn-success btn-block">
                            <i class="fas fa-chart-line me-2"></i>
                            Mes Notes
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('student.absences') }}" class="btn btn-warning btn-block">
                            <i class="fas fa-clock me-2"></i>
                            Mes Absences
                        </a>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-info btn-block" onclick="window.print()">
                            <i class="fas fa-print me-2"></i>
                            Imprimer Bulletin
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
    .card {
        border: 1px solid #000 !important;
    }
    .table {
        border-collapse: collapse !important;
    }
    .table th, .table td {
        border: 1px solid #000 !important;
    }
}
</style>
@endpush
@endsection




