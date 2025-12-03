@extends('layouts.app')

@section('title', 'Rapport Journalier - Comptabilité')

@section('content')
<style>
    /* Styles pour l'impression */
    @media print {
        @page {
            size: A4 portrait;
            margin: 0.8cm;
        }
        
        body {
            margin: 0;
            padding: 0 0 40px 0;
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.4;
            color: #000;
            background: white;
        }
        
        .no-print {
            display: none !important;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .header {
            margin-bottom: 5px;
            border-bottom: 1px solid #000;
            padding-bottom: 3px;
        }
        
        .header-content {
            text-align: center;
        }
        
        .school-info {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 5px;
            gap: 8px;
        }
        
        .school-logo {
            flex-shrink: 0;
        }
        
        .logo-image {
            max-width: 35px;
            max-height: 35px;
            object-fit: contain;
        }
        
        .school-details {
            text-align: left;
        }
        
        .school-name {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
            color: #000;
            line-height: 1.1;
        }
        
        .school-slogan {
            margin: 1px 0 0 0;
            font-size: 10px;
            color: #666;
            font-style: italic;
            line-height: 1.1;
        }
        
        .document-title {
            margin: 3px 0;
        }
        
        .document-title h2 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
            color: #000;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .document-info {
            margin-top: 2px;
        }
        
        .generation-info {
            margin: 0;
            font-size: 10px;
            color: #666;
            font-style: italic;
        }
        
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            font-size: 10px;
            color: #666;
            padding: 5px;
            background-color: white;
            border-top: 1px solid #ccc;
        }
        
        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 5px;
        }
        
        .footer-school-info {
            flex: 1;
            min-width: 150px;
        }
        
        .footer-document-info {
            flex: 1;
            text-align: right;
            min-width: 150px;
        }
        
        .school-address,
        .school-phone {
            margin: 0;
            font-size: 9px;
            line-height: 1.1;
        }
        
        .footer-document-info p {
            margin: 0;
            font-size: 9px;
            color: #666;
            font-style: italic;
        }
        
        .report-content {
            margin-top: 10px;
        }
        
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        
        .report-table th,
        .report-table td {
            border: 1px solid #000;
            padding: 3px;
            font-size: 11px;
        }
        
        .report-table th {
            background-color: #f5f5f5;
            font-weight: bold;
            text-align: center;
        }
        
        .report-table td {
            text-align: left;
        }
        
        .report-table .text-end {
            text-align: right;
        }
        
        .summary-cards {
            display: none;
        }
        
        .print-only {
            display: block !important;
        }
    }
    
    /* Masquer l'en-tête et le pied de page à l'écran */
    .print-only {
        display: none;
    }
