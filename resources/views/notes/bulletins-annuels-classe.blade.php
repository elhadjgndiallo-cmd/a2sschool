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
            <div class="bulletin-page" style="border: 3px solid {{ $couleurs['document']['document_border'] ?? '#2c3e50' }}; border-radius: 8px; box-sizing: border-box;">
                <div class="card" style="border: none; border-radius: 0; overflow: hidden; height: 100%;">
                    <div class="card-header" style="background: linear-gradient(135deg, {{ $couleurs['bulletin']['bulletin_header_bg'] ?? '#28a745' }} 0%, {{ $couleurs['bulletin']['bulletin_header_bg'] ?? '#20c997' }} 100%); color: {{ $couleurs['bulletin']['bulletin_header_text'] ?? '#ffffff' }}; border: none; padding: 8px 15px; position: relative; width: 100%; box-sizing: border-box;">
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
                        <div class="text-center" style="padding: 0 70px; margin: 8px 0 6px 0 !important; box-sizing: border-box; text-align: center !important; display: block; margin-left: auto !important; margin-right: auto !important; width: calc(100% - 140px); max-width: calc(100% - 140px);">
                            <h4 class="mb-1" style="font-weight: 800; font-size: 1.4rem; text-shadow: 2px 2px 4px rgba(0,0,0,0.5); text-transform: uppercase; letter-spacing: 0.5px; line-height: 1.2; margin: 0 auto 4px auto !important; text-align: center !important; display: block; width: 100% !important;">
                                {{ $schoolName }}
                            </h4>
                            @if($schoolSlogan)
                            <div style="font-size: 0.9rem; font-weight: 500; opacity: 0.95; line-height: 1.2; font-style: italic; margin: 2px auto 0 auto !important; text-align: center !important; display: block; width: 100% !important;">
                                {{ $schoolSlogan }}
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="card-body" style="padding: 6px; background: white;">
                        <!-- Informations élève -->
                        <div class="row mb-2" style="margin-bottom: 2px !important;">
                            <div class="col-md-6">
                                <h5 style="font-size: 1.3rem; margin-bottom: 4px; font-weight: 800; color: #2c3e50; line-height: 1.3;"><strong>{{ $bulletin['eleve']->nom_complet }}</strong></h5>
                                <p class="mb-1" style="font-size: 1.1rem; margin-bottom: 3px; font-weight: 600; line-height: 1.3;"><strong>Numéro:</strong> <span style="font-weight: 500;">{{ $bulletin['eleve']->numero_etudiant }}</span></p>
                                <p class="mb-1" style="font-size: 1.1rem; margin-bottom: 3px; font-weight: 600; line-height: 1.3;"><strong>Date de naissance:</strong> <span style="font-weight: 500;">{{ $bulletin['eleve']->utilisateur->date_naissance ? \Carbon\Carbon::parse($bulletin['eleve']->utilisateur->date_naissance)->format('d/m/Y') : 'Non renseignée' }}</span></p>
                            </div>
                            <div class="col-md-6 text-end">
                                <div style="display: flex; align-items: center; justify-content: flex-end;">
                                    <div style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 8px 12px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.2); display: inline-block;">
                                        <h5 class="mb-0" style="font-weight: 800; font-size: 1.1rem; margin-bottom: 2px; text-shadow: 1px 1px 2px rgba(0,0,0,0.3); line-height: 1.2;">Rang Annuel: {{ $bulletin['rang'] }}/{{ count($bulletins) }}</h5>
                                        <p class="mb-0" style="font-size: 1.1rem; font-weight: 600; line-height: 1.2;">Moyenne: <strong>{{ number_format($bulletin['moyenneAnnuelle'], 2) }}/{{ $bulletin['eleve']->classe->note_max }}</strong></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tableau des matières par trimestre -->
                        <div class="table-responsive">
                            <table class="table table-bordered" style="margin-bottom: 4px; font-size: 0.85rem; table-layout: fixed; width: 100%; min-width: 650px;">
                                @php
                                    $isPrimaire = $classe->isPrimaire();
                                    $nombreTrimestres = $isPrimaire ? 3 : 2;
                                @endphp
                                <thead style="background: linear-gradient(135deg, {{ $couleurs['bulletin']['bulletin_table_header_bg'] ?? '#34495e' }} 0%, {{ $couleurs['bulletin']['bulletin_table_header_bg'] ?? '#2c3e50' }} 100%); color: {{ $couleurs['bulletin']['bulletin_table_header_text'] ?? '#ffffff' }};">
                                    <tr>
                                        <th style="font-weight: 700; border: 1px solid {{ $couleurs['bulletin']['bulletin_table_border'] ?? '#2c3e50' }}; font-size: 0.7rem; padding: 4px 2px; width: {{ $nombreTrimestres == 3 ? '24%' : '28%' }}; min-width: 110px;">Matière</th>
                                        <th style="font-weight: 700; border: 1px solid {{ $couleurs['bulletin']['bulletin_table_border'] ?? '#2c3e50' }}; text-align: center; font-size: 0.75rem; padding: 4px 2px; width: 6%; min-width: 25px;">Coef.</th>
                                        @if($isPrimaire)
                                            <th style="font-weight: 700; border: 1px solid {{ $couleurs['bulletin']['bulletin_table_border'] ?? '#2c3e50' }}; text-align: center; font-size: 0.75rem; padding: 4px 2px; width: 12%; min-width: 50px;">Trimestre 1</th>
                                            <th style="font-weight: 700; border: 1px solid {{ $couleurs['bulletin']['bulletin_table_border'] ?? '#2c3e50' }}; text-align: center; font-size: 0.75rem; padding: 4px 2px; width: 12%; min-width: 50px;">Trimestre 2</th>
                                            <th style="font-weight: 700; border: 1px solid {{ $couleurs['bulletin']['bulletin_table_border'] ?? '#2c3e50' }}; text-align: center; font-size: 0.75rem; padding: 4px 2px; width: 12%; min-width: 50px;">Trimestre 3</th>
                                            <th style="font-weight: 700; border: 1px solid {{ $couleurs['bulletin']['bulletin_table_border'] ?? '#2c3e50' }}; text-align: center; font-size: 0.75rem; padding: 4px 2px; width: 12%; min-width: 50px;">Moy. Annuelle</th>
                                            <th style="font-weight: 700; border: 1px solid {{ $couleurs['bulletin']['bulletin_table_border'] ?? '#2c3e50' }}; text-align: center; font-size: 0.75rem; padding: 4px 2px; width: 22%; min-width: 50px;">Mention</th>
                                        @else
                                            <th style="font-weight: 700; border: 1px solid {{ $couleurs['bulletin']['bulletin_table_border'] ?? '#2c3e50' }}; text-align: center; font-size: 0.75rem; padding: 4px 2px; width: 16%; min-width: 50px;">Trimestre 1</th>
                                            <th style="font-weight: 700; border: 1px solid {{ $couleurs['bulletin']['bulletin_table_border'] ?? '#2c3e50' }}; text-align: center; font-size: 0.75rem; padding: 4px 2px; width: 16%; min-width: 50px;">Trimestre 2</th>
                                            <th style="font-weight: 700; border: 1px solid {{ $couleurs['bulletin']['bulletin_table_border'] ?? '#2c3e50' }}; text-align: center; font-size: 0.75rem; padding: 4px 2px; width: 16%; min-width: 50px;">Moy. Annuelle</th>
                                            <th style="font-weight: 700; border: 1px solid {{ $couleurs['bulletin']['bulletin_table_border'] ?? '#2c3e50' }}; text-align: center; font-size: 0.75rem; padding: 4px 2px; width: 18%; min-width: 50px;">Mention</th>
                                        @endif
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
                                            <td style="font-weight: 600; border: 1px solid {{ $couleurs['bulletin']['bulletin_table_border'] ?? '#dee2e6' }}; padding: 4px 2px; background-color: {{ $couleurs['bulletin']['bulletin_table_body_bg'] ?? '#f8f9fa' }}; font-size: 0.65rem; width: {{ $nombreTrimestres == 3 ? '24%' : '28%' }}; min-width: 110px;">{{ $matiere->nom }}</td>
                                            <td style="text-align: center; border: 1px solid {{ $couleurs['bulletin']['bulletin_table_border'] ?? '#dee2e6' }}; padding: 5px 3px; font-weight: 600; background-color: {{ $couleurs['bulletin']['bulletin_table_body_bg'] ?? '#e9ecef' }}; font-size: 0.75rem; width: 6%; min-width: 25px;">{{ $coefficient }}</td>
                                            @if($isPrimaire)
                                                <td style="text-align: center; border: 1px solid {{ $couleurs['bulletin']['bulletin_table_border'] ?? '#dee2e6' }}; padding: 5px 3px; font-size: 0.85rem; font-weight: 600; width: 12%; min-width: 50px; {{ $noteT1Finale >= ($noteMax * 0.5) ? 'color: ' . ($couleurs['bulletin']['bulletin_success_text'] ?? '#28a745') . ';' : 'color: ' . ($couleurs['bulletin']['bulletin_danger_text'] ?? '#dc3545') . ';' }}">
                                                    {{ $noteT1Finale ? number_format($noteT1Finale, 2) : '-' }}
                                                </td>
                                                <td style="text-align: center; border: 1px solid {{ $couleurs['bulletin']['bulletin_table_border'] ?? '#dee2e6' }}; padding: 5px 3px; font-size: 0.85rem; font-weight: 600; width: 12%; min-width: 50px; {{ $noteT2Finale >= ($noteMax * 0.5) ? 'color: ' . ($couleurs['bulletin']['bulletin_success_text'] ?? '#28a745') . ';' : 'color: ' . ($couleurs['bulletin']['bulletin_danger_text'] ?? '#dc3545') . ';' }}">
                                                    {{ $noteT2Finale ? number_format($noteT2Finale, 2) : '-' }}
                                                </td>
                                                <td style="text-align: center; border: 1px solid {{ $couleurs['bulletin']['bulletin_table_border'] ?? '#dee2e6' }}; padding: 5px 3px; font-size: 0.85rem; font-weight: 600; width: 12%; min-width: 50px; {{ $noteT3Finale >= ($noteMax * 0.5) ? 'color: ' . ($couleurs['bulletin']['bulletin_success_text'] ?? '#28a745') . ';' : 'color: ' . ($couleurs['bulletin']['bulletin_danger_text'] ?? '#dc3545') . ';' }}">
                                                    {{ $noteT3Finale ? number_format($noteT3Finale, 2) : '-' }}
                                                </td>
                                                <td style="text-align: center; border: 1px solid {{ $couleurs['bulletin']['bulletin_table_border'] ?? '#dee2e6' }}; padding: 5px 3px; font-size: 0.85rem; font-weight: 700; color: {{ $couleurs['bulletin']['bulletin_success_text'] ?? '#28a745' }}; background: {{ $couleurs['bulletin']['bulletin_success_bg'] ?? '#f8f9fa' }}; width: 12%; min-width: 50px;">
                                                    {{ number_format($moyenneMatiere, 2) }}
                                                </td>
                                                <td style="text-align: center; border: 1px solid {{ $couleurs['bulletin']['bulletin_table_border'] ?? '#dee2e6' }}; padding: 5px 3px; font-size: 0.7rem; width: 22%; min-width: 50px;">
                                                    <span class="badge" style="font-size: 0.5rem; padding: 1px 2px; 
                                                        @if($mention == 'Excellent') background-color: {{ $couleurs['bulletin']['bulletin_excellent_bg'] ?? '#28a745' }}; @elseif($mention == 'Très Bien') background-color: {{ $couleurs['bulletin']['bulletin_tres_bien_bg'] ?? '#007bff' }}; @elseif($mention == 'Bien') background-color: {{ $couleurs['bulletin']['bulletin_bien_bg'] ?? '#17a2b8' }}; @elseif($mention == 'Assez Bien') background-color: {{ $couleurs['bulletin']['bulletin_assez_bien_bg'] ?? '#ffc107' }}; color: {{ $couleurs['bulletin']['bulletin_assez_bien_text'] ?? '#212529' }}; @else background-color: {{ $couleurs['bulletin']['bulletin_a_ameliorer_bg'] ?? '#dc3545' }}; @endif">
                                                        {{ $mention }}
                                                    </span>
                                                </td>
                                            @else
                                                <td style="text-align: center; border: 1px solid {{ $couleurs['bulletin']['bulletin_table_border'] ?? '#dee2e6' }}; padding: 5px 3px; font-size: 0.85rem; font-weight: 600; width: 16%; min-width: 50px; {{ $noteT1Finale >= ($noteMax * 0.5) ? 'color: ' . ($couleurs['bulletin']['bulletin_success_text'] ?? '#28a745') . ';' : 'color: ' . ($couleurs['bulletin']['bulletin_danger_text'] ?? '#dc3545') . ';' }}">
                                                    {{ $noteT1Finale ? number_format($noteT1Finale, 2) : '-' }}
                                                </td>
                                                <td style="text-align: center; border: 1px solid {{ $couleurs['bulletin']['bulletin_table_border'] ?? '#dee2e6' }}; padding: 5px 3px; font-size: 0.85rem; font-weight: 600; width: 16%; min-width: 50px; {{ $noteT2Finale >= ($noteMax * 0.5) ? 'color: ' . ($couleurs['bulletin']['bulletin_success_text'] ?? '#28a745') . ';' : 'color: ' . ($couleurs['bulletin']['bulletin_danger_text'] ?? '#dc3545') . ';' }}">
                                                    {{ $noteT2Finale ? number_format($noteT2Finale, 2) : '-' }}
                                                </td>
                                                <td style="text-align: center; border: 1px solid {{ $couleurs['bulletin']['bulletin_table_border'] ?? '#dee2e6' }}; padding: 5px 3px; font-size: 0.85rem; font-weight: 700; color: {{ $couleurs['bulletin']['bulletin_success_text'] ?? '#28a745' }}; background: {{ $couleurs['bulletin']['bulletin_success_bg'] ?? '#f8f9fa' }}; width: 16%; min-width: 50px;">
                                                    {{ number_format($moyenneMatiere, 2) }}
                                                </td>
                                                <td style="text-align: center; border: 1px solid {{ $couleurs['bulletin']['bulletin_table_border'] ?? '#dee2e6' }}; padding: 5px 3px; font-size: 0.7rem; width: 18%; min-width: 50px;">
                                                    <span class="badge" style="font-size: 0.5rem; padding: 1px 2px; 
                                                        @if($mention == 'Excellent') background-color: {{ $couleurs['bulletin']['bulletin_excellent_bg'] ?? '#28a745' }}; @elseif($mention == 'Très Bien') background-color: {{ $couleurs['bulletin']['bulletin_tres_bien_bg'] ?? '#007bff' }}; @elseif($mention == 'Bien') background-color: {{ $couleurs['bulletin']['bulletin_bien_bg'] ?? '#17a2b8' }}; @elseif($mention == 'Assez Bien') background-color: {{ $couleurs['bulletin']['bulletin_assez_bien_bg'] ?? '#ffc107' }}; color: {{ $couleurs['bulletin']['bulletin_assez_bien_text'] ?? '#212529' }}; @else background-color: {{ $couleurs['bulletin']['bulletin_a_ameliorer_bg'] ?? '#dc3545' }}; @endif">
                                                        {{ $mention }}
                                                    </span>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-top: 3px solid #2c3e50;">
                                    <tr>
                                        <td colspan="2" style="font-weight: 700; text-align: right; padding: 6px 4px; font-size: 0.85rem; color: #2c3e50; width: {{ $nombreTrimestres == 3 ? '34%' : '34%' }};">TOTAL ANNUEL</td>
                                        @if($isPrimaire)
                                            <td colspan="4" style="font-weight: 700; text-align: center; padding: 6px 4px; font-size: 0.85rem; color: #6c757d; width: 48%;">-</td>
                                            <td style="font-weight: 700; text-align: center; padding: 6px 4px; font-size: 0.85rem; color: #2c3e50; width: 18%;">{{ $totalCoeffAnnuel }}</td>
                                        @else
                                            <td colspan="3" style="font-weight: 700; text-align: center; padding: 6px 4px; font-size: 0.85rem; color: #6c757d; width: 48%;">-</td>
                                            <td style="font-weight: 700; text-align: center; padding: 6px 4px; font-size: 0.85rem; color: #2c3e50; width: 18%;">{{ $totalCoeffAnnuel }}</td>
                                        @endif
                                    </tr>
                                    <tr>
                                        @if($isPrimaire)
                                            <td colspan="6" style="font-weight: 700; text-align: right; padding: 6px 4px; font-size: 0.85rem; color: #2c3e50; width: 82%;">MOYENNE ANNUELLE</td>
                                        @else
                                            <td colspan="5" style="font-weight: 700; text-align: right; padding: 6px 4px; font-size: 0.85rem; color: #2c3e50; width: 82%;">MOYENNE ANNUELLE</td>
                                        @endif
                                        <td style="font-weight: 700; text-align: center; padding: 6px 4px; width: 18%;">
                                            <span class="badge bg-success" style="font-size: 0.7rem; padding: 3px 6px; font-weight: 700;">
                                                {{ number_format($bulletin['moyenneAnnuelle'], 2) }}/{{ $bulletin['eleve']->classe->note_max }}
                                            </span>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Tableau des rangs par trimestre -->
                        <div class="mt-1">
                            <h5 style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%); color: white; padding: 6px 10px; border-radius: 5px; margin-bottom: 6px; font-weight: 700; text-align: center; font-size: 1rem;">
                                RANGS PAR TRIMESTRE
                            </h5>
                            <table class="table table-bordered" style="margin-bottom: 4px; font-size: 1rem;">
                                <thead style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%); color: white;">
                                    <tr>
                                        @if($isPrimaire)
                                            <th style="font-weight: 700; border: 1px solid #495057; text-align: center; font-size: 1rem; padding: 6px 4px; width: 20%;">Trimestre 1</th>
                                            <th style="font-weight: 700; border: 1px solid #495057; text-align: center; font-size: 1rem; padding: 6px 4px; width: 20%;">Trimestre 2</th>
                                            <th style="font-weight: 700; border: 1px solid #495057; text-align: center; font-size: 1rem; padding: 6px 4px; width: 20%;">Trimestre 3</th>
                                            <th style="font-weight: 700; border: 1px solid #495057; text-align: center; font-size: 1rem; padding: 6px 4px; width: 20%;">Rang Annuel</th>
                                            <th style="font-weight: 700; border: 1px solid #495057; text-align: center; font-size: 1rem; padding: 6px 4px; width: 20%;">Mention Annuelle</th>
                                        @else
                                            <th style="font-weight: 700; border: 1px solid #495057; text-align: center; font-size: 1rem; padding: 6px 4px; width: 25%;">Trimestre 1</th>
                                            <th style="font-weight: 700; border: 1px solid #495057; text-align: center; font-size: 1rem; padding: 6px 4px; width: 25%;">Trimestre 2</th>
                                            <th style="font-weight: 700; border: 1px solid #495057; text-align: center; font-size: 1rem; padding: 6px 4px; width: 25%;">Rang Annuel</th>
                                            <th style="font-weight: 700; border: 1px solid #495057; text-align: center; font-size: 1rem; padding: 6px 4px; width: 25%;">Mention Annuelle</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        @if($isPrimaire)
                                            <td style="text-align: center; border: 1px solid #dee2e6; padding: 6px 4px; font-size: 1rem; font-weight: 700; color: #007bff; background: #f8f9fa;">
                                                {{ $bulletin['rangsParPeriode']['trimestre1'] ?? '-' }}/{{ count($bulletins) }}
                                            </td>
                                            <td style="text-align: center; border: 1px solid #dee2e6; padding: 6px 4px; font-size: 1rem; font-weight: 700; color: #007bff; background: #f8f9fa;">
                                                {{ $bulletin['rangsParPeriode']['trimestre2'] ?? '-' }}/{{ count($bulletins) }}
                                            </td>
                                            <td style="text-align: center; border: 1px solid #dee2e6; padding: 6px 4px; font-size: 1rem; font-weight: 700; color: #007bff; background: #f8f9fa;">
                                                {{ $bulletin['rangsParPeriode']['trimestre3'] ?? '-' }}/{{ count($bulletins) }}
                                            </td>
                                            <td style="text-align: center; border: 1px solid #dee2e6; padding: 6px 4px; font-size: 1rem; font-weight: 700; color: #28a745; background: #f8f9fa;">
                                                {{ $bulletin['rang'] }}/{{ count($bulletins) }}
                                            </td>
                                            <td style="text-align: center; border: 1px solid #dee2e6; padding: 6px 4px; font-size: 1rem; font-weight: 700; color: #6c757d; background: #f8f9fa;">
                                                @php
                                                    $moyenne = $bulletin['moyenneAnnuelle'];
                                                    $noteMax = $bulletin['eleve']->classe->note_max;
                                                    $mentionAnnuelle = '';
                                                    if($moyenne >= ($noteMax * 0.8)) {
                                                        $mentionAnnuelle = 'Excellent';
                                                    } elseif($moyenne >= ($noteMax * 0.7)) {
                                                        $mentionAnnuelle = 'Très Bien';
                                                    } elseif($moyenne >= ($noteMax * 0.6)) {
                                                        $mentionAnnuelle = 'Bien';
                                                    } elseif($moyenne >= ($noteMax * 0.5)) {
                                                        $mentionAnnuelle = 'Assez Bien';
                                                    } else {
                                                        $mentionAnnuelle = 'À Améliorer';
                                                    }
                                                @endphp
                                                <span class="badge" style="font-size: 0.6rem; padding: 2px 4px; 
                                                    @if($mentionAnnuelle == 'Excellent') background-color: #28a745; @elseif($mentionAnnuelle == 'Très Bien') background-color: #007bff; @elseif($mentionAnnuelle == 'Bien') background-color: #17a2b8; @elseif($mentionAnnuelle == 'Assez Bien') background-color: #ffc107; color: #212529; @else background-color: #dc3545; @endif">
                                                    {{ $mentionAnnuelle }}
                                                </span>
                                            </td>
                                        @else
                                            <td style="text-align: center; border: 1px solid #dee2e6; padding: 6px 4px; font-size: 1rem; font-weight: 700; color: #007bff; background: #f8f9fa;">
                                                {{ $bulletin['rangsParPeriode']['trimestre1'] ?? '-' }}/{{ count($bulletins) }}
                                            </td>
                                            <td style="text-align: center; border: 1px solid #dee2e6; padding: 6px 4px; font-size: 1rem; font-weight: 700; color: #007bff; background: #f8f9fa;">
                                                {{ $bulletin['rangsParPeriode']['trimestre2'] ?? '-' }}/{{ count($bulletins) }}
                                            </td>
                                            <td style="text-align: center; border: 1px solid #dee2e6; padding: 6px 4px; font-size: 1rem; font-weight: 700; color: #28a745; background: #f8f9fa;">
                                                {{ $bulletin['rang'] }}/{{ count($bulletins) }}
                                            </td>
                                            <td style="text-align: center; border: 1px solid #dee2e6; padding: 6px 4px; font-size: 1rem; font-weight: 700; color: #6c757d; background: #f8f9fa;">
                                                @php
                                                    $moyenne = $bulletin['moyenneAnnuelle'];
                                                    $noteMax = $bulletin['eleve']->classe->note_max;
                                                    $mentionAnnuelle = '';
                                                    if($moyenne >= ($noteMax * 0.8)) {
                                                        $mentionAnnuelle = 'Excellent';
                                                    } elseif($moyenne >= ($noteMax * 0.7)) {
                                                        $mentionAnnuelle = 'Très Bien';
                                                    } elseif($moyenne >= ($noteMax * 0.6)) {
                                                        $mentionAnnuelle = 'Bien';
                                                    } elseif($moyenne >= ($noteMax * 0.5)) {
                                                        $mentionAnnuelle = 'Assez Bien';
                                                    } else {
                                                        $mentionAnnuelle = 'À Améliorer';
                                                    }
                                                @endphp
                                                <span class="badge" style="font-size: 0.6rem; padding: 2px 4px; 
                                                    @if($mentionAnnuelle == 'Excellent') background-color: #28a745; @elseif($mentionAnnuelle == 'Très Bien') background-color: #007bff; @elseif($mentionAnnuelle == 'Bien') background-color: #17a2b8; @elseif($mentionAnnuelle == 'Assez Bien') background-color: #ffc107; color: #212529; @else background-color: #dc3545; @endif">
                                                    {{ $mentionAnnuelle }}
                                                </span>
                                            </td>
                                        @endif
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Signature -->
                            <div class="mt-1">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div style="text-align: right;">
                                            <div class="d-inline-block" style="margin-right: 30px;">
                                                <div style="border-top: 1px solid #333; margin-top: 15px; padding-top: 6px;">
                                                    <p style="margin: 0; font-size: 0.75rem; font-weight: 600;">Le Directeur</p>
                                                    <p style="margin: 0; font-size: 0.7rem; color: #666;">Signature et Cachet</p>
                                                </div>
                                            </div>
                                            <div class="d-inline-block">
                                                <div style="border-top: 1px solid #333; margin-top: 15px; padding-top: 6px;">
                                                    <p style="margin: 0; font-size: 0.75rem; font-weight: 600;">Appréciation</p>
                                                    <p style="margin: 0; font-size: 0.7rem; color: #666;">Signature</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-1">
                                    <div class="col-md-12">
                                        <div style="text-align: left;">
                                            <p style="margin: 0; font-size: 0.8rem; color: #6c757d;">
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
            <div style="page-break-after: always; margin: 0; padding: 0; height: 1px; visibility: hidden;"></div>
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
        margin: 0.3cm;
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
    
    /* Cacher les divs de saut de page vides */
    div[style*="page-break-after: always"] {
        display: none !important;
        height: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        visibility: hidden !important;
        overflow: hidden !important;
    }
    
    /* Afficher les bulletins */
    .bulletin-page {
        display: block !important;
        visibility: visible !important;
        page-break-inside: avoid;
        page-break-after: auto;
        width: 100%;
        height: 27.5cm; /* Hauteur optimisée */
        margin: 0;
        padding: 0.3cm; /* Padding minimal */
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
    }
    
    .bulletin-page .card {
        height: 100%;
        border: none;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
    }
    
    .bulletin-page .card-header {
        flex-shrink: 0;
        padding: 6px;
    }
    
    .bulletin-page .card-body {
        flex: 1;
        padding: 8px;
    }
    
    /* Maintenir les tailles augmentées pour l'impression */
    body {
        font-size: 12px;
        line-height: 1.3;
        margin: 0;
        padding: 0;
    }
    
    .bulletin-page .card-header h4 {
        font-size: 1.4rem !important;
    }
    
    .bulletin-page .card-header h3 {
        font-size: 1.2rem !important;
    }
    
    .bulletin-page .card-header h5 {
        font-size: 1rem !important;
    }
    
    .bulletin-page table {
        font-size: 1rem !important;
    }
    
    .bulletin-page table th,
    .bulletin-page table td {
        font-size: 1rem !important;
        padding: 6px 5px !important;
    }
    
    .bulletin-page h4 {
        font-size: 1.1rem !important;
    }
    
    .bulletin-page h5 {
        font-size: 1rem !important;
    }
    
    .bulletin-page .card-body h5 {
        font-size: 1.2rem !important;
    }
    
    .bulletin-page .card-body p {
        font-size: 1rem !important;
    }
    
    .bulletin-page .badge {
        font-size: 0.7rem !important;
        padding: 3px 5px !important;
    }
    
    /* Conservation des couleurs */
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
}
</style>
@endsection
