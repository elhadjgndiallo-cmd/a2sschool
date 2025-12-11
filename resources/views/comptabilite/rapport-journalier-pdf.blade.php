<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport Journalier - Comptabilité</title>
    <style>
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
            font-size: 11px;
        }
        
        .report-table th,
        .report-table td {
            border: 1px solid #000;
            padding: 4px;
            text-align: left;
        }
        
        .report-table th {
            background-color: #f5f5f5;
            font-weight: bold;
            text-align: center;
        }
        
        .report-table .text-end {
            text-align: right;
        }
        
        .text-success {
            color: #000;
        }
        
        .text-danger {
            color: #000;
        }
        
        .fw-bold {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- En-tête -->
    <div class="header">
        <div class="header-content">
            <!-- Logo et nom de l'école -->
            <div class="school-info">
                @php
                    $schoolInfo = \App\Helpers\SchoolHelper::getSchoolInfo();
                    $logoPath = $schoolInfo && isset($schoolInfo->logo) && $schoolInfo->logo ? storage_path('app/public/' . $schoolInfo->logo) : null;
                    $schoolName = $schoolInfo && isset($schoolInfo->nom) ? $schoolInfo->nom : config('app.name', 'A2S School');
                    $schoolSlogan = $schoolInfo && isset($schoolInfo->slogan) ? $schoolInfo->slogan : '';
                @endphp
                @if($logoPath && file_exists($logoPath))
                    <div class="school-logo">
                        <img src="{{ $logoPath }}" alt="Logo de l'école" class="logo-image">
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
                    @php
                        $reportType = $type ?? request('type', 'jour');
                    @endphp
                    @if($reportType == 'mois')
                        RAPPORT MENSUEL DE COMPTABILITÉ
                    @elseif($reportType == 'annee')
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
                    @php
                        $reportType = $type ?? request('type', 'jour');
                    @endphp
                    @if($reportType == 'mois')
                        Période: {{ \Carbon\Carbon::parse(request('month', now()->format('Y-m')))->format('F Y') }}
                    @elseif($reportType == 'annee')
                        Année: {{ request('year', now()->year) }}
                    @else
                        Date: {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
                    @endif
                </p>
            </div>
        </div>
    </div>

    <!-- Journal des transactions -->
    <div class="report-content">
        @if($journal->count() > 0)
            <table class="report-table">
                <thead>
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
                            <small>
                                {{ \Carbon\Carbon::parse($transaction['date'])->format('d/m/Y') }}
                                <br>
                                {{ \Carbon\Carbon::parse($transaction['created_at'])->format('H:i') }}
                            </small>
                        </td>
                        <td>
                            <div>
                                <strong>{{ $transaction['libelle'] }}</strong>
                                <br>
                                <small>
                                    {{ $transaction['source'] }}
                                    @if($transaction['enregistre_par'])
                                        - Enregistré par {{ $transaction['enregistre_par']->prenom }} {{ $transaction['enregistre_par']->nom }}
                                    @endif
                                </small>
                            </div>
                        </td>
                        <td class="text-end">
                            @if($transaction['entree'] > 0)
                                <span class="fw-bold">
                                    +{{ number_format($transaction['entree'], 0, ',', ' ') }} GNF
                                </span>
                            @else
                                <span>-</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($transaction['sortie'] > 0)
                                <span class="fw-bold">
                                    -{{ number_format($transaction['sortie'], 0, ',', ' ') }} GNF
                                </span>
                            @else
                                <span>-</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <span class="fw-bold">
                                {{ number_format($transaction['solde'], 0, ',', ' ') }} GNF
                            </span>
                        </td>
                    </tr>
                    @endforeach
                    
                    <!-- Totaux -->
                    <tr style="background-color: #333; color: white;">
                        <td colspan="2" class="fw-bold" style="color: white;">TOTAUX</td>
                        <td class="text-end fw-bold" style="color: white;">
                            {{ number_format($totalEntrees, 0, ',', ' ') }} GNF
                        </td>
                        <td class="text-end fw-bold" style="color: white;">
                            {{ number_format($totalSorties, 0, ',', ' ') }} GNF
                        </td>
                        <td class="text-end fw-bold" style="color: white;">
                            {{ number_format($soldeFinal, 0, ',', ' ') }} GNF
                        </td>
                    </tr>
                </tbody>
            </table>
        @else
            <div style="text-align: center; padding: 20px;">
                <h5>Aucune transaction pour cette date</h5>
                <p>Il n'y a pas d'entrées ou de sorties enregistrées pour le {{ $dateCarbon->format('d/m/Y') }}.</p>
            </div>
        @endif
    </div>

    <!-- Pied de page -->
    <div class="footer">
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
</body>
</html>

