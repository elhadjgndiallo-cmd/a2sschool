<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bulletins - {{ $classe->nom }}</title>
    <style>
        @page { size: A4 portrait; margin: 0.5cm; }
        body { margin: 0; padding: 0; font-family: DejaVu Sans, sans-serif; font-size: 11px; line-height: 1.3; color: #333; background: #fff; }
        .bulletin-page { page-break-after: always; margin-bottom: 15px; border: 3px solid #2c3e50; border-radius: 8px; box-sizing: border-box; overflow: hidden; }
        .bulletin-page:last-child { page-break-after: avoid; }
        .card-header { background: #1a5490; color: white; border: none; padding: 8px 15px; position: relative; }
        .card-body { padding: 8px 12px 65px 12px; }
        .bulletin-footer { padding: 6px 12px; border-top: 1px solid #dee2e6; margin-top: 8px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 4px; font-size: 10px; }
        th, td { border: 1px solid #2c3e50; padding: 6px 5px; text-align: left; }
        th { background: #2c3e50; color: white; font-weight: 700; }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 10px; font-weight: 600; }
        .bg-primary { background-color: #0d6efd; color: #fff; }
        .bg-success { background-color: #198754; color: #fff; }
        .bg-warning { background-color: #ffc107; color: #000; }
        .bg-danger { background-color: #dc3545; color: #fff; }
        .bg-info { background-color: #0dcaf0; color: #000; }
        .bg-secondary { background-color: #6c757d; color: #fff; }
        
        /* Styles pour forcer l'affichage horizontal */
        .bulletin-footer {
            display: block !important;
            width: 100% !important;
        }
        
        .bulletin-footer > div:first-child {
            display: flex !important;
            flex-direction: row !important;
            gap: 2% !important;
            width: 100% !important;
            align-items: stretch !important;
        }
        
        .bulletin-footer .footer-section {
            flex: 1 !important;
            max-width: 48% !important;
        }
    </style>
</head>
<body>
@php
    $schoolInfo = \App\Helpers\SchoolHelper::getSchoolInfo();
    $schoolName = $schoolInfo && isset($schoolInfo->nom) ? $schoolInfo->nom : 'École';
    $schoolSlogan = $schoolInfo && isset($schoolInfo->slogan) ? $schoolInfo->slogan : '';
@endphp
@foreach($bulletins as $bulletin)
<div class="bulletin-page">
    <div class="card-header">
        @if($logoDataUri)
        <img src="{{ $logoDataUri }}" alt="Logo" style="position: absolute; top: 8px; left: 15px; max-width: 50px; max-height: 50px; background: white; padding: 4px; border-radius: 5px;">
        <img src="{{ $logoDataUri }}" alt="Logo" style="position: absolute; top: 8px; right: 15px; max-width: 50px; max-height: 50px; background: white; padding: 4px; border-radius: 5px;">
        @endif
        <div style="text-align: center; padding: 0 70px;">
            <h4 style="margin: 8px 0 4px 0; font-size: 1.2rem; font-weight: 800;">{{ $schoolName }}</h4>
            @if($schoolSlogan)<div style="font-size: 0.85rem; font-style: italic;">{{ $schoolSlogan }}</div>@endif
        </div>
        <div style="border-top: 2px solid rgba(255,255,255,0.3); padding-top: 6px; margin-top: 8px;">
            <div style="float: left;">
                <div style="font-size: 1.1rem; font-weight: 800;">BULLETIN DE NOTES</div>
                <div style="font-size: 0.9rem;">{{ $classe->nom }} - {{ $classe->niveau }}</div>
            </div>
            <div style="float: right; text-align: right;">
                <div style="font-size: 1rem; font-weight: 700;">Année Scolaire {{ $anneeScolaireActive ? $anneeScolaireActive->nom : (date('Y').'-'.(date('Y')+1)) }}</div>
                <div style="font-size: 0.9rem;">
                    @if($periode == 'trimestre1') Trimestre 1
                    @elseif($periode == 'trimestre2') Trimestre 2
                    @elseif($periode == 'trimestre3') Trimestre 3
                    @else {{ ucfirst($periode) }}
                    @endif
                </div>
            </div>
            <div style="clear: both;"></div>
        </div>
    </div>
    <div class="card-body">
        <div style="margin-bottom: 8px;">
            <div style="float: left;">
                <strong style="font-size: 1.1rem;">{{ $bulletin['eleve']->nom_complet }}</strong><br>
                <strong>Numéro:</strong> {{ $bulletin['eleve']->numero_etudiant }}<br>
                <strong>Date de naissance:</strong> {{ $bulletin['eleve']->utilisateur->date_naissance ? \Carbon\Carbon::parse($bulletin['eleve']->utilisateur->date_naissance)->format('d/m/Y') : 'Non renseignée' }}
            </div>
            <div style="float: right; text-align: right;">
                <div style="background: #2980b9; color: white; padding: 6px 10px; border-radius: 5px; display: inline-block;">
                    <div style="font-weight: 800;">Rang: {{ $bulletin['rang'] }}/{{ $classe->eleves->count() }}</div>
                    <div>Moyenne: <strong>{{ number_format($bulletin['moyenne_generale'] ?? 0, 2) }}/{{ $classe->note_max }}</strong></div>
                </div>
            </div>
            <div style="clear: both;"></div>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>Matière</th>
                    <th class="text-center">Coef.</th>
                    @if(!$classe->isPrimaire())<th class="text-center">Cours</th>@endif
                    <th class="text-center">Comp.</th>
                    <th class="text-center">Finale</th>
                    @if(!$classe->isPrimaire())<th class="text-center">Points</th>@endif
                    <th class="text-center">Mention</th>
                </tr>
            </thead>
            <tbody>
                @php $totalPoints = 0; $totalCoeff = 0; @endphp
                @foreach($bulletin['notes'] as $matiere => $data)
                    @php $totalPoints += $data['points']; $totalCoeff += $data['coefficient']; @endphp
                    <tr>
                        <td style="background: #f8f9fa;"><strong>{{ $matiere }}</strong></td>
                        <td class="text-center" style="background: #e9ecef;">{{ $data['coefficient'] }}</td>
                        @if(!$classe->isPrimaire())
                        <td class="text-center">{{ $data['note_cours'] > 0 ? number_format($data['note_cours'], 2) : '-' }}/{{ $classe->note_max }}</td>
                        @endif
                        <td class="text-center">{{ $data['note_composition'] > 0 ? number_format($data['note_composition'], 2) : '-' }}/{{ $classe->note_max }}</td>
                        <td class="text-center"><strong>{{ number_format($data['note_finale'], 2) }}/{{ $classe->note_max }}</strong></td>
                        @if(!$classe->isPrimaire())<td class="text-center" style="background: #e9ecef;">{{ $data['points'] }}</td>@endif
                        <td>
                            @php $appreciationNote = $classe->getAppreciation($data['note_finale']); @endphp
                            <span class="badge bg-{{ $appreciationNote['color'] }}">{{ $appreciationNote['label'] }}</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                @php $moy = $bulletin['moyenne_generale'] ?? 0; $appreciationGenerale = $classe->getAppreciation($moy); $appreciationGeneraleBadge = $classe->getAppreciation($moy); @endphp
                <tr style="background: #e9ecef; border-top: 3px solid #2c3e50;">
                    <th>MOYENNE GÉNÉRALE</th>
                    <th class="text-center">{{ $totalCoeff }}</th>
                    @if(!$classe->isPrimaire())<th class="text-center">-</th>@endif
                    <th class="text-center">-</th>
                    <th class="text-center"><span class="badge bg-{{ $appreciationGeneraleBadge['color'] }}" style="padding: 5px 12px;">{{ number_format($moy, 2) }}/{{ $classe->note_max }}</span></th>
                    @if(!$classe->isPrimaire())<th class="text-center">{{ round($totalPoints, 2) }}</th>@endif
                    <th><span class="badge bg-{{ $appreciationGenerale['color'] }}">{{ $appreciationGenerale['label'] }}</span></th>
                </tr>
            </tfoot>
        </table>
    </div>
    <div class="bulletin-footer">
        <table style="width: 100%; border-collapse: collapse; border: none;">
            <tr>
                <td style="width: 48%; padding: 0; vertical-align: top; padding-right: 2%;">
                    <!-- Section Observations -->
                    <div style="border: 1px solid #2c3e50; border-radius: 4px; padding: 2px 4px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); min-height: 55px; box-sizing: border-box;">
                        <h6 style="color: #2c3e50; font-weight: 700; margin-bottom: 1px; border-bottom: 1px solid #2c3e50; padding-bottom: 1px; font-size: 10px; line-height: 1.0;"><strong>Observations:</strong></h6>
                        <div style="line-height: 1.1; font-size: 9px; padding-top: 1px; min-height: 40px;">
                            &nbsp;
                        </div>
                    </div>
                </td>
                <td style="width: 48%; padding: 0; vertical-align: top; padding-left: 2%;">
                    <!-- Section Signatures -->
                    <div style="border: 1px solid #2c3e50; border-radius: 4px; padding: 2px 4px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); min-height: 55px; box-sizing: border-box;">
                        <h6 style="color: #2c3e50; font-weight: 700; margin-bottom: 1px; border-bottom: 1px solid #2c3e50; padding-bottom: 1px; font-size: 10px; line-height: 1.0;"><strong>Signatures:</strong></h6>
                        <table style="width: 100%; border-collapse: collapse; border: none; margin-top: 1px;">
                            <tr>
                                <td style="width: 48%; padding: 0; text-align: center; padding-right: 2%;">
                                    <p style="text-align: center; font-weight: 700; color: #2c3e50; margin-bottom: 1px; font-size: 9px; line-height: 1.0;">Directeur</p>
                                    <div style="height: 20px; border-bottom: 1px solid #2c3e50; margin-bottom: 1px;"></div>
                                    <div style="text-align: center; color: #6c757d; font-size: 8px; line-height: 1.0;">Date: _____</div>
                                </td>
                                <td style="width: 48%; padding: 0; text-align: center; padding-left: 2%;">
                                    <p style="text-align: center; font-weight: 700; color: #2c3e50; margin-bottom: 1px; font-size: 9px; line-height: 1.0;">Parent</p>
                                    <div style="height: 20px; border-bottom: 1px solid #2c3e50; margin-bottom: 1px;"></div>
                                    <div style="text-align: center; color: #6c757d; font-size: 8px; line-height: 1.0;">Date: _____</div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>
@endforeach
</body>
</html>
