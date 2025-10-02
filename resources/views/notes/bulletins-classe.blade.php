@extends('layouts.app')

@section('title', 'Bulletins de Notes - ' . $classe->nom)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4 no-print">
                <h2><i class="fas fa-file-alt me-2"></i>Bulletins de Notes - {{ $classe->nom }}</h2>
                <div class="no-print">
                    <button onclick="window.print()" class="btn btn-primary">
                        <i class="fas fa-print me-2"></i>Imprimer
                    </button>
                    <a href="{{ route('notes.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Retour
                    </a>
                </div>
            </div>

            @foreach($bulletins as $bulletin)
            <div class="bulletin-page mb-5" style="page-break-after: always;">
                <div class="card">
                    <div class="card-header" style="background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%); color: white; border: none;">
                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="mb-0" style="font-weight: 700; text-shadow: 1px 1px 2px rgba(0,0,0,0.3);">BULLETIN DE NOTES</h4>
                                <small style="opacity: 0.9;">{{ $classe->nom }} - {{ $classe->niveau }}</small>
                            </div>
                            <div class="col-md-6 text-end">
                                <h5 style="font-weight: 600;">Année Scolaire {{ date('Y') }}-{{ date('Y')+1 }}</h5>
                                <small style="opacity: 0.9;">Trimestre 1</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <!-- Informations élève -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5><strong>{{ $bulletin['eleve']->nom_complet }}</strong></h5>
                                <p class="mb-1"><strong>Numéro:</strong> {{ $bulletin['eleve']->numero_etudiant }}</p>
                                <p class="mb-1"><strong>Date de naissance:</strong> {{ $bulletin['eleve']->date_naissance ? $bulletin['eleve']->date_naissance->format('d/m/Y') : 'Non renseignée' }}</p>
                            </div>
                            <div class="col-md-6 text-end">
                                <div style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                    <h4 class="mb-0" style="font-weight: 700;">Rang: {{ $bulletin['rang'] }}/{{ count($bulletins) }}</h4>
                                    <p class="mb-0" style="font-size: 1.1rem;">Moyenne générale: <strong>{{ number_format($bulletin['moyenne_generale'] ?? 0, 2) }}/20</strong></p>
                                </div>
                            </div>
                        </div>

                        <!-- Tableau des notes par matière -->
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead style="background: linear-gradient(135deg, #34495e 0%, #2c3e50 100%); color: white;">
                                    <tr>
                                        <th style="font-weight: 700; border: 1px solid #2c3e50;">Matière</th>
                                        <th style="font-weight: 700; border: 1px solid #2c3e50; text-align: center;">Coefficient</th>
                                        <th style="font-weight: 700; border: 1px solid #2c3e50; text-align: center;">Note Cours</th>
                                        <th style="font-weight: 700; border: 1px solid #2c3e50; text-align: center;">Note Composition</th>
                                        <th style="font-weight: 700; border: 1px solid #2c3e50; text-align: center;">Note Finale</th>
                                        <th style="font-weight: 700; border: 1px solid #2c3e50; text-align: center;">Points</th>
                                        <th style="font-weight: 700; border: 1px solid #2c3e50; text-align: center;">Appréciation</th>
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
                                            <td style="font-weight: 600; padding: 12px; background-color: #f8f9fa;"><strong>{{ $matiere }}</strong></td>
                                            <td class="text-center" style="padding: 12px; font-weight: 600; background-color: #e9ecef;">{{ $data['coefficient'] }}</td>
                                            <td class="text-center" style="padding: 12px;">
                                                <span class="badge {{ $data['note_cours'] >= 10 ? 'bg-success' : 'bg-danger' }}" style="font-size: 0.9rem; padding: 6px 10px;">
                                                    {{ $data['note_cours'] > 0 ? number_format($data['note_cours'], 2) : '-' }}/20
                                                </span>
                                            </td>
                                            <td class="text-center" style="padding: 12px;">
                                                <span class="badge {{ $data['note_composition'] >= 10 ? 'bg-success' : 'bg-danger' }}" style="font-size: 0.9rem; padding: 6px 10px;">
                                                    {{ $data['note_composition'] > 0 ? number_format($data['note_composition'], 2) : '-' }}/20
                                                </span>
                                            </td>
                                            <td class="text-center" style="padding: 12px;">
                                                <span class="badge {{ $data['note_finale'] >= 10 ? 'bg-success' : 'bg-danger' }}" style="font-size: 0.9rem; padding: 6px 10px; font-weight: 700;">
                                                    {{ number_format($data['note_finale'], 2) }}/20
                                                </span>
                                            </td>
                                            <td class="text-center" style="padding: 12px; font-weight: 600; background-color: #e9ecef;">{{ $data['points'] }}</td>
                                            <td style="padding: 12px;">
                                                @if($data['note_finale'] >= 16)
                                                    <span class="badge bg-success" style="font-size: 0.85rem; padding: 4px 8px;">Excellent</span>
                                                @elseif($data['note_finale'] >= 14)
                                                    <span class="badge bg-info" style="font-size: 0.85rem; padding: 4px 8px;">Très bien</span>
                                                @elseif($data['note_finale'] >= 12)
                                                    <span class="badge bg-warning text-dark" style="font-size: 0.85rem; padding: 4px 8px;">Bien</span>
                                                @elseif($data['note_finale'] >= 10)
                                                    <span class="badge bg-secondary" style="font-size: 0.85rem; padding: 4px 8px;">Assez bien</span>
                                                @else
                                                    <span class="badge bg-danger" style="font-size: 0.85rem; padding: 4px 8px;">Insuffisant</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-top: 3px solid #2c3e50;">
                                        <th style="font-weight: 700; padding: 15px; font-size: 1.1rem; color: #2c3e50;">MOYENNE GÉNÉRALE</th>
                                        <th class="text-center" style="font-weight: 700; padding: 15px; font-size: 1.1rem; color: #2c3e50;">{{ $totalCoeff }}</th>
                                        <th class="text-center" style="font-weight: 700; padding: 15px; font-size: 1.1rem; color: #6c757d;">-</th>
                                        <th class="text-center" style="font-weight: 700; padding: 15px; font-size: 1.1rem; color: #6c757d;">-</th>
                                        <th class="text-center" style="font-weight: 700; padding: 15px;">
                                            <span class="badge {{ ($bulletin['moyenne_generale'] ?? 0) >= 10 ? 'bg-success' : 'bg-danger' }}" style="font-size: 1rem; padding: 8px 15px; font-weight: 700;">
                                                {{ number_format($bulletin['moyenne_generale'] ?? 0, 2) }}/20
                                            </span>
                                        </th>
                                        <th class="text-center" style="font-weight: 700; padding: 15px; font-size: 1.1rem; color: #2c3e50;">{{ round($totalPoints, 2) }}</th>
                                        <th style="font-weight: 700; padding: 15px;">
                                            @php $moy = $bulletin['moyenne_generale'] ?? 0; @endphp
                                            @if($moy >= 16)
                                                <span class="badge bg-success" style="font-size: 0.9rem; padding: 6px 12px; font-weight: 700;">Excellent</span>
                                            @elseif($moy >= 14)
                                                <span class="badge bg-info" style="font-size: 0.9rem; padding: 6px 12px; font-weight: 700;">Très bien</span>
                                            @elseif($moy >= 12)
                                                <span class="badge bg-warning text-dark" style="font-size: 0.9rem; padding: 6px 12px; font-weight: 700;">Bien</span>
                                            @elseif($moy >= 10)
                                                <span class="badge bg-secondary" style="font-size: 0.9rem; padding: 6px 12px; font-weight: 700;">Assez bien</span>
                                            @else
                                                <span class="badge bg-danger" style="font-size: 0.9rem; padding: 6px 12px; font-weight: 700;">Insuffisant</span>
                                            @endif
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Observations -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div style="border: 2px solid #2c3e50; border-radius: 8px; padding: 20px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                                    <h6 style="color: #2c3e50; font-weight: 700; margin-bottom: 15px; border-bottom: 2px solid #2c3e50; padding-bottom: 8px;"><strong>Observations du conseil de classe:</strong></h6>
                                    <div style="min-height: 80px; font-style: italic; color: #495057; line-height: 1.6;">
                                        @php $moy = $bulletin['moyenne_generale'] ?? 0; @endphp
                                        @if($moy >= 16)
                                            <span style="color: #28a745; font-weight: 600;">Excellent travail. Félicitations pour ces très bons résultats.</span>
                                        @elseif($moy >= 14)
                                            <span style="color: #17a2b8; font-weight: 600;">Bon travail. Continuez dans cette voie.</span>
                                        @elseif($moy >= 12)
                                            <span style="color: #ffc107; font-weight: 600;">Travail satisfaisant. Peut mieux faire.</span>
                                        @elseif($moy >= 10)
                                            <span style="color: #6c757d; font-weight: 600;">Résultats passables. Des efforts sont nécessaires.</span>
                                        @else
                                            <span style="color: #dc3545; font-weight: 600;">Résultats insuffisants. Un travail sérieux s'impose.</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div style="border: 2px solid #2c3e50; border-radius: 8px; padding: 20px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                                    <h6 style="color: #2c3e50; font-weight: 700; margin-bottom: 15px; border-bottom: 2px solid #2c3e50; padding-bottom: 8px;"><strong>Signatures:</strong></h6>
                                    <div class="row mt-3">
                                        <div class="col-6">
                                            <p class="text-center" style="font-weight: 600; color: #2c3e50; margin-bottom: 10px;">Le Directeur</p>
                                            <div style="height: 50px; border-bottom: 2px solid #2c3e50; margin-bottom: 10px;"></div>
                                            <small class="text-center d-block" style="color: #6c757d;">Date: _______________</small>
                                        </div>
                                        <div class="col-6">
                                            <p class="text-center" style="font-weight: 600; color: #2c3e50; margin-bottom: 10px;">Le Parent</p>
                                            <div style="height: 50px; border-bottom: 2px solid #2c3e50; margin-bottom: 10px;"></div>
                                            <small class="text-center d-block" style="color: #6c757d;">Date: _______________</small>
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