</style>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
                <h2 class="mb-0 mb-md-0">
                    <i class="fas fa-calendar-day text-primary me-2"></i>
                    <span class="d-none d-sm-inline">Rapport Journalier</span>
                    <span class="d-sm-none">Rapport</span>
                </h2>
                <div class="btn-group w-100 w-md-auto">
                    <button onclick="window.print()" class="btn btn-outline-primary">
                        <i class="fas fa-print me-1"></i><span class="d-none d-sm-inline">Imprimer</span>
                    </button>
                    <a href="{{ route('comptabilite.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i><span class="d-none d-sm-inline">Retour</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- En-tête pour l'impression -->
    <div class="header print-only">
        <div class="header-content">
            <!-- Logo et nom de l'école -->
            <div class="school-info">
                @php
                    $schoolInfo = \App\Helpers\SchoolHelper::getSchoolInfo();
                    $logoUrl = $schoolInfo && isset($schoolInfo->logo) && $schoolInfo->logo ? asset('storage/' . $schoolInfo->logo) : null;
                    $schoolName = $schoolInfo && isset($schoolInfo->nom) ? $schoolInfo->nom : config('app.name', 'A2S School');
                    $schoolSlogan = $schoolInfo && isset($schoolInfo->slogan) ? $schoolInfo->slogan : '';
                @endphp
                @if($logoUrl)
                    <div class="school-logo">
                        <img src="{{ $logoUrl }}" alt="Logo de l'école" class="logo-image">
                    </div>
                @endif
                <div class="school-details">
                    <h1 class="school-name">{{ $schoolName }}</h1>
                    @if($schoolSlogan)
                        <p class="school-slogan">"{{ $schoolSlogan }}"</p>
                    @endif
                </div>
            </div>
            
            <!-- Titre du document -->
            <div class="document-title">
                <h2>
                    @if(request('type') == 'mois')
                        RAPPORT MENSUEL DE COMPTABILITÉ
                    @elseif(request('type') == 'annee')
                        RAPPORT ANNUEL DE COMPTABILITÉ
                    @else
                        RAPPORT JOURNALIER DE COMPTABILITÉ
                    @endif
                </h2>
            </div>
            
            <!-- Informations de génération -->
            <div class="document-info">
                <p class="generation-info">
                    Généré le {{ now()->format('d/m/Y à H:i') }} | 
                    @if(request('type') == 'mois')
                        Période: {{ \Carbon\Carbon::parse(request('month', now()->format('Y-m')))->format('F Y') }}
                    @elseif(request('type') == 'annee')
                        Année: {{ request('year', now()->year) }}
                    @else
                        Date: {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
                    @endif
                </p>
            </div>
        </div>
    </div>

    <!-- Filtres de date -->
    <div class="row mb-4 no-print">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="fas fa-filter me-2"></i>Filtrer par période
                    </h6>
                    <form method="GET" action="{{ route('comptabilite.rapport-journalier') }}" id="filterForm">
                        <div class="row">
                            <div class="col-md-3">
                                <label class="form-label">Type de rapport</label>
                                <select name="type" id="reportType" class="form-select" onchange="toggleDateInputs()">
                                    <option value="jour" {{ request('type', 'jour') == 'jour' ? 'selected' : '' }}>Journalier</option>
                                    <option value="mois" {{ request('type') == 'mois' ? 'selected' : '' }}>Mensuel</option>
                                    <option value="annee" {{ request('type') == 'annee' ? 'selected' : '' }}>Annuel</option>
                                </select>
                            </div>
                            <div class="col-md-3" id="dateInput">
                                <label class="form-label">Date</label>
                                <input type="date" 
                                       name="date" 
                                       value="{{ $date }}" 
                                       class="form-control"
                                       max="{{ now()->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-3" id="monthInput" style="display: none;">
                                <label class="form-label">Mois</label>
                                <input type="month" 
                                       name="month" 
                                       value="{{ request('month', now()->format('Y-m')) }}" 
                                       class="form-control"
                                       max="{{ now()->format('Y-m') }}">
                            </div>
                            <div class="col-md-3" id="yearInput" style="display: none;">
                                <label class="form-label">Année</label>
                                <select name="year" class="form-select">
                                    @for($year = now()->year; $year >= 2020; $year--)
                                        <option value="{{ $year }}" {{ request('year', now()->year) == $year ? 'selected' : '' }}>
                                            {{ $year }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-1"></i>Générer
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="fas fa-info-circle me-2"></i>Informations
                    </h6>
                    <div class="small text-muted">
                        <p class="mb-1"><strong>Journalier:</strong> Transactions d'une journée</p>
                        <p class="mb-1"><strong>Mensuel:</strong> Toutes les transactions du mois</p>
                        <p class="mb-0"><strong>Annuel:</strong> Toutes les transactions de l'année</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Résumé de la période -->
    <div class="row mb-4 no-print">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="fas fa-info-circle me-2"></i>Résumé de la journée
                    </h6>
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="text-success">
                                <strong>{{ number_format($totalEntrees, 0, ',', ' ') }} GNF</strong>
                                <br><small>Entrées</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="text-danger">
                                <strong>{{ number_format($totalSorties, 0, ',', ' ') }} GNF</strong>
                                <br><small>Sorties</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="text-primary">
                                <strong>{{ number_format($soldeFinal, 0, ',', ' ') }} GNF</strong>
                                <br><small>Solde final</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Journal des transactions -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-book me-2"></i>
                        Journal du {{ $dateCarbon->format('d/m/Y') }}
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($journal->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th style="width: 15%">Date</th>
                                        <th style="width: 40%">Libellé</th>
                                        <th style="width: 15%" class="text-end">Entrée</th>
                                        <th style="width: 15%" class="text-end">Sortie</th>
                                        <th style="width: 15%" class="text-end">Solde</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($journal as $transaction)
                                    <tr>
                                        <td>
                                            <small class="text-muted">
                                                {{ \Carbon\Carbon::parse($transaction['date'])->format('d/m/Y') }}
                                                <br>
                                                {{ \Carbon\Carbon::parse($transaction['created_at'])->format('H:i') }}
                                            </small>
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $transaction['libelle'] }}</strong>
                                                <br>
                                                <small class="text-muted">
                                                    {{ $transaction['source'] }}
                                                    @if($transaction['enregistre_par'])
                                                        - Enregistré par {{ $transaction['enregistre_par']->prenom }} {{ $transaction['enregistre_par']->nom }}
                                                    @endif
                                                </small>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            @if($transaction['entree'] > 0)
                                                <span class="text-success fw-bold">
                                                    +{{ number_format($transaction['entree'], 0, ',', ' ') }} GNF
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if($transaction['sortie'] > 0)
                                                <span class="text-danger fw-bold">
                                                    -{{ number_format($transaction['sortie'], 0, ',', ' ') }} GNF
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <span class="fw-bold {{ $transaction['solde'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                {{ number_format($transaction['solde'], 0, ',', ' ') }} GNF
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                    
                                    <!-- Totaux -->
                                    <tr class="table-dark">
                                        <td colspan="2" class="fw-bold">TOTAUX</td>
                                        <td class="text-end fw-bold text-success">
                                            {{ number_format($totalEntrees, 0, ',', ' ') }} GNF
                                        </td>
                                        <td class="text-end fw-bold text-danger">
                                            {{ number_format($totalSorties, 0, ',', ' ') }} GNF
                                        </td>
                                        <td class="text-end fw-bold text-primary">
                                            {{ number_format($soldeFinal, 0, ',', ' ') }} GNF
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Aucune transaction pour cette date</h5>
                            <p class="text-muted">Il n'y a pas d'entrées ou de sorties enregistrées pour le {{ $dateCarbon->format('d/m/Y') }}.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pied de page pour l'impression -->
<div class="footer print-only">
    <div class="footer-content">
        <div class="footer-school-info">
            @php
                $schoolInfo = \App\Helpers\SchoolHelper::getSchoolInfo();
                $schoolName = $schoolInfo && isset($schoolInfo->nom) ? $schoolInfo->nom : config('app.name', 'A2S School');
                $schoolAddress = $schoolInfo && isset($schoolInfo->adresse) ? $schoolInfo->adresse : '';
                $schoolPhone = $schoolInfo && isset($schoolInfo->telephone) ? $schoolInfo->telephone : '';
                $schoolEmail = $schoolInfo && isset($schoolInfo->email) ? $schoolInfo->email : '';
            @endphp
            <p class="school-address">{{ $schoolName }}</p>
            @if($schoolAddress)
                <p class="school-address">{{ $schoolAddress }}</p>
            @endif
            @if($schoolPhone || $schoolEmail)
                <p class="school-phone">
                    @if($schoolPhone)
                        Tél: {{ $schoolPhone }}
                    @endif
                    @if($schoolPhone && $schoolEmail)
                         | 
                    @endif
                    @if($schoolEmail)
                        Email: {{ $schoolEmail }}
                    @endif
                </p>
            @endif
        </div>
        <div class="footer-document-info">
            <p>Rapport généré le {{ now()->format('d/m/Y à H:i') }}</p>
            <p>Date du rapport: {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</p>
        </div>
    </div>
</div>

@push('styles')
<style>
    @media print {
        .btn-group, .card-header .btn, .navbar, .sidebar {
            display: none !important;
        }
        
        .container-fluid {
            padding: 0 !important;
        }
        
        .card {
            border: none !important;
            box-shadow: none !important;
        }
        
        .table {
            font-size: 12px;
        }
        
        .table th, .table td {
            padding: 8px 4px !important;
        }
        
        body {
            font-size: 12px;
        }
        
        h2 {
            font-size: 18px;
        }
        
        h5 {
            font-size: 14px;
        }
    }
</style>
@endpush
@endsection

@push('scripts')
<script>
function toggleDateInputs() {
    const reportType = document.getElementById('reportType').value;
    const dateInput = document.getElementById('dateInput');
    const monthInput = document.getElementById('monthInput');
    const yearInput = document.getElementById('yearInput');
    
    // Masquer tous les inputs
    dateInput.style.display = 'none';
    monthInput.style.display = 'none';
    yearInput.style.display = 'none';
    
    // Afficher le bon input selon le type
    switch(reportType) {
        case 'jour':
            dateInput.style.display = 'block';
            break;
        case 'mois':
            monthInput.style.display = 'block';
            break;
        case 'annee':
            yearInput.style.display = 'block';
            break;
    }
}

// Initialiser l'affichage au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    toggleDateInputs();
});
</script>
@endpush
