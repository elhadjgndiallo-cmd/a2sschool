@extends('layouts.app')

@section('title', 'Mon Bulletin')

@section('content')
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
                <p><strong>Année scolaire :</strong> {{ now()->year }}-{{ now()->year + 1 }}</p>
                <p><strong>Trimestre :</strong> {{ now()->month <= 3 ? '1er' : (now()->month <= 6 ? '2ème' : '3ème') }}</p>
                <p><strong>Date d'édition :</strong> {{ now()->format('d/m/Y') }}</p>
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
                                <span class="badge bg-{{ $data['moyenne'] >= 16 ? 'success' : ($data['moyenne'] >= 12 ? 'warning' : 'danger') }}">
                                    {{ number_format($data['moyenne'], 2) }}/20
                                </span>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($data['notes'] as $note)
                                        <span class="badge bg-{{ ($note->note_finale ?? 0) >= 16 ? 'success' : (($note->note_finale ?? 0) >= 12 ? 'warning' : 'danger') }}">
                                            {{ $note->note_finale ?? 'N/A' }}/20
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
                        {{ number_format($moyenneGenerale, 2) }}/20
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




