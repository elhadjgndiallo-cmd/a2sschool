@extends('layouts.app')

@section('title', 'Bulletins de Notes - ' . $classe->nom)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4 no-print">
                <h2><i class="fas fa-file-alt me-2"></i>Bulletins de Notes - {{ $classe->nom }}</h2>
                <div class="no-print">
                    <button onclick="window.print()" class="btn btn-primary me-2">
                        <i class="fas fa-print me-2"></i>Imprimer
                    </button>
                    <a href="{{ route('notes.bulletins.classe.pdf', $classe->id) }}?periode={{ $periode }}" class="btn btn-success me-2">
                        <i class="fas fa-download me-2"></i>Télécharger
                    </a>
                    <a href="{{ route('notes.index') }}" class="btn btn-secondary">
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
                    <div class="card-header" style="background: linear-gradient(135deg, #1a5490 0%, #2c3e50 100%); color: white; border: none; padding: 8px 15px; position: relative; width: 100%; box-sizing: border-box;">
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
                        
                        <div class="border-top border-white border-2 pt-2" style="border-top: 2px solid rgba(255,255,255,0.3) !important; padding-top: 6px !important; margin-top: 8px; padding-left: 0; padding-right: 0;">
                            <div class="row align-items-center" style="margin-left: 0; margin-right: 0;">
                                <div class="col-md-6" style="padding-left: 0; padding-right: 5px;">
                                    <h3 class="mb-0" style="font-weight: 800; text-shadow: 2px 2px 4px rgba(0,0,0,0.5); font-size: 1.25rem; letter-spacing: 0.5px; line-height: 1.2;">BULLETIN DE NOTES</h3>
                                    <div style="font-size: 1rem; font-weight: 500; opacity: 0.95; line-height: 1.2; margin-top: 2px;">{{ $classe->nom }} - {{ $classe->niveau }}</div>
                                </div>
                                <div class="col-md-6 text-end" style="padding-left: 5px; padding-right: 0;">
                                    <h4 style="font-weight: 700; font-size: 1.1rem; margin-bottom: 2px; text-shadow: 1px 1px 2px rgba(0,0,0,0.5); line-height: 1.2;">Année Scolaire {{ $anneeScolaireActive ? $anneeScolaireActive->nom : (date('Y') . '-' . (date('Y')+1)) }}</h4>
                                    <div style="font-size: 1rem; font-weight: 500; opacity: 0.95; line-height: 1.2;">
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
                        <div class="row mb-2" style="margin-bottom: 2px !important;">
                            <div class="col-md-6">
                                <h5 style="font-size: 1.2rem; margin-bottom: 4px; font-weight: 800; color: #2c3e50; line-height: 1.2;"><strong>{{ $bulletin['eleve']->nom_complet }}</strong></h5>
                                <p class="mb-1" style="font-size: 1rem; margin-bottom: 3px; font-weight: 600; line-height: 1.2;"><strong>Numéro:</strong> <span style="font-weight: 500;">{{ $bulletin['eleve']->numero_etudiant }}</span></p>
                                <p class="mb-1" style="font-size: 1rem; margin-bottom: 3px; font-weight: 600; line-height: 1.2;"><strong>Date de naissance:</strong> <span style="font-weight: 500;">{{ $bulletin['eleve']->utilisateur->date_naissance ? \Carbon\Carbon::parse($bulletin['eleve']->utilisateur->date_naissance)->format('d/m/Y') : 'Non renseignée' }}</span></p>
                            </div>
                            <div class="col-md-6 text-end">
                                <div style="display: flex; align-items: center; justify-content: flex-end; gap: 10px;">
                                    <div style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: white; padding: 6px 10px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.2); display: inline-block;">
                                        <h5 class="mb-0" style="font-weight: 800; font-size: 1.05rem; margin-bottom: 2px; text-shadow: 1px 1px 2px rgba(0,0,0,0.3); line-height: 1.1;">Rang: {{ $bulletin['rang'] }}/{{ $classe->eleves->count() }}</h5>
                                        <p class="mb-0" style="font-size: 1.05rem; font-weight: 600; line-height: 1.1;">Moyenne: <strong>{{ number_format($bulletin['moyenne_generale'] ?? 0, 2) }}/{{ $classe->note_max }}</strong></p>
                                    </div>
                                    <!-- QR Code de vérification -->
                                    <div style="background: white; padding: 4px; border-radius: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                        {!! QrCode::size(80)->generate($bulletin['verification_url']) !!}
                                    </div>
                                </div>
                                <div style="text-align: right; margin-top: 4px; font-size: 0.7rem; color: #6c757d;">
                                    <small>Scannez pour vérifier</small>
                                </div>
                            </div>
                        </div>

                        <!-- Tableau des notes par matière -->
                        <div class="table-responsive">
                            <table class="table table-bordered" style="margin-bottom: 1px;">
                                <thead style="background: linear-gradient(135deg, #34495e 0%, #2c3e50 100%); color: white;">
                                    <tr>
                                        <th style="font-weight: 700; border: 1px solid #2c3e50; font-size: 1rem; padding: 6px 5px;">Matière</th>
                                        <th style="font-weight: 700; border: 1px solid #2c3e50; text-align: center; font-size: 1rem; padding: 6px 5px;">Coef.</th>
                                        @if(!$classe->isPrimaire())
                                        <th style="font-weight: 700; border: 1px solid #2c3e50; text-align: center; font-size: 1rem; padding: 6px 5px;">Cours</th>
                                        @endif
                                        <th style="font-weight: 700; border: 1px solid #2c3e50; text-align: center; font-size: 1rem; padding: 6px 5px;">Comp.</th>
                                        <th style="font-weight: 700; border: 1px solid #2c3e50; text-align: center; font-size: 1rem; padding: 6px 5px;">Finale</th>
                                        @if(!$classe->isPrimaire())
                                        <th style="font-weight: 700; border: 1px solid #2c3e50; text-align: center; font-size: 1rem; padding: 6px 5px;">Points</th>
                                        @endif
                                        <th style="font-weight: 700; border: 1px solid #2c3e50; text-align: center; font-size: 1rem; padding: 6px 5px;">Mention</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $totalPoints = 0; $totalCoeff = 0; @endphp
                                    @foreach($bulletin['notes'] as $matiere => $data)
                                        @php 
                                            $totalPoints += $data['points'];
                                            $totalCoeff += $data['coefficient'];
                                        @endphp
                                        <tr style="border-bottom: 1px solid #dee2e6;">
                                            <td style="font-weight: 600; padding: 6px 5px; background-color: #f8f9fa; font-size: 0.95rem;"><strong>{{ $matiere }}</strong></td>
                                            <td class="text-center" style="padding: 6px 5px; font-weight: 600; background-color: #e9ecef; font-size: 0.95rem;">{{ $data['coefficient'] }}</td>
                                            @if(!$classe->isPrimaire())
                                            <td class="text-center notes-cell" style="padding: 6px 5px; font-size: 1rem;">
                                                <span class="note-value" style="font-size: 1rem; font-weight: 600; color: #2c3e50;">
                                                    {{ $data['note_cours'] > 0 ? number_format($data['note_cours'], 2) : '-' }}/{{ $classe->note_max }}
                                                </span>
                                            </td>
                                            @endif
                                            <td class="text-center notes-cell" style="padding: 6px 5px; font-size: 1rem;">
                                                <span class="note-value" style="font-size: 1rem; font-weight: 600; color: #2c3e50;">
                                                    {{ $data['note_composition'] > 0 ? number_format($data['note_composition'], 2) : '-' }}/{{ $classe->note_max }}
                                                </span>
                                            </td>
                                            <td class="text-center notes-cell" style="padding: 6px 5px; font-size: 1rem;">
                                                <span class="note-value" style="font-size: 1rem; font-weight: 700; color: #2c3e50;">
                                                    {{ number_format($data['note_finale'], 2) }}/{{ $classe->note_max }}
                                                </span>
                                            </td>
                                            @if(!$classe->isPrimaire())
                                            <td class="text-center" style="padding: 6px 5px; font-weight: 600; background-color: #e9ecef; font-size: 0.95rem;">{{ $data['points'] }}</td>
                                            @endif
                                            <td style="padding: 6px 5px; font-size: 0.85rem;">
                                                @php
                                                    $appreciationNote = $classe->getAppreciation($data['note_finale']);
                                                @endphp
                                                <span class="badge bg-{{ $appreciationNote['color'] }}" style="font-size: 0.65rem; padding: 2px 4px;">{{ $appreciationNote['label'] }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-top: 3px solid #2c3e50;">
                                        <th style="font-weight: 700; padding: 8px 5px; font-size: 1.05rem; color: #2c3e50;">MOYENNE GÉNÉRALE</th>
                                        <th class="text-center" style="font-weight: 700; padding: 8px 5px; font-size: 1.05rem; color: #2c3e50;">{{ $totalCoeff }}</th>
                                        @if(!$classe->isPrimaire())
                                        <th class="text-center" style="font-weight: 700; padding: 8px 5px; font-size: 1.05rem; color: #6c757d;">-</th>
                                        @endif
                                        <th class="text-center" style="font-weight: 700; padding: 8px 5px; font-size: 1.05rem; color: #6c757d;">-</th>
                                        <th class="text-center" style="font-weight: 700; padding: 8px 5px;">
                                            @php 
                                                $moy = $bulletin['moyenne_generale'] ?? 0;
                                                $appreciationGeneraleBadge = $classe->getAppreciation($moy);
                                            @endphp
                                            <span class="badge bg-{{ $appreciationGeneraleBadge['color'] }}" style="font-size: 1rem; padding: 5px 12px; font-weight: 700;">
                                                {{ number_format($moy, 2) }}/{{ $classe->note_max }}
                                            </span>
                                        </th>
                                        @if(!$classe->isPrimaire())
                                        <th class="text-center" style="font-weight: 700; padding: 8px 5px; font-size: 1.05rem; color: #2c3e50;">{{ round($totalPoints, 2) }}</th>
                                        @endif
                                        <th style="font-weight: 700; padding: 8px 5px;">
                                            @php 
                                                $appreciationGenerale = $classe->getAppreciation($moy);
                                            @endphp
                                            <span class="badge bg-{{ $appreciationGenerale['color'] }}" style="font-size: 0.7rem; padding: 2px 5px; font-weight: 700;">{{ $appreciationGenerale['label'] }}</span>
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- Footer avec Observations et Signatures -->
                    <div class="bulletin-footer">
                        <div class="row" style="margin: 0; display: flex; flex-direction: row; flex-wrap: nowrap;">
                            <div class="col-md-6" style="padding-right: 8px; padding-left: 0; width: 50%; flex: 0 0 50%; display: inline-block; vertical-align: top;">
                                <div style="border: 1px solid #2c3e50; border-radius: 4px; padding: 4px 6px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); min-height: 60px;">
                                    <h6 style="color: #2c3e50; font-weight: 700; margin-bottom: 4px; border-bottom: 1px solid #2c3e50; padding-bottom: 2px; font-size: 14px; line-height: 1.2;"><strong>Observations:</strong></h6>
                                    <div style="line-height: 1.3; font-size: 14px; padding-top: 2px; min-height: 50px;">
                                        &nbsp;
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6" style="padding-left: 8px; padding-right: 0; width: 50%; flex: 0 0 50%; display: inline-block; vertical-align: top;">
                                <div style="border: 1px solid #2c3e50; border-radius: 4px; padding: 4px 6px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); min-height: 80px;">
                                    <h6 style="color: #2c3e50; font-weight: 700; margin-bottom: 4px; border-bottom: 1px solid #2c3e50; padding-bottom: 2px; font-size: 14px; line-height: 1.2;"><strong>Signatures:</strong></h6>
                                    <div class="row" style="margin-top: 4px; margin-left: 0; margin-right: 0;">
                                        <div class="col-6" style="padding-left: 0; padding-right: 4px;">
                                            <p class="text-center" style="font-weight: 700; color: #2c3e50; margin-bottom: 4px; font-size: 14px; line-height: 1.2;">Directeur</p>
                                            <div style="height: 35px; border-bottom: 1px solid #2c3e50; margin-bottom: 4px;"></div>
                                            <div class="text-center" style="color: #6c757d; font-size: 14px; line-height: 1.2; margin-top: 2px;">Date: _____</div>
                                        </div>
                                        <div class="col-6" style="padding-left: 4px; padding-right: 0;">
                                            <p class="text-center" style="font-weight: 700; color: #2c3e50; margin-bottom: 4px; font-size: 14px; line-height: 1.2;">Parent</p>
                                            <div style="height: 35px; border-bottom: 1px solid #2c3e50; margin-bottom: 4px;"></div>
                                            <div class="text-center" style="color: #6c757d; font-size: 14px; line-height: 1.2; margin-top: 2px;">Date: _____</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Styles pour l'impression A4 */
@media print {
    @page {
        size: A4 portrait;
        margin: 0.5cm !important;
    }
    
    * {
        -webkit-print-color-adjust: exact !important;
        color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    
    html, body {
        width: 100%;
        height: 100%;
        margin: 0;
        padding: 0;
        font-size: 11px;
        line-height: 1.3;
        background: white !important;
    }
    
    /* Masquer les éléments non nécessaires */
    .no-print,
    nav,
    .navbar,
    header,
    footer,
    .btn-toolbar,
    .btn {
        display: none !important;
    }
    
    .container-fluid,
    .container {
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 auto !important;
        padding: 0 !important;
        box-sizing: border-box !important;
    }
    
    .row {
        margin-left: 0 !important;
        margin-right: 0 !important;
    }
    
    .col-md-12,
    .col-md-6 {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }
    
    .bulletin-page {
        page-break-inside: avoid !important;
        page-break-after: always !important;
        page-break-before: auto !important;
        margin: 0 !important;
        padding: 0 !important;
        width: 21cm !important;
        max-width: 21cm !important;
        height: 29.7cm !important;
        max-height: 29.7cm !important;
        box-shadow: none !important;
        border: 3px solid #2c3e50 !important;
        border-radius: 8px !important;
        background: white !important;
        overflow: hidden !important;
        display: block !important;
        position: relative !important;
        box-sizing: border-box !important;
    }
    
    .bulletin-page:first-child {
        page-break-before: auto !important;
    }
    
    .bulletin-page + .bulletin-page {
        page-break-before: always !important;
    }
    
    .bulletin-page:last-child {
        page-break-after: avoid !important;
    }
    
    .card {
        border: none !important;
        border-radius: 0 !important;
        box-shadow: none !important;
        margin: 0 !important;
        padding: 0 !important;
        height: 100% !important;
        min-height: 100% !important;
        max-height: 100% !important;
        display: flex !important;
        flex-direction: column !important;
        page-break-inside: avoid !important;
        page-break-after: avoid !important;
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
        overflow: hidden !important;
        position: relative !important;
    }
    
    .card-header {
        background: linear-gradient(135deg, #1a5490 0%, #2c3e50 100%) !important;
        color: white !important;
        border: none !important;
        padding: 4px 12px !important;
        margin: 0 auto !important;
        page-break-inside: avoid !important;
        page-break-after: avoid !important;
        flex-shrink: 0 !important;
        position: relative !important;
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
        height: 90px !important;
        max-height: 90px !important;
        overflow: hidden !important;
    }
    
    .card-header > div[style*="position: absolute"][style*="left"] {
        left: 15px !important;
    }
    
    .card-header > div[style*="position: absolute"][style*="right"] {
        right: 15px !important;
    }
    
    .card-header .text-center {
        margin-left: auto !important;
        margin-right: auto !important;
        text-align: center !important;
        display: block !important;
        width: calc(100% - 140px) !important;
        max-width: calc(100% - 140px) !important;
        box-sizing: border-box !important;
        position: relative !important;
        left: 0 !important;
        right: 0 !important;
        padding-left: 70px !important;
        padding-right: 70px !important;
    }
    
    .card-header .text-center h4 {
        margin-left: auto !important;
        margin-right: auto !important;
        text-align: center !important;
        display: block !important;
        width: 100% !important;
    }
    
    .card-header .text-center > div {
        margin-left: auto !important;
        margin-right: auto !important;
        text-align: center !important;
        display: block !important;
        width: 100% !important;
    }
    
    .card-header img {
        max-width: 50px !important;
        max-height: 50px !important;
        object-fit: contain !important;
        background: white !important;
        padding: 4px !important;
        border-radius: 5px !important;
    }
    
    .card-header h4 {
        font-size: 1.4rem !important;
        margin-bottom: 4px !important;
        font-weight: 800 !important;
        line-height: 1.2 !important;
    }
    
    .card-header h3 {
        font-size: 1.25rem !important;
        margin-bottom: 3px !important;
        font-weight: 800 !important;
        line-height: 1.2 !important;
    }
    
    .card-header h5 {
        font-size: 1.1rem !important;
        margin-bottom: 2px !important;
        font-weight: 700 !important;
        line-height: 1.2 !important;
    }
    
    .card-header div {
        font-size: 1rem !important;
        line-height: 1.2 !important;
    }
    
    .card-header .d-flex {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px !important;
        margin-bottom: 6px !important;
    }
    
    .card-header .text-center {
        text-align: center !important;
        margin-left: auto !important;
        margin-right: auto !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }
    
    .card-header .text-center h4 {
        text-align: center !important;
        margin-left: auto !important;
        margin-right: auto !important;
    }
    
    .card-header .text-center div {
        text-align: center !important;
    }
    
    .card-header .border-top {
        padding-top: 6px !important;
        margin-top: 6px !important;
    }
    
    .card-body {
        padding: 8px 12px 65px 12px !important;
        page-break-inside: avoid !important;
        font-size: 0.85rem !important;
        flex: 1 1 auto !important;
        overflow: hidden !important;
        display: flex !important;
        flex-direction: column !important;
        min-height: 0 !important;
        flex-shrink: 1 !important;
        position: relative !important;
        margin-bottom: 0 !important;
        box-sizing: border-box !important;
    }
    
    .bulletin-footer {
        position: absolute !important;
        bottom: 0 !important;
        left: 0 !important;
        right: 0 !important;
        width: 100% !important;
        padding: 6px 12px 6px 12px !important;
        flex-shrink: 0 !important;
        margin-bottom: 0 !important;
        margin-top: auto !important;
        height: 85px !important;
        max-height: 85px !important;
        min-height: 85px !important;
        overflow: visible !important;
        background: white !important;
        z-index: 100 !important;
        box-sizing: border-box !important;
        page-break-inside: avoid !important;
        page-break-after: avoid !important;
        border-top: 1px solid #dee2e6 !important;
    }
    
    .bulletin-footer .row {
        margin-left: 0 !important;
        margin-right: 0 !important;
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: nowrap !important;
    }
    
    .bulletin-footer .col-md-6 {
        padding-left: 0 !important;
        padding-right: 0 !important;
        display: inline-block !important;
        float: none !important;
        width: 50% !important;
        max-width: 50% !important;
        flex: 0 0 50% !important;
        vertical-align: top !important;
    }
    
    .bulletin-footer .col-md-6:first-child {
        padding-right: 8px !important;
    }
    
    .bulletin-footer .col-md-6:last-child {
        padding-left: 8px !important;
    }
    
    .bulletin-footer .col-6 {
        padding-left: 0 !important;
        padding-right: 0 !important;
        display: block !important;
        float: none !important;
        width: 50% !important;
        flex: 0 0 50% !important;
    }
    
    .bulletin-footer .col-6:first-child {
        padding-right: 3px !important;
    }
    
    .bulletin-footer .col-6:last-child {
        padding-left: 3px !important;
    }
    
    .card-body h5 {
        font-size: 1.2rem !important;
        margin-bottom: 2px !important;
        line-height: 1.2 !important;
    }
    
    .card-body h6 {
        font-size: 1rem !important;
        margin-bottom: 2px !important;
        line-height: 1.2 !important;
    }
    
    .card-body p {
        font-size: 1rem !important;
        margin-bottom: 2px !important;
        line-height: 1.2 !important;
    }
    
    .table {
        font-size: 0.9rem !important;
        margin-bottom: 4px !important;
        width: 100% !important;
        border-collapse: collapse !important;
        page-break-inside: avoid !important;
    }
    
    .table th, .table td {
        padding: 6px 5px !important;
        border: 1px solid #333 !important;
        text-align: left !important;
        font-size: 0.95rem !important;
        line-height: 1.2 !important;
    }
    
    .table th {
        font-size: 1rem !important;
        font-weight: 700 !important;
        padding: 6px 5px !important;
    }
    
    .table td span {
        font-size: 1rem !important;
    }
    
    .notes-cell .note-value {
        font-size: 1rem !important;
    }
    
    .notes-cell {
        font-size: 1rem !important;
    }
    
    .table thead {
        background: linear-gradient(135deg, #34495e 0%, #2c3e50 100%) !important;
        color: white !important;
    }
    
    .table thead th {
        background: linear-gradient(135deg, #34495e 0%, #2c3e50 100%) !important;
        color: white !important;
        font-weight: 700 !important;
        border: 1px solid #2c3e50 !important;
    }
    
    .table tbody tr {
        page-break-inside: avoid;
    }
    
    .table tfoot {
        background: #f8f9fa !important;
        font-weight: 700 !important;
    }
    
    .table-responsive {
        overflow-x: auto !important;
        overflow-y: auto !important;
        page-break-inside: avoid !important;
        max-height: calc(28.7cm - 90px - 75px - 120px) !important;
        flex: 1 1 auto !important;
        min-height: 0 !important;
        flex-shrink: 1 !important;
        margin-bottom: 0 !important;
        padding-bottom: 0 !important;
        position: relative !important;
        z-index: 1 !important;
    }
    
    .badge {
        padding: 3px 6px !important;
        font-size: 0.75rem !important;
        border: 1px solid #333 !important;
        line-height: 1.2 !important;
    }
    
    .row {
        margin: 0 !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: nowrap !important;
    }
    
    .row > * {
        padding: 4px 6px !important;
    }
    
    .col-md-12 {
        margin: 0 !important;
        padding: 0 !important;
    }
    
    .col-md-6 {
        display: block !important;
        float: none !important;
        width: 50% !important;
        flex: 0 0 50% !important;
        max-width: 50% !important;
    }
    
    .card-header .border-top {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }
    
    .card-header .row {
        margin-left: 0 !important;
        margin-right: 0 !important;
    }
    
    .card-header .row .col-md-6:first-child {
        padding-left: 0 !important;
        padding-right: 5px !important;
    }
    
    .card-header .row .col-md-6.text-end,
    .card-header .row .col-md-6:last-child {
        padding-left: 5px !important;
        padding-right: 0 !important;
    }
    
    .mb-1, .mb-2, .mb-3, .mb-4, .mb-5 {
        margin-bottom: 6px !important;
    }
    
    .mt-1, .mt-2, .mt-3 {
        margin-top: 6px !important;
    }
    
    /* Assurer que les couleurs s'affichent */
    .bg-success {
        background-color: #28a745 !important;
        color: white !important;
    }
    
    .bg-danger {
        background-color: #dc3545 !important;
        color: white !important;
    }
    
    .bg-info {
        background-color: #17a2b8 !important;
        color: white !important;
    }
    
    .bg-warning {
        background-color: #ffc107 !important;
        color: #000 !important;
    }
    
    .bg-secondary {
        background-color: #6c757d !important;
        color: white !important;
    }
}

/* Styles pour l'écran */
.bulletin-page {
    background: white;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    margin-bottom: 20px;
    border: 3px solid #2c3e50;
    border-radius: 8px;
    overflow: hidden;
    width: 21cm;
    min-height: 29.7cm;
    max-height: 29.7cm;
    margin: 0 auto 20px auto;
    padding: 0;
    box-sizing: border-box;
}

.bulletin-page .card {
    border: none;
    box-shadow: none;
    border-radius: 0;
    height: 100%;
    min-height: 100%;
    max-height: 100%;
}

.bulletin-page .table {
    margin-bottom: 0;
    border-collapse: separate;
    border-spacing: 0;
}

.bulletin-page .table th {
    background: #f8f9fa;
    font-weight: 600;
    border: 1px solid #dee2e6;
}

.bulletin-page .table td {
    border: 1px solid #dee2e6;
    vertical-align: middle;
}

.bulletin-page .table tbody tr:hover {
    background-color: #f8f9fa;
}

.bulletin-page .badge {
    font-weight: 600;
    border-radius: 6px;
}

.bulletin-page .badge.bg-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
}

.bulletin-page .badge.bg-danger {
    background: linear-gradient(135deg, #dc3545 0%, #e74c3c 100%) !important;
}

.bulletin-page .badge.bg-info {
    background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%) !important;
}

.bulletin-page .badge.bg-warning {
    background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%) !important;
}

.bulletin-page .badge.bg-secondary {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%) !important;
}

@media screen and (max-width: 768px) {
    .bulletin-page {
        width: 100%;
        margin: 0 0 20px 0;
        min-height: auto;
    }
    
    .bulletin-page .card {
        height: auto;
        min-height: auto;
    }
}
</style>
@endpush

@section('scripts')
<script>
// Scripts optionnels si nécessaire
</script>
@endsection

