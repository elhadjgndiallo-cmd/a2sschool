@extends('layouts.app')

@section('title', 'Bulletins Annuels Formatés - ' . $classe->nom)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4 no-print">
                <h2><i class="fas fa-chart-line me-2"></i>Bulletins Annuels Formatés - {{ $classe->nom }}</h2>
                <div class="no-print">
                    <button onclick="window.print()" class="btn btn-primary me-2">
                        <i class="fas fa-print me-2"></i>Imprimer
                    </button>
                    <a href="{{ route('notes.bulletins.annuel.pdf', $classe->id) }}" class="btn btn-success me-2">
                        <i class="fas fa-download me-2"></i>Télécharger
                    </a>
                    <a href="{{ route('notes.bulletins') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Retour
                    </a>
                </div>
            </div>

            @php
                $schoolInfo = \App\Helpers\SchoolHelper::getSchoolInfo();
                $logoUrl = $schoolInfo && $schoolInfo->logo ? asset('storage/' . $schoolInfo->logo) : null;
                $schoolName = $schoolInfo && isset($schoolInfo->nom) ? $schoolInfo->nom : 'École';
                $schoolSlogan = $schoolInfo && isset($schoolInfo->slogan) ? $schoolInfo->slogan : '';
            @endphp

            @foreach($bulletins as $bulletin)
            <div class="bulletin-page {{ $loop->first ? 'first-bulletin' : '' }}" style="border: 3px solid #2c3e50; border-radius: 8px; box-sizing: border-box;">
                <div class="card" style="border: none; border-radius: 0; overflow: hidden; height: 100%;">
                    <div class="card-header" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; border: none; padding: 8px 15px; position: relative; width: 100%; box-sizing: border-box;">
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
                        <div class="text-center" style="padding: 0 70px; margin: 6px 0 4px 0 !important; box-sizing: border-box; text-align: center !important; display: block; margin-left: auto !important; margin-right: auto !important; width: calc(100% - 140px); max-width: calc(100% - 140px);">
                            <h4 class="mb-1" style="font-weight: 800; font-size: 1.2rem; text-shadow: 2px 2px 4px rgba(0,0,0,0.5); text-transform: uppercase; letter-spacing: 0.5px; line-height: 1.2; margin: 0 auto 3px auto !important; text-align: center !important; display: block; width: 100% !important;">
                                {{ $schoolName }}
                            </h4>
                            @if($schoolSlogan)
                            <div style="font-size: 0.8rem; font-weight: 500; opacity: 0.95; line-height: 1.2; font-style: italic; margin: 1px auto 0 auto !important; text-align: center !important; display: block; width: 100% !important;">
                                {{ $schoolSlogan }}
                            </div>
                            @endif
                        </div>
                        
                        <div class="border-top border-white border-2 pt-2" style="border-top: 2px solid rgba(255,255,255,0.3) !important; padding-top: 4px !important; margin-top: 6px; padding-left: 0; padding-right: 0;">
                            <div class="row align-items-center" style="margin-left: 0; margin-right: 0;">
                                <div class="col-md-6" style="padding-left: 0; padding-right: 5px;">
                                    <h3 class="mb-0" style="font-weight: 800; text-shadow: 2px 2px 4px rgba(0,0,0,0.5); font-size: 1.1rem; letter-spacing: 0.5px; line-height: 1.2;">BULLETIN ANNUEL</h3>
                                    <div style="font-size: 0.9rem; font-weight: 500; opacity: 0.95; line-height: 1.2; margin-top: 1px;">{{ $classe->nom }} - {{ $classe->niveau }}</div>
                                </div>
                                <div class="col-md-6 text-end" style="padding-left: 5px; padding-right: 0;">
                                    <h4 style="font-weight: 700; font-size: 0.95rem; margin-bottom: 1px; text-shadow: 1px 1px 2px rgba(0,0,0,0.5); line-height: 1.2;">Année Scolaire {{ $anneeScolaireActive ? $anneeScolaireActive->nom : (date('Y') . '-' . (date('Y')+1)) }}</h4>
                                    <div style="font-size: 0.9rem; font-weight: 500; opacity: 0.95; line-height: 1.2;">
                                        Tous les Trimestres
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body" style="padding: 10px; background: white;">
                        <!-- Informations élève -->
                        <div class="row mb-2" style="margin-bottom: 2px !important;">
                            <div class="col-md-6">
                                <h5 style="font-size: 1rem; margin-bottom: 3px; font-weight: 800; color: #2c3e50; line-height: 1.2;"><strong>{{ $bulletin['eleve']->nom_complet }}</strong></h5>
                                <p class="mb-1" style="font-size: 0.9rem; margin-bottom: 2px; font-weight: 600; line-height: 1.2;"><strong>Numéro:</strong> <span style="font-weight: 500;">{{ $bulletin['eleve']->numero_etudiant }}</span></p>
                                <p class="mb-1" style="font-size: 0.9rem; margin-bottom: 2px; font-weight: 600; line-height: 1.2;"><strong>Date de naissance:</strong> <span style="font-weight: 500;">{{ $bulletin['eleve']->utilisateur->date_naissance ? \Carbon\Carbon::parse($bulletin['eleve']->utilisateur->date_naissance)->format('d/m/Y') : 'Non renseignée' }}</span></p>
                            </div>
                            <div class="col-md-6 text-end">
                                <div style="display: flex; align-items: center; justify-content: flex-end; gap: 8px;">
                                    <div style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 4px 8px; border-radius: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.2); display: inline-block;">
                                        <h5 class="mb-0" style="font-weight: 800; font-size: 0.9rem; margin-bottom: 1px; text-shadow: 1px 1px 2px rgba(0,0,0,0.3); line-height: 1.1;">Rang Annuel: {{ $bulletin['rang'] }}/{{ count($bulletins) }}</h5>
                                        <p class="mb-0" style="font-size: 0.9rem; font-weight: 600; line-height: 1.1;">Moyenne: <strong>{{ number_format($bulletin['moyenneAnnuelle'], 2) }}/{{ $bulletin['eleve']->classe->note_max }}</strong></p>
                                    </div>
                                    <!-- QR Code de vérification -->
                                    <div style="background: white; padding: 3px; border-radius: 3px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                        {!! QrCode::size(60)->generate($bulletin['verification_url'] ?? '#') !!}
                                    </div>
                                </div>
                                <div style="text-align: right; margin-top: 2px; font-size: 0.6rem; color: #6c757d;">
                                    <small>Scannez pour vérifier</small>
                                </div>
                            </div>
                        </div>

                        <!-- Tableau des matières par trimestre -->
                        <div class="table-responsive">
                            <table class="table table-bordered" style="margin-bottom: 1px; font-size: 0.7rem;">
                                <thead style="background: linear-gradient(135deg, #34495e 0%, #2c3e50 100%); color: white;">
                                    <tr>
                                        <th style="font-weight: 700; border: 1px solid #2c3e50; font-size: 0.7rem; padding: 2px 3px;">Matière</th>
                                        <th style="font-weight: 700; border: 1px solid #2c3e50; text-align: center; font-size: 0.7rem; padding: 2px 3px;">Coef.</th>
                                        <th style="font-weight: 700; border: 1px solid #2c3e50; text-align: center; font-size: 0.7rem; padding: 2px 3px;">Trimestre 1</th>
                                        <th style="font-weight: 700; border: 1px solid #2c3e50; text-align: center; font-size: 0.7rem; padding: 2px 3px;">Trimestre 2</th>
                                        <th style="font-weight: 700; border: 1px solid #2c3e50; text-align: center; font-size: 0.7rem; padding: 2px 3px;">Trimestre 3</th>
                                        <th style="font-weight: 700; border: 1px solid #2c3e50; text-align: center; font-size: 0.7rem; padding: 2px 3px;">Moy. Annuelle</th>
                                        <th style="font-weight: 700; border: 1px solid #2c3e50; text-align: center; font-size: 0.7rem; padding: 2px 3px;">Mention</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php 
                                    $totalPointsAnnuel = 0; 
                                    $totalCoeffAnnuel = 0;
                                    $matieresGrouped = [];
                                    
                                    // Grouper les notes par matière
                                    foreach($bulletin['notesParPeriode'] as $periode => $notes) {
                                        foreach($notes as $note) {
                                            $matiereId = $note->matiere->id;
                                            if(!isset($matieresGrouped[$matiereId])) {
                                                $matieresGrouped[$matiereId] = [
                                                    'matiere' => $note->matiere,
                                                    'coefficient' => $note->coefficient ?? 1,
                                                    'notes' => []
                                                ];
                                            }
                                            $matieresGrouped[$matiereId]['notes'][$periode] = $note;
                                        }
                                    }
                                    @endphp
                                    
                                    @foreach($matieresGrouped as $matiereId => $matiereData)
                                        @php 
                                        $matiere = $matiereData['matiere'];
                                        $coefficient = $matiereData['coefficient'];
                                        
                                        $noteT1 = $matiereData['notes']['trimestre1'] ?? null;
                                        $noteT2 = $matiereData['notes']['trimestre2'] ?? null;
                                        $noteT3 = $matiereData['notes']['trimestre3'] ?? null;
                                        
                                        $noteT1Finale = $noteT1 ? $noteT1->note_finale : null;
                                        $noteT2Finale = $noteT2 ? $noteT2->note_finale : null;
                                        $noteT3Finale = $noteT3 ? $noteT3->note_finale : null;
                                        
                                        // Calculer la moyenne annuelle par matière
                                        $notesValides = array_filter([$noteT1Finale, $noteT2Finale, $noteT3Finale]);
                                        $moyenneMatiere = count($notesValides) > 0 ? array_sum($notesValides) / count($notesValides) : 0;
                                        
                                        $pointsAnnuels = $moyenneMatiere * $coefficient;
                                        $totalPointsAnnuel += $pointsAnnuels;
                                        $totalCoeffAnnuel += $coefficient;
                                        
                                        // Déterminer la mention
                                        $noteMax = $bulletin['eleve']->classe->note_max;
                                        $mention = '';
                                        if($moyenneMatiere >= ($noteMax * 0.8)) {
                                            $mention = 'Excellent';
                                        } elseif($moyenneMatiere >= ($noteMax * 0.7)) {
                                            $mention = 'Très Bien';
                                        } elseif($moyenneMatiere >= ($noteMax * 0.6)) {
                                            $mention = 'Bien';
                                        } elseif($moyenneMatiere >= ($noteMax * 0.5)) {
                                            $mention = 'Assez Bien';
                                        } else {
                                            $mention = 'À Améliorer';
                                        }
                                        @endphp
                                        <tr>
                                            <td style="font-weight: 600; border: 1px solid #dee2e6; padding: 1px 2px; font-size: 0.65rem;">{{ $matiere->nom }}</td>
                                            <td style="text-align: center; border: 1px solid #dee2e6; padding: 1px 2px; font-size: 0.65rem;">{{ $coefficient }}</td>
                                            <td style="text-align: center; border: 1px solid #dee2e6; padding: 1px 2px; font-size: 0.65rem; font-weight: 600; {{ $noteT1Finale >= ($noteMax * 0.5) ? 'color: #155724;' : 'color: #dc3545;' }}">
                                                {{ $noteT1Finale ? number_format($noteT1Finale, 2) : '-' }}
                                            </td>
                                            <td style="text-align: center; border: 1px solid #dee2e6; padding: 1px 2px; font-size: 0.65rem; font-weight: 600; {{ $noteT2Finale >= ($noteMax * 0.5) ? 'color: #155724;' : 'color: #dc3545;' }}">
                                                {{ $noteT2Finale ? number_format($noteT2Finale, 2) : '-' }}
                                            </td>
                                            <td style="text-align: center; border: 1px solid #dee2e6; padding: 1px 2px; font-size: 0.65rem; font-weight: 600; {{ $noteT3Finale >= ($noteMax * 0.5) ? 'color: #155724;' : 'color: #dc3545;' }}">
                                                {{ $noteT3Finale ? number_format($noteT3Finale, 2) : '-' }}
                                            </td>
                                            <td style="text-align: center; border: 1px solid #dee2e6; padding: 1px 2px; font-size: 0.65rem; font-weight: 700; color: #28a745; background: #f8f9fa;">
                                                {{ number_format($moyenneMatiere, 2) }}
                                            </td>
                                            <td style="text-align: center; border: 1px solid #dee2e6; padding: 1px 2px; font-size: 0.6rem;">
                                                <span class="badge" style="font-size: 0.5rem; padding: 1px 2px; 
                                                    @if($mention == 'Excellent') background-color: #28a745; @elseif($mention == 'Très Bien') background-color: #007bff; @elseif($mention == 'Bien') background-color: #17a2b8; @elseif($mention == 'Assez Bien') background-color: #ffc107; color: #212529; @else background-color: #dc3545; @endif">
                                                    {{ $mention }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot style="background: #f8f9fa;">
                                    <tr>
                                        <td colspan="2" style="font-weight: 700; text-align: right; padding: 2px 3px; font-size: 0.7rem;">TOTAL ANNUEL</td>
                                        <td colspan="4" style="font-weight: 700; text-align: center; padding: 2px 3px; font-size: 0.7rem;">-</td>
                                        <td style="font-weight: 700; text-align: center; padding: 2px 3px; font-size: 0.7rem;">{{ $totalCoeffAnnuel }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" style="font-weight: 700; text-align: right; padding: 2px 3px; font-size: 0.75rem;">MOYENNE ANNUELLE</td>
                                        <td style="font-weight: 700; text-align: center; padding: 2px 3px; font-size: 0.8rem; color: #28a745;">
                                            {{ number_format($bulletin['moyenneAnnuelle'], 2) }}/{{ $bulletin['eleve']->classe->note_max }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Tableau des rangs par trimestre -->
                        <div class="mt-3">
                            <h5 style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%); color: white; padding: 4px 8px; border-radius: 3px; margin-bottom: 6px; font-weight: 700; text-align: center; font-size: 0.85rem;">
                                RANGS PAR TRIMESTRE ET ANNUEL
                            </h5>
                            <table class="table table-bordered" style="font-size: 0.75rem;">
                                <thead style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%); color: white;">
                                    <tr>
                                        <th style="font-weight: 700; border: 1px solid #495057; text-align: center; font-size: 0.75rem; padding: 2px 3px;">Trimestre 1</th>
                                        <th style="font-weight: 700; border: 1px solid #495057; text-align: center; font-size: 0.75rem; padding: 2px 3px;">Trimestre 2</th>
                                        <th style="font-weight: 700; border: 1px solid #495057; text-align: center; font-size: 0.75rem; padding: 2px 3px;">Trimestre 3</th>
                                        <th style="font-weight: 700; border: 1px solid #495057; text-align: center; font-size: 0.75rem; padding: 2px 3px;">RANG ANNUEL</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="text-align: center; border: 1px solid #dee2e6; padding: 3px; font-size: 0.85rem; font-weight: 700; color: #007bff;">
                                            {{ $bulletin['rangsParPeriode']['trimestre1'] ?? '-' }}/{{ count($bulletins) }}
                                        </td>
                                        <td style="text-align: center; border: 1px solid #dee2e6; padding: 3px; font-size: 0.85rem; font-weight: 700; color: #007bff;">
                                            {{ $bulletin['rangsParPeriode']['trimestre2'] ?? '-' }}/{{ count($bulletins) }}
                                        </td>
                                        <td style="text-align: center; border: 1px solid #dee2e6; padding: 3px; font-size: 0.85rem; font-weight: 700; color: #007bff;">
                                            {{ $bulletin['rangsParPeriode']['trimestre3'] ?? '-' }}/{{ count($bulletins) }}
                                        </td>
                                        <td style="text-align: center; border: 1px solid #dee2e6; padding: 3px; font-size: 0.85rem; font-weight: 700; color: #28a745; background: #f8f9fa;">
                                            {{ $bulletin['rang'] }}/{{ count($bulletins) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Signature -->
                        <div class="mt-2">
                            <div class="row">
                                <div class="col-md-12">
                                    <div style="text-align: right;">
                                        <div class="d-inline-block" style="margin-right: 30px;">
                                            <div style="border-top: 1px solid #333; margin-top: 15px; padding-top: 5px;">
                                                <p style="margin: 0; font-size: 0.65rem; font-weight: 600;">Le Directeur</p>
                                                <p style="margin: 0; font-size: 0.55rem; color: #666;">Signature et Cachet</p>
                                            </div>
                                        </div>
                                        <div class="d-inline-block">
                                            <div style="border-top: 1px solid #333; margin-top: 15px; padding-top: 5px;">
                                                <p style="margin: 0; font-size: 0.65rem; font-weight: 600;">Appréciation</p>
                                                <p style="margin: 0; font-size: 0.55rem; color: #666;">Signature</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-12">
                                    <div style="text-align: right;">
                                        <p style="margin: 0; font-size: 0.7rem; color: #6c757d;">
                                            <strong>Fait à Conakry, le {{ \Carbon\Carbon::now()->format('d/m/Y') }}</strong>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            @if(!$loop->last)
            <div style="page-break-after: always;"></div>
            @endif
            @endforeach
        </div>
    </div>
</div>

<style>
/* Styles pour l'impression A4 */
@media print {
    @page {
        size: A4 portrait;
        margin: 0.5cm;
    }
    
    /* Masquer les éléments non nécessaires */
    .no-print,
    nav,
    .navbar,
    header,
    footer,
    .btn-toolbar,
    .btn,
    button,
    .d-flex.justify-content-between {
        display: none !important;
    }
    
    /* Afficher les bulletins */
    .bulletin-page {
        display: block !important;
        page-break-inside: avoid;
        page-break-after: always;
        margin-bottom: 0;
        border: 3px solid #2c3e50 !important;
        border-radius: 8px;
        background: white;
        overflow: hidden;
        width: 100%;
        min-height: 29.7cm;
    }
    
    .bulletin-page:last-child {
        page-break-after: auto;
    }
    
    .bulletin-page .card {
        border: none;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    
    .bulletin-page .card-header {
        flex-shrink: 0;
    }
    
    .bulletin-page .card-body {
        flex: 1;
        padding: 10px;
    }
    
    /* Réduire les tailles pour A4 portrait */
    body {
        font-size: 10px;
    }
    
    .bulletin-page .card-header h4 {
        font-size: 1.1rem !important;
    }
    
    .bulletin-page .card-header h3 {
        font-size: 1rem !important;
    }
    
    .bulletin-page .card-header h5 {
        font-size: 0.9rem !important;
    }
    
    .bulletin-page table {
        font-size: 0.7rem !important;
    }
    
    .bulletin-page table th,
    .bulletin-page table td {
        font-size: 0.7rem !important;
        padding: 2px 3px !important;
    }
    
    .bulletin-page h4 {
        font-size: 0.8rem !important;
    }
    
    .bulletin-page h5 {
        font-size: 0.85rem !important;
    }
    
    /* Conservation des couleurs */
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
}
</style>
@endsection
