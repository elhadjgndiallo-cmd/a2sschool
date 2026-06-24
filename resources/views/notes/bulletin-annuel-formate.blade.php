@extends('layouts.app')

@section('title', 'Bulletin Annuel - ' . $eleve->utilisateur->nom . ' ' . $eleve->utilisateur->prenom)

@section('styles')
<link rel="stylesheet" href="{{ asset('css/print-bulletin.css') }}">
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom no-print">
        <h1 class="h2">
            <i class="fas fa-file-alt me-2"></i>
            Bulletin Annuel
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <button onclick="window.print()" class="btn btn-primary me-2">
                <i class="fas fa-print me-1"></i>
                Imprimer
            </button>
            <a href="{{ route('notes.eleve', $eleve->id) }}" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-1"></i>
                Retour aux Notes
            </a>
        </div>
    </div>

    @php
        $schoolInfo = \App\Helpers\SchoolHelper::getSchoolInfo();
        $logoUrl = $schoolInfo && $schoolInfo->logo ? asset('storage/' . $schoolInfo->logo) : null;
        $schoolName = $schoolInfo && isset($schoolInfo->nom) ? $schoolInfo->nom : 'École';
        $schoolSlogan = $schoolInfo && isset($schoolInfo->slogan) ? $schoolInfo->slogan : '';
        $anneeScolaireActive = \App\Models\AnneeScolaire::anneeActive();
        $headerBg = '#1a5490';
        $headerText = '#ffffff';
    @endphp

    @foreach($periodes as $periode)
    <div class="card mb-4">
        <div class="card-header" style="background: linear-gradient(135deg, {{ $headerBg }} 0%, {{ $headerBg }} 100%); color: {{ $headerText }}; border: none; padding: 8px 15px; position: relative;">
            <!-- Logo aux angles -->
            <div style="position: absolute; top: 8px; left: 15px;">
                @if($logoUrl)
                <img src="{{ $logoUrl }}" alt="Logo de l'école" style="max-width: 50px; max-height: 50px; object-fit: contain; background: white; padding: 4px; border-radius: 5px; display: block;">
                @endif
            </div>
            <div style="position: absolute; top: 8px; right: 15px;">
                @if($logoUrl)
                <img src="{{ $logoUrl }}" alt="Logo de l'école" style="max-width: 50px; max-height: 50px; object-fit: contain; background: white; padding: 4px; border-radius: 5px; display: block;">
                @endif
            </div>
            
            <!-- Nom de l'école et slogan au centre -->
            <div class="text-center" style="padding: 0 70px; margin-bottom: 6px !important; margin-top: 8px;">
                <h4 class="mb-1" style="font-weight: 800; font-size: 1.2rem; text-shadow: 2px 2px 4px rgba(0,0,0,0.5); text-transform: uppercase; letter-spacing: 0.5px; line-height: 1.2; margin-bottom: 4px !important;">
                    {{ $schoolName }}
                </h4>
                @if($schoolSlogan)
                <div style="font-size: 0.75rem; font-weight: 500; opacity: 0.95; line-height: 1.2; font-style: italic; margin-top: 2px;">
                    {{ $schoolSlogan }}
                </div>
                @endif
            </div>
            
            <div class="border-top border-white border-2 pt-2" style="border-top: 2px solid rgba(255,255,255,0.3) !important; padding-top: 6px !important; margin-top: 8px; padding-left: 0; padding-right: 0;">
                <div class="row align-items-center" style="margin-left: 0; margin-right: 0;">
                    <div class="col-md-6" style="padding-left: 0; padding-right: 5px;">
                        <h3 class="mb-0" style="font-weight: 800; text-shadow: 2px 2px 4px rgba(0,0,0,0.5); font-size: 1.05rem; letter-spacing: 0.5px; line-height: 1.2;">BULLETIN DE NOTES</h3>
                        <div style="font-size: 0.85rem; font-weight: 500; opacity: 0.95; line-height: 1.2; margin-top: 2px;">{{ $eleve->classe->nom }} - {{ $eleve->classe->niveau }}</div>
                    </div>
                    <div class="col-md-6 text-end" style="padding-left: 5px; padding-right: 0;">
                        <h4 style="font-weight: 700; font-size: 0.95rem; margin-bottom: 2px; text-shadow: 1px 1px 2px rgba(0,0,0,0.5); line-height: 1.2;">Année Scolaire {{ $anneeScolaireActive ? $anneeScolaireActive->nom : (date('Y') . '-' . (date('Y')+1)) }}</h4>
                        <div style="font-size: 0.85rem; font-weight: 500; opacity: 0.95; line-height: 1.2;">
                            @if($periode == 'trimestre1')
                                Trimestre 1
                            @elseif($periode == 'trimestre2')
                                Trimestre 2
                            @elseif($periode == 'trimestre3')
                                Trimestre 3
                            @else
                                {{ ucfirst($periode) }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card-body" style="display: flex; flex-direction: column; flex: 1;">
            <!-- Informations élève -->
            <div class="row mb-2" style="margin-bottom: 8px !important;">
                <div class="col-md-6">
                    <h5 style="font-size: 1.05rem; margin-bottom: 4px; font-weight: 800; color: #2c3e50; line-height: 1.2;"><strong>{{ $eleve->nom_complet }}</strong></h5>
                    <p class="mb-1" style="font-size: 0.9rem; margin-bottom: 3px; font-weight: 600; line-height: 1.2;"><strong>Classe:</strong> <span class="badge bg-primary" style="font-size: 0.9rem;">{{ $eleve->classe->nom }}</span></p>
                    <p class="mb-1" style="font-size: 0.9rem; margin-bottom: 3px; font-weight: 600; line-height: 1.2;"><strong>Numéro:</strong> <span style="font-weight: 500;">{{ $eleve->numero_etudiant }}</span></p>
                    <p class="mb-1" style="font-size: 0.9rem; margin-bottom: 3px; font-weight: 600; line-height: 1.2;"><strong>Date de naissance:</strong> <span style="font-weight: 500;">{{ $eleve->utilisateur->date_naissance ? \Carbon\Carbon::parse($eleve->utilisateur->date_naissance)->format('d/m/Y') : 'Non renseignée' }}</span></p>
                </div>
                <div class="col-md-6 text-end">
                    <div style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: white; padding: 8px 12px; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.2); display: inline-block;">
                        @php
                        // Calcul simple du rang pour cette période
                        $rangPeriode = 1; // Valeur par défaut
                        @endphp
                        <h5 class="mb-0" style="font-weight: 800; font-size: 0.95rem; margin-bottom: 3px; text-shadow: 1px 1px 2px rgba(0,0,0,0.3); line-height: 1.2;">Rang: {{ $rangPeriode }}/{{ $eleve->classe->eleves->count() }}</h5>
                        <p class="mb-0" style="font-size: 0.95rem; font-weight: 600; line-height: 1.2;">Moyenne: <strong>{{ number_format($moyennesParPeriode[$periode], 2) }}/{{ $eleve->classe->note_max }}</strong></p>
                    </div>
                </div>
            </div>

            <!-- Tableau des notes par matière -->
            <div class="table-responsive">
                <table class="table table-bordered" style="margin-bottom: 8px;">
                    <thead style="background: linear-gradient(135deg, #34495e 0%, #2c3e50 100%); color: white;">
                        <tr>
                            <th style="font-weight: 700; border: 1px solid #2c3e50; font-size: 0.85rem; padding: 5px 4px;">Matière</th>
                            <th style="font-weight: 700; border: 1px solid #2c3e50; text-align: center; font-size: 0.85rem; padding: 5px 4px;">Coef.</th>
                            @if(!$eleve->classe->isPrimaire())
                            <th style="font-weight: 700; border: 1px solid #2c3e50; text-align: center; font-size: 0.85rem; padding: 5px 4px;">Cours</th>
                            @endif
                            <th style="font-weight: 700; border: 1px solid #2c3e50; text-align: center; font-size: 0.85rem; padding: 5px 4px;">Comp.</th>
                            <th style="font-weight: 700; border: 1px solid #2c3e50; text-align: center; font-size: 0.85rem; padding: 5px 4px;">Finale</th>
                            @if(!$eleve->classe->isPrimaire())
                            <th style="font-weight: 700; border: 1px solid #2c3e50; text-align: center; font-size: 0.85rem; padding: 5px 4px;">Points</th>
                            @endif
                            <th style="font-weight: 700; border: 1px solid #2c3e50; text-align: center; font-size: 0.85rem; padding: 5px 4px;">Mention</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalPoints = 0; $totalCoeff = 0; @endphp
                        @foreach($notesParPeriode[$periode] as $note)
                            @php 
                            $totalPoints += $note->note_finale * ($note->coefficient ?? 1);
                            $totalCoeff += ($note->coefficient ?? 1);
                            
                            // Déterminer la mention
                            $noteMax = $eleve->classe->note_max;
                            $mention = '';
                            if($note->note_finale >= ($noteMax * 0.8)) {
                                $mention = 'Excellent';
                            } elseif($note->note_finale >= ($noteMax * 0.7)) {
                                $mention = 'Très Bien';
                            } elseif($note->note_finale >= ($noteMax * 0.6)) {
                                $mention = 'Bien';
                            } elseif($note->note_finale >= ($noteMax * 0.5)) {
                                $mention = 'Assez Bien';
                            } else {
                                $mention = 'À Améliorer';
                            }
                            @endphp
                            <tr>
                                <td style="font-weight: 600; border: 1px solid #dee2e6; padding: 4px; font-size: 0.85rem;">{{ $note->matiere->nom }}</td>
                                <td style="text-align: center; border: 1px solid #dee2e6; padding: 4px; font-size: 0.85rem;">{{ $note->coefficient ?? 1 }}</td>
                                @if(!$eleve->classe->isPrimaire())
                                <td style="text-align: center; border: 1px solid #dee2e6; padding: 4px; font-size: 0.85rem;">{{ $note->note_cours ? number_format($note->note_cours, 2) : '-' }}</td>
                                @endif
                                <td style="text-align: center; border: 1px solid #dee2e6; padding: 4px; font-size: 0.85rem;">{{ $note->note_composition ? number_format($note->note_composition, 2) : '-' }}</td>
                                <td style="text-align: center; border: 1px solid #dee2e6; padding: 4px; font-weight: 600; font-size: 0.85rem; color: {{ $note->note_finale >= ($noteMax * 0.5) ? '#155724' : '#dc3545' }};">{{ number_format($note->note_finale, 2) }}</td>
                                @if(!$eleve->classe->isPrimaire())
                                <td style="text-align: center; border: 1px solid #dee2e6; padding: 4px; font-size: 0.85rem;">{{ number_format($note->note_finale * ($note->coefficient ?? 1), 2) }}</td>
                                @endif
                                <td style="text-align: center; border: 1px solid #dee2e6; padding: 4px; font-size: 0.8rem;">
                                    <span class="badge" style="font-size: 0.7rem; padding: 2px 6px; 
                                        @if($mention == 'Excellent') background-color: #28a745; @elseif($mention == 'Très Bien') background-color: #007bff; @elseif($mention == 'Bien') background-color: #17a2b8; @elseif($mention == 'Assez Bien') background-color: #ffc107; color: #212529; @else background-color: #dc3545; @endif">
                                        {{ $mention }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Résumé du trimestre -->
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="card" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border: 1px solid #dee2e6;">
                            <div class="card-body p-3">
                                <h6 class="card-title" style="font-weight: 700; color: #495057; font-size: 0.9rem;">Résumé du {{ ucfirst(str_replace('trimestre', 'Trimestre ', $periode)) }}</h6>
                                <p class="mb-1" style="font-size: 0.85rem; margin-bottom: 2px;"><strong>Total Coefficients:</strong> {{ $totalCoeff }}</p>
                                <p class="mb-1" style="font-size: 0.85rem; margin-bottom: 2px;"><strong>Total Points:</strong> {{ number_format($totalPoints, 2) }}</p>
                                <p class="mb-0" style="font-size: 0.85rem;"><strong>Moyenne Générale:</strong> <span style="font-size: 1rem; font-weight: 700; color: #007bff;">{{ number_format($moyennesParPeriode[$periode], 2) }}/{{ $eleve->classe->note_max }}</span></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card" style="background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); border: 1px solid #c3e6cb;">
                            <div class="card-body p-3">
                                <h6 class="card-title" style="font-weight: 700; color: #155724; font-size: 0.9rem;">Performance</h6>
                                <p class="mb-0" style="font-size: 0.85rem;">
                                    @if($moyennesParPeriode[$periode] >= ($eleve->classe->note_max * 0.8))
                                        <span class="badge" style="background-color: #28a745; font-size: 0.8rem;">Excellent</span>
                                    @elseif($moyennesParPeriode[$periode] >= ($eleve->classe->note_max * 0.7))
                                        <span class="badge" style="background-color: #007bff; font-size: 0.8rem;">Très Bien</span>
                                    @elseif($moyennesParPeriode[$periode] >= ($eleve->classe->note_max * 0.6))
                                        <span class="badge" style="background-color: #17a2b8; font-size: 0.8rem;">Bien</span>
                                    @elseif($moyennesParPeriode[$periode] >= ($eleve->classe->note_max * 0.5))
                                        <span class="badge" style="background-color: #ffc107; color: #212529; font-size: 0.8rem;">Assez Bien</span>
                                    @else
                                        <span class="badge" style="background-color: #dc3545; font-size: 0.8rem;">À Améliorer</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach

    <!-- Résumé Annuel -->
    <div class="card">
        <div class="card-header py-2" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white;">
            <h5 class="mb-0" style="font-weight: 700; font-size: 1rem;">
                <i class="fas fa-chart-line me-2"></i>Résumé Annuel
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="card border-primary">
                        <div class="card-body text-center">
                            <h6 class="card-title text-primary">Moyenne Annuelle</h6>
                            <h3 class="text-primary" style="font-weight: 800; font-size: 2rem;">{{ number_format($moyenneAnnuelle, 2) }}/{{ $eleve->classe->note_max }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-success">
                        <div class="card-body text-center">
                            <h6 class="card-title text-success">Rang Annuel</h6>
                            <h3 class="text-success" style="font-weight: 800; font-size: 2rem;">{{ $rangAnnuel }}/{{ count($bulletins ?? [$eleve]) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-info">
                        <div class="card-body text-center">
                            <h6 class="card-title text-info">Appréciation Générale</h6>
                            <h5 style="font-weight: 700;">
                                @if($moyenneAnnuelle >= 16)
                                    <span class="badge bg-success" style="font-size: 1rem;">Excellent</span>
                                @elseif($moyenneAnnuelle >= 14)
                                    <span class="badge bg-primary" style="font-size: 1rem;">Très Bien</span>
                                @elseif($moyenneAnnuelle >= 12)
                                    <span class="badge bg-info" style="font-size: 1rem;">Bien</span>
                                @elseif($moyenneAnnuelle >= 10)
                                    <span class="badge bg-warning" style="font-size: 1rem;">Assez Bien</span>
                                @else
                                    <span class="badge bg-danger" style="font-size: 1rem;">À Améliorer</span>
                                @endif
                            </h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Signature et vérification -->
    <div class="card mt-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Date de génération:</strong> {{ now()->format('d/m/Y H:i') }}</p>
                    <p><strong>Année Scolaire:</strong> {{ $anneeScolaireActive->annee ?? '2024-2025' }}</p>
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
