<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détail des Notes - Tests Mensuels - {{ $classe->nom }}</title>
    <style>
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
            padding: 5px;
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
        
        .btn-retour {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1000;
            background-color: #0d6efd;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            display: inline-flex;
            align-items: center;
        }
        
        .btn-retour:hover {
            background-color: #0b5ed7;
            color: white;
            text-decoration: none;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <a href="{{ route('notes.mensuel.resultats', $classe->id) }}?mois={{ $mois }}&annee={{ $annee }}" class="btn-retour no-print">
        <i class="fas fa-arrow-left" style="margin-right: 5px;"></i>
        Retour
    </a>
    
    <div class="header">
        <h1>DÉTAIL DES NOTES - TESTS MENSUELS</h1>
        <h2>Classe: {{ $classe->nom }} - {{ $moisListe[$mois] }} {{ $annee }}</h2>
    </div>
    
    <div class="info-section">
        <p><strong>Classe:</strong> {{ $classe->nom }}</p>
        <p><strong>Niveau:</strong> {{ $classe->niveau }}</p>
        <p><strong>Période:</strong> {{ $moisListe[$mois] }} {{ $annee }}</p>
        <p><strong>Nombre de notes:</strong> {{ count($tests) }}</p>
        <p><strong>Date d'impression:</strong> {{ date('d/m/Y à H:i') }}</p>
    </div>
    
    @if(count($tests) > 0)
    <div class="info-section">
        <h3 style="margin: 0 0 10px 0; font-size: 14px; font-weight: bold;">Détail des notes par élève</h3>
        <table>
            <thead>
                <tr>
                    <th style="width: 10%;">Matricule</th>
                    <th style="width: 20%;" class="text-left">Nom</th>
                    <th style="width: 20%;" class="text-left">Prénom</th>
                    <th style="width: 25%;" class="text-left">Matière</th>
                    <th style="width: 10%;">Note</th>
                    <th style="width: 10%;">Coefficient</th>
                    <th style="width: 5%;">Enseignant</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tests as $test)
                <tr>
                    <td class="bold">{{ $test->eleve->numero_etudiant }}</td>
                    <td class="text-left">{{ $test->eleve->utilisateur->nom }}</td>
                    <td class="text-left">{{ $test->eleve->utilisateur->prenom }}</td>
                    <td class="text-left">{{ $test->matiere->nom }}</td>
                    <td class="bold">
                        {{ number_format($test->note, 2) }}/{{ $classe->note_max }}
                    </td>
                    <td>{{ $test->coefficient }}</td>
                    <td class="text-left" style="font-size: 9px;">
                        {{ $test->enseignant->utilisateur->prenom }} {{ $test->enseignant->utilisateur->nom }}
                    </td>
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
        // Auto-print when page loads (only if not already printed)
        window.onload = function() {
            // Attendre un peu avant d'imprimer pour permettre le chargement complet
            setTimeout(function() {
                window.print();
            }, 500);
        };
        
        // Si l'utilisateur annule l'impression, permettre de revenir en arrière
        window.addEventListener('afterprint', function() {
            // L'utilisateur peut maintenant utiliser le bouton retour
        });
    </script>
</body>
</html>

