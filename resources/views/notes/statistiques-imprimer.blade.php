@extends('layouts.app')

@section('title', 'Statistiques Trimestrielles - ' . $classe->nom)

@push('styles')
<style>
    @media print {
        /* Masquer absolument tous les menus */
        .top-navbar,
        .navbar,
        .sidebar,
        .sidebar-overlay,
        nav.navbar,
        nav.sidebar,
        .navbar-expand-lg,
        .navbar-collapse,
        .navbar-nav,
        .dropdown-menu,
        .btn-toolbar.no-print {
            display: none !important;
            visibility: hidden !important;
            height: 0 !important;
            width: 0 !important;
            overflow: hidden !important;
            position: absolute !important;
            left: -9999px !important;
        }
        
        /* Réinitialiser le padding du body et du main-content */
        body {
            padding-top: 0 !important;
            margin-top: 0 !important;
        }
        
        .main-content {
            padding-top: 0 !important;
            margin-top: 0 !important;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Bouton retour (masqué à l'impression) -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom no-print">
        <h1 class="h2">
            <i class="fas fa-chart-bar me-2"></i>
            Statistiques Trimestrielles - {{ $classe->nom }}
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <button onclick="window.print()" class="btn btn-primary me-2">
                <i class="fas fa-print me-1"></i>
                Imprimer
            </button>
            <a href="{{ route('notes.statistiques') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Retour
            </a>
        </div>
    </div>

    <script>
        // Ajouter une classe au body pour l'impression
        document.addEventListener('DOMContentLoaded', function() {
            document.body.classList.add('print-page');
        });
    </script>
    
    <style>
        @media print {
            /* Masquer tous les menus et éléments de navigation */
            .no-print,
            .top-navbar,
            .navbar,
            .sidebar,
            .sidebar-overlay,
            nav,
            .navbar-brand,
            .navbar-toggler,
            .navbar-collapse,
            .dropdown-menu,
            .btn-toolbar,
            .main-content > .row:first-child,
            .main-content > .container-fluid:first-child > .row:first-child {
                display: none !important;
                visibility: hidden !important;
                height: 0 !important;
                overflow: hidden !important;
            }
            
            /* Masquer le padding et les marges du layout */
            body {
                padding: 0 !important;
                margin: 0 !important;
            }
            
            .main-content {
                padding: 0 !important;
                margin: 0 !important;
                padding-top: 0 !important;
            }
            
            .container-fluid {
                padding: 0 !important;
                margin: 0 !important;
                max-width: 100% !important;
            }
            
            /* Format de page */
            @page {
                size: A4 landscape;
                margin: 1cm;
            }
            
            body {
                font-family: Arial, sans-serif;
                font-size: 11px;
                line-height: 1.4;
                margin: 0;
                padding: 0;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
            }
            
            .print-container {
                width: 100%;
                max-width: 100%;
                margin: auto;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
            }
            
            .header {
                text-align: center;
                width: 100%;
            }
            
            table {
                margin-left: auto !important;
                margin-right: auto !important;
                display: table !important;
                width: auto !important;
                max-width: 95% !important;
            }
            
            .print-container > * {
                margin-left: auto;
                margin-right: auto;
            }
            
            div[style*="display: flex"] {
                display: flex !important;
                justify-content: center !important;
                width: 100% !important;
            }
        }
        
        .print-container {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            width: 100%;
            margin: 0 auto;
        }
        
        @media print {
            .print-container {
                margin: auto;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
            }
        }
        
        .header {
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        
        .header-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .header-logo {
            max-width: 80px;
            max-height: 80px;
            flex-shrink: 0;
        }
        
        .header-center {
            flex: 1;
            text-align: center;
            padding: 0 20px;
        }
        
        .header-left, .header-right {
            width: 80px;
            flex-shrink: 0;
        }
        
        .header-top .republic {
            color: #dc3545;
            font-weight: bold;
            font-size: 14px;
        }
        
        .header-top .de {
            color: #000;
            font-size: 14px;
        }
        
        .header-top .guinee {
            color: #28a745;
            font-weight: bold;
            font-size: 14px;
        }
        
        .header-top .school-name {
            font-size: 16px;
            font-weight: bold;
            margin-top: 5px;
            color: #333;
        }
        
        .header-top .school-slogan {
            font-size: 12px;
            font-style: italic;
            color: #666;
            margin-top: 3px;
        }
        
        .header-top .school-address {
            font-size: 11px;
            color: #666;
            margin-top: 3px;
        }
        
        .header h1 {
            margin: 10px 0 0 0;
            font-size: 18px;
            font-weight: bold;
            text-align: center;
        }
        
        .header h2 {
            margin: 5px 0 0 0;
            font-size: 14px;
            color: #666;
            text-align: center;
        }
        
        .table-wrapper {
            display: flex;
            justify-content: center;
            width: 100%;
            margin: 20px 0;
        }
        
        table {
            border-collapse: collapse;
            margin: 0 auto;
        }
        
        @media print {
            .table-wrapper {
                display: flex !important;
                justify-content: center !important;
                width: 100% !important;
                margin: 20px auto !important;
            }
            
            table {
                margin-left: auto !important;
                margin-right: auto !important;
                display: table !important;
            }
        }
        
        th, td {
            border: 1px solid #333;
            padding: 6px;
            text-align: center;
            font-size: 20px;
        }
        
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        
        .text-left {
            text-align: left;
        }
        
        .text-success {
            color: #28a745;
            font-weight: bold;
        }
        
        .text-danger {
            color: #dc3545;
            font-weight: bold;
        }
        
        .fw-bold {
            font-weight: bold;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>

    <div class="print-container">
        <div class="header">
            <div style="text-align: center; margin-bottom: 15px;">
                <h1>STATISTIQUES TRIMESTRIELLES</h1>
                <h2>{{ $classe->nom }} - {{ $periode->nom }}</h2>
            </div>
            <div class="header-top">
                <div class="header-left">
                    @if($etablissement && $etablissement->logo)
                        <img src="{{ asset('storage/' . $etablissement->logo) }}" alt="Logo" class="header-logo">
                    @endif
                </div>
                <div class="header-center">
                    <div>
                        <span class="republic">RÉPUBLIQUE</span> <span class="de">de</span> <span class="guinee">GUINÉE</span>
                    </div>
                    @if($etablissement)
                        <div class="school-name">{{ $etablissement->nom }}</div>
                        @if($etablissement->slogan)
                            <div class="school-slogan">{{ $etablissement->slogan }}</div>
                        @endif
                        @if($etablissement->adresse)
                            <div class="school-address">{{ $etablissement->adresse }}</div>
                        @endif
                    @endif
                </div>
                <div class="header-right">
                    @if($etablissement && $etablissement->logo)
                        <img src="{{ asset('storage/' . $etablissement->logo) }}" alt="Logo" class="header-logo">
                    @endif
                </div>
            </div>
        </div>

        @if($stats)
        <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th rowspan="2" class="text-left">Statistiques</th>
                    <th colspan="2">Effectifs</th>
                    <th colspan="2">Composés</th>
                    <th colspan="2">Non composés</th>
                    <th colspan="4">Moyennant</th>
                    <th colspan="4">Non moyennant</th>
                </tr>
                <tr>
                    <th>Total</th>
                    <th>Filles</th>
                    <th>Total</th>
                    <th>Filles</th>
                    <th>Total</th>
                    <th>Filles</th>
                    <th>Total</th>
                    <th>Filles</th>
                    <th>% Total</th>
                    <th>% Filles</th>
                    <th>Total</th>
                    <th>Filles</th>
                    <th>% Total</th>
                    <th>% Filles</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-left fw-bold">Classe</td>
                    <td class="fw-bold">{{ $stats['effectifs']['total'] }}</td>
                    <td class="fw-bold">{{ $stats['effectifs']['filles'] }}</td>
                    <td>{{ $stats['composes']['total'] }}</td>
                    <td>{{ $stats['composes']['filles'] }}</td>
                    <td>{{ $stats['non_composes']['total'] }}</td>
                    <td>{{ $stats['non_composes']['filles'] }}</td>
                    <td class="text-success fw-bold">{{ $stats['moyennant']['total'] }}</td>
                    <td class="text-success fw-bold">{{ $stats['moyennant']['filles'] }}</td>
                    <td class="text-success">{{ $stats['moyennant']['pct_total'] }}%</td>
                    <td class="text-success">{{ $stats['moyennant']['pct_filles'] }}%</td>
                    <td class="text-danger fw-bold">{{ $stats['non_moyennant']['total'] }}</td>
                    <td class="text-danger fw-bold">{{ $stats['non_moyennant']['filles'] }}</td>
                    <td class="text-danger">{{ $stats['non_moyennant']['pct_total'] }}%</td>
                    <td class="text-danger">{{ $stats['non_moyennant']['pct_filles'] }}%</td>
                </tr>
            </tbody>
        </table>
        </div>
        @endif

        <div class="footer">
            <p>Document généré le {{ date('d/m/Y à H:i') }}</p>
        </div>
    </div>
@endsection
