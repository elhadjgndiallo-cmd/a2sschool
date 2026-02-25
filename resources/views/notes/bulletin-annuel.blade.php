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
            <a href="{{ route('notes.eleve', $eleve->id) }}" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-1"></i>
                Retour aux Notes
            </a>
            <a href="{{ route('notes.bulletins.annuel.pdf', $classe->id) }}?eleve={{ $eleve->id }}" class="btn btn-primary" target="_blank">
                <i class="fas fa-download me-1"></i>
                Télécharger PDF
            </a>
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
                <div class="col-md-6">
                    <strong>Nom & Prénom:</strong> {{ $eleve->utilisateur->nom }} {{ $eleve->utilisateur->prenom }}
                </div>
                <div class="col-md-6">
                    <strong>Classe:</strong> {{ $classe->nom }}
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
                        @foreach($notesParPeriode as $periode => $notes)
                            @foreach($notes as $index => $note)
                                @if($loop->first)
                                <tr>
                                    <td><strong>{{ $note->matiere->nom }}</strong></td>
                                    @foreach($periodes as $p)
                                        @php
                                        $notePeriode = $notesParPeriode[$p]->where('matiere_id', $note->matiere_id)->first();
                                        @endphp
                                        <td class="text-center">
                                            {{ $notePeriode ? number_format($notePeriode->note_finale, 2) : '-' }}
                                        </td>
                                        <td class="text-center">
                                            {{ $notePeriode ? $notePeriode->coefficient : '-' }}
                                        </td>
                                    @endforeach
                                    <td class="text-center">
                                        @php
                                        $totalPoints = 0;
                                        $totalCoeffs = 0;
                                        @foreach($periodes as $p)
                                            @php
                                            $n = $notesParPeriode[$p]->where('matiere_id', $note->matiere_id)->first();
                                            if($n && $n->note_finale !== null) {
                                                $totalPoints += $n->note_finale * ($n->coefficient ?? 1);
                                                $totalCoeffs += ($n->coefficient ?? 1);
                                            }
                                            @endphp
                                        @endforeach
                                        $moyenneAnnuelleMat = $totalCoeffs > 0 ? $totalPoints / $totalCoeffs : 0;
                                        @endphp
                                        {{ number_format($moyenneAnnuelleMat, 2) }}
                                    </td>
                                </tr>
                                @endif
                            @endforeach
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td><strong>Moyenne Période</strong></td>
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
</div>
@endsection