@push('styles')
<style>
/* Styles pour l'impression A4 */
@media print {
    @page {
        size: A4;
        margin: 1cm;
    }
    
    * {
        -webkit-print-color-adjust: exact !important;
        color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    
    body {
        font-size: 12px;
        line-height: 1.4;
    }
    
    .btn, .card-header { 
        -webkit-print-color-adjust: exact !important;
        color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    
    .bulletin-page {
        page-break-after: always;
        margin-bottom: 0;
        width: 100%;
        max-width: none;
        box-shadow: none;
        border: 1px solid #ddd;
    }
    
    .bulletin-page:last-child {
        page-break-after: avoid;
    }
    
    .card {
        border: 1px solid #333;
        box-shadow: none;
    }
    
    .card-header {
        background: #f8f9fa !important;
        border-bottom: 2px solid #333;
        padding: 10px;
    }
    
    .card-body {
        padding: 15px;
    }
    
    .table {
        font-size: 11px;
        margin-bottom: 10px;
    }
    
    .table th, .table td {
        padding: 5px;
        border: 1px solid #333;
    }
    
    .table-dark th {
        background: #333 !important;
        color: white !important;
    }
    
    .alert {
        border: 1px solid #333;
        padding: 8px;
        margin-bottom: 10px;
    }
    
    .border {
        border: 1px solid #333 !important;
    }
    
    /* Masquer les éléments non nécessaires à l'impression */
    .no-print {
        display: none !important;
    }
}

/* Styles pour l'écran */
.bulletin-page {
    background: white;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    margin-bottom: 20px;
    border: 2px solid #2c3e50;
    border-radius: 12px;
    overflow: hidden;
}

.bulletin-page .card {
    border: none;
    box-shadow: none;
    border-radius: 12px;
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

/* Optimisation pour A4 */
.bulletin-page {
    width: 21cm;
    min-height: 29.7cm;
    margin: 0 auto 20px auto;
    padding: 0;
}

@media screen and (max-width: 768px) {
    .bulletin-page {
        width: 100%;
        margin: 0 0 20px 0;
    }
}
</style>
@endpush
@endsection
