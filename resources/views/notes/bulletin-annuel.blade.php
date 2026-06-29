@extends('layouts.app')

@section('title', 'Bulletin Annuel - ' . $eleve->utilisateur->nom . ' ' . $eleve->utilisateur->prenom)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-file-alt me-2"></i>
            Bulletin Annuel
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <button onclick="window.print()" class="btn btn-primary me-2">
                <i class="fas fa-print me-1"></i>
                Imprimer
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <!-- En-tête du bulletin -->
            <div class="row mb-4">
                <div class="col-md-12 text-center">
                    <h4>ÉCOLE PRIMAIRE SECONDAIRE</h4>
                    <h5>BULLETIN ANNUEL</h5>
                    <h6>Année Scolaire {{ $anneeScolaireActive->annee ?? '2024-2025' }}</h6>
                </div>
            </div>

            <!-- Informations élève -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <strong>Nom & Prénom:</strong> {{ $eleve->utilisateur->nom }} {{ $eleve->utilisateur->prenom }}
                </div>
                <div class="col-md-4">
                    <strong>Classe:</strong> <span class="badge bg-primary">{{ $classe->nom }}</span>
                </div>
                <div class="col-md-4">
                    <strong>Matricule:</strong> {{ $eleve->matricule ?? $eleve->numero_etudiant ?? 'N/A' }}
                </div>
            </div>

            <!-- Tableau des notes par période -->
            <div class="table-responsive mb-4">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th rowspan="2" class="align-middle">Matières</th>
                            @foreach($periodes as $periode)
                            <th colspan="2" class="text-center">
                                {{ ucfirst(str_replace('trimestre', 'Trimestre ', $periode)) }}
                            </th>
                            @endforeach
                            <th rowspan="2" class="align-middle text-center">Moyenne Annuelle</th>
                        </tr>
                        <tr>
                            @foreach($periodes as $periode)
                            <th class="text-center">Note</th>
                            <th class="text-center">Coeff</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $matieresGrouped = [];
                            foreach ($notesParPeriode as $notes) {
                                foreach ($notes as $note) {
                                    $matieresGrouped[$note->matiere_id] = $note->matiere;
                                }
                            }
                        @endphp
                        @foreach($matieresGrouped as $matiereId => $matiere)
                                <tr>
                                    <td><strong>{{ $matiere->nom }}</strong></td>
                                    @foreach($periodes as $p)
                                        @php
                                        $noteFinalePeriode = $notesFinalesParMatiereParPeriode[$matiereId][$p] ?? null;
                                        $notePeriode = isset($notesParPeriode[$p]) ? $notesParPeriode[$p]->where('matiere_id', $matiereId)->first() : null;
                                        @endphp
                                        <td class="text-center">
                                            {{ $noteFinalePeriode !== null ? number_format($noteFinalePeriode, 2) : '-' }}
                                        </td>
                                        <td class="text-center">
                                            {{ $notePeriode ? ($notePeriode->coefficient ?? 1) : '-' }}
                                        </td>
                                    @endforeach
                                    <td class="text-center">
                                        {{ number_format($moyennesAnnuellesParMatiere[$matiereId] ?? 0, 2) }}
                                    </td>
                                </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td><strong>Moyenne Générale</strong></td>
                            @foreach($periodes as $periode)
                            <td colspan="2" class="text-center">
                                <strong>{{ number_format($moyennesParPeriode[$periode], 2) }}</strong>
                            </td>
                            @endforeach
                            <td class="text-center">
                                <strong>{{ number_format($moyenneAnnuelle, 2) }}</strong>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Résultats annuels -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title">Résultats Annuels</h6>
                            <p><strong>Moyenne Générale Annuelle:</strong> {{ number_format($moyenneAnnuelle, 2) }}/20</p>
                            <p><strong>Rang dans la classe:</strong> {{ $rangAnnuel }}/{{ $eleve->classe->eleves->count() }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title">Appréciation Générale</h6>
                            <p>
                                @if($moyenneAnnuelle >= 16)
                                    <span class="badge bg-success">Excellent</span>
                                @elseif($moyenneAnnuelle >= 14)
                                    <span class="badge bg-primary">Très Bien</span>
                                @elseif($moyenneAnnuelle >= 12)
                                    <span class="badge bg-info">Bien</span>
                                @elseif($moyenneAnnuelle >= 10)
                                    <span class="badge bg-warning">Assez Bien</span>
                                @else
                                    <span class="badge bg-danger">À Améliorer</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Signature et vérification -->
            <div class="row mt-5">
                <div class="col-md-6">
                    <p><strong>Date de génération:</strong> {{ now()->format('d/m/Y H:i') }}</p>
                </div>
                <div class="col-md-6 text-end">
                    <small class="text-muted">
                        Code de vérification: {{ substr($token, 0, 10) }}...
                        <a href="{{ $verificationUrl }}" target="_blank" class="text-primary">Vérifier</a>
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Ligne en bas du bulletin -->
    <div style="border-top: 2px solid #000; margin-top: 20px; padding-top: 10px;"></div>
</div>

@endsection
