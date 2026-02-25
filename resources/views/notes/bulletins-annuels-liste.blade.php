@extends('layouts.app')

@section('title', 'Bulletins Annuels Formatés - ' . $classe->nom)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-chart-line me-2"></i>
            Bulletins Annuels Formatés - {{ $classe->nom }}
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('notes.bulletins') }}" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-1"></i>
                Retour aux Bulletins
            </a>
            <a href="{{ route('notes.bulletins.annuel.pdf', $classe->id) }}" class="btn btn-primary">
                <i class="fas fa-download me-1"></i>
                Télécharger Tous les Bulletins PDF
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-users me-2"></i>
                        Liste des Élèves - {{ $classe->nom }}
                        <span class="badge bg-light text-success ms-2">{{ $classe->eleves->count() }} élève(s)</span>
                    </h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="25%">Nom & Prénoms</th>
                                    <th width="15%">Numéro</th>
                                    <th width="15%">Moyenne Annuelle</th>
                                    <th width="10%">Rang Annuel</th>
                                    <th width="15%">Appréciation</th>
                                    <th width="15%" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                $totalEleves = $classe->eleves->count();
                                @endphp
                                @foreach($classe->eleves as $index => $eleve)
                                    @php
                                    // Calculer les données pour cet élève
                                    $isPrimaire = $classe->isPrimaire();
                                    $periodes = $isPrimaire ? ['trimestre1', 'trimestre2', 'trimestre3'] : ['trimestre1', 'trimestre2'];
                                    
                                    $notesParPeriode = [];
                                    $moyennesParPeriode = [];
                                    $totalCoefficientsParPeriode = [];
                                    
                                    foreach ($periodes as $periode) {
                                        $notes = $eleve->notes()
                                            ->where('periode', $periode)
                                            ->with('matiere')
                                            ->get();
                                            
                                        $notesParPeriode[$periode] = $notes;
                                        
                                        $totalPoints = 0;
                                        $totalCoefficients = 0;
                                        
                                        foreach ($notes as $note) {
                                            if ($note->note_finale !== null) {
                                                $coefficient = $note->coefficient ?? 1;
                                                $totalPoints += $note->note_finale * $coefficient;
                                                $totalCoefficients += $coefficient;
                                            }
                                        }
                                        
                                        $moyennesParPeriode[$periode] = $totalCoefficients > 0 ? $totalPoints / $totalCoefficients : 0;
                                        $totalCoefficientsParPeriode[$periode] = $totalCoefficients;
                                    }
                                    
                                    // Calculer la moyenne annuelle
                                    $totalPointsAnnuel = 0;
                                    $totalCoefficientsAnnuel = 0;
                                    
                                    foreach ($periodes as $periode) {
                                        $totalPointsAnnuel += $moyennesParPeriode[$periode] * $totalCoefficientsParPeriode[$periode];
                                        $totalCoefficientsAnnuel += $totalCoefficientsParPeriode[$periode];
                                    }
                                    
                                    $moyenneAnnuelle = $totalCoefficientsAnnuel > 0 ? $totalPointsAnnuel / $totalCoefficientsAnnuel : 0;
                                    
                                    // Appréciation
                                    $appreciation = '';
                                    if ($moyenneAnnuelle >= 16) {
                                        $appreciation = 'Excellent';
                                        $appreciationColor = 'success';
                                    } elseif ($moyenneAnnuelle >= 14) {
                                        $appreciation = 'Très Bien';
                                        $appreciationColor = 'primary';
                                    } elseif ($moyenneAnnuelle >= 12) {
                                        $appreciation = 'Bien';
                                        $appreciationColor = 'info';
                                    } elseif ($moyenneAnnuelle >= 10) {
                                        $appreciation = 'Assez Bien';
                                        $appreciationColor = 'warning';
                                    } else {
                                        $appreciation = 'À Améliorer';
                                        $appreciationColor = 'danger';
                                    }
                                    
                                    // Rang simplifié
                                    $rang = $index + 1;
                                    @endphp
                                    <tr>
                                        <td class="fw-bold">{{ $index + 1 }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px; font-size: 0.8rem;">
                                                    {{ substr($eleve->utilisateur->nom, 0, 1) }}{{ substr($eleve->utilisateur->prenom, 0, 1) }}
                                                </div>
                                                <div>
                                                    <strong>{{ $eleve->utilisateur->nom }} {{ $eleve->utilisateur->prenom }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $eleve->utilisateur->date_naissance ? \Carbon\Carbon::parse($eleve->utilisateur->date_naissance)->format('d/m/Y') : '' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary">{{ $eleve->numero_etudiant }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="fw-bold {{ $moyenneAnnuelle >= 10 ? 'text-success' : 'text-danger' }}">
                                                {{ number_format($moyenneAnnuelle, 2) }}/{{ $classe->note_max }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info">{{ $rang }}/{{ $totalEleves }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-{{ $appreciationColor }}">{{ $appreciation }}</span>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('notes.bulletin.annuel.formate', $eleve->id) }}" 
                                               class="btn btn-success btn-sm me-1" 
                                               title="Voir Bulletin Annuel Formaté">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('notes.bulletin.annuel.formate', $eleve->id) }}" 
                                               class="btn btn-primary btn-sm" 
                                               title="Télécharger Bulletin Annuel Formaté"
                                               target="_blank">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
