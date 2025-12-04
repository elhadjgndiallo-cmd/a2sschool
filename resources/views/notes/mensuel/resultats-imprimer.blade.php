<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultats Tests Mensuels - {{ $classe->nom }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 1cm;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }
        
        .header h2 {
            margin: 5px 0 0 0;
            font-size: 14px;
            color: #666;
        }
        
        .info-section {
            margin-bottom: 15px;
        }
        
        .info-section h3 {
            margin: 0 0 5px 0;
            font-size: 14px;
            font-weight: bold;
        }
        
        .info-section p {
            margin: 2px 0;
            font-size: 11px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        th, td {
            border: 1px solid #333;
            padding: 4px;
            text-align: center;
            font-size: 10px;
        }
        
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        
        .text-left {
            text-align: left;
        }
        
        .text-right {
            text-align: right;
        }
        
        .bold {
            font-weight: bold;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        
        .no-data {
            text-align: center;
            padding: 20px;
            color: #666;
            font-style: italic;
        }
        
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>RÉSULTATS DES TESTS MENSUELS</h1>
        <h2>Classe: {{ $classe->nom }} - {{ $moisListe[$mois] }} {{ $annee }}</h2>
    </div>
    
    <div class="info-section">
        <h3>Informations générales</h3>
        <p><strong>Classe:</strong> {{ $classe->nom }}</p>
        <p><strong>Niveau:</strong> {{ $classe->niveau }}</p>
        <p><strong>Période:</strong> {{ $moisListe[$mois] }} {{ $annee }}</p>
        <p><strong>Effectif:</strong> {{ count($resultats) }} élèves classés</p>
        <p><strong>Date d'impression:</strong> {{ date('d/m/Y à H:i') }}</p>
    </div>
    
    @if(count($resultats) > 0)
    <div class="info-section">
        <h3>Classement des élèves</h3>
        <table>
            <thead>
                <tr>
                    <th style="width: 8%;">Rang</th>
                    <th style="width: 15%;">Matricule</th>
                    <th style="width: 25%;">Nom</th>
                    <th style="width: 25%;">Prénom</th>
                    <th style="width: 12%;">Moyenne</th>
                    <th style="width: 15%;">Mention</th>
                </tr>
            </thead>
            <tbody>
                @foreach($resultats as $resultat)
                @php
                    $eleve = $resultat['eleve'];
                    $moyenne = $resultat['moyenne'];
                    $rang = $resultat['rang'];
                    
                    // Déterminer l'appréciation selon la moyenne
                    if ($moyenne >= 16) {
                        $appreciation = 'Excellent';
                    } elseif ($moyenne >= 14) {
                        $appreciation = 'Très bien';
                    } elseif ($moyenne >= 12) {
                        $appreciation = 'Bien';
                    } elseif ($moyenne >= 10) {
                        $appreciation = 'Assez bien';
                    } elseif ($moyenne >= 8) {
                        $appreciation = 'Passable';
                    } else {
                        $appreciation = 'Insuffisant';
                    }
                @endphp
                <tr>
                    <td class="bold">{{ $rang }}{{ $rang == 1 ? 'er' : 'ème' }}</td>
                    <td class="bold">{{ $eleve->matricule }}</td>
                    <td class="text-left">{{ $eleve->nom }}</td>
                    <td class="text-left">{{ $eleve->prenom }}</td>
                    <td class="bold">
                        @if($moyenne == 0.00)
                            00/{{ $classe->note_max }}
                        @else
                            {{ number_format($moyenne, 2) }}/{{ $classe->note_max }}
                        @endif
                    </td>
                    <td>{{ $appreciation }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="no-data">
        <p>Aucun test mensuel enregistré pour {{ $moisListe[$mois] }} {{ $annee }}</p>
    </div>
    @endif
    
    <div class="footer">
        <p>Document généré le {{ date('d/m/Y à H:i') }} - Système de Gestion Scolaire</p>
    </div>
    
    <script>
        // Auto-print when page loads
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
