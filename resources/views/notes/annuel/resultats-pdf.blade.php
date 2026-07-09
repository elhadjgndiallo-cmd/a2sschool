<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultats Annuels - {{ $classe->nom }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 1cm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 10px;
            line-height: 1.3;
            color: #000;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #000;
        }

        .header h1 {
            font-size: 16px;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .header .info {
            font-size: 11px;
            color: #555;
        }

        .classe-info {
            background-color: #f8f9fa;
            padding: 8px;
            margin-bottom: 15px;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
        }

        .classe-info strong {
            color: #000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table th,
        table td {
            border: 1px solid #000;
            padding: 6px 4px;
            text-align: center;
        }

        table th {
            background-color: #343a40;
            color: white;
            font-weight: bold;
            font-size: 9px;
        }

        table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        table tbody td {
            font-size: 9px;
        }

        .text-left {
            text-align: left !important;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }

        .badge-success {
            background-color: #28a745;
            color: white;
        }

        .badge-info {
            background-color: #17a2b8;
            color: white;
        }

        .badge-warning {
            background-color: #ffc107;
            color: #000;
        }

        .badge-secondary {
            background-color: #6c757d;
            color: white;
        }

        .badge-primary {
            background-color: #007bff;
            color: white;
        }

        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ccc;
            text-align: center;
            font-size: 8px;
            color: #666;
        }
    </style>
</head>
<body>
    <!-- En-tête -->
    <div class="header">
        <h1>Résultats Annuels - Classement</h1>
        <div class="info">
            Classe : <strong>{{ $classe->nom }}</strong> | 
            Année scolaire : <strong>{{ $anneeScolaireActive->nom }}</strong> | 
            Généré le : <strong>{{ now()->format('d/m/Y à H:i') }}</strong>
        </div>
    </div>

    <!-- Informations de la classe -->
    <div class="classe-info">
        <div>
            <strong>Classe :</strong> {{ $classe->nom }}
        </div>
        <div>
            <strong>Effectif :</strong> {{ count($resultats) }} élèves
        </div>
        <div>
            <strong>Année scolaire :</strong> {{ $anneeScolaireActive->nom }}
        </div>
    </div>

    <!-- Tableau des résultats -->
    @if(count($resultats) > 0)
    <table>
        <thead>
            <tr>
                <th style="width: 8%">Matricule</th>
                <th style="width: 12%" class="text-left">Nom</th>
                <th style="width: 12%" class="text-left">Prénom</th>
                <th style="width: 10%">Moyenne T1</th>
                <th style="width: 8%">Rang T1</th>
                <th style="width: 10%">Moyenne T2</th>
                <th style="width: 8%">Rang T2</th>
                @if($isPrimaire)
                <th style="width: 10%">Moyenne T3</th>
                <th style="width: 8%">Rang T3</th>
                @endif
                <th style="width: 10%">Moyenne Annuelle</th>
                <th style="width: 8%">Rang Annuel</th>
            </tr>
        </thead>
        <tbody>
            @foreach($resultats as $resultat)
            <tr>
                <td>{{ $resultat['matricule'] }}</td>
                <td class="text-left">{{ $resultat['eleve']->utilisateur->nom }}</td>
                <td class="text-left">{{ $resultat['eleve']->utilisateur->prenom }}</td>
                <td>
                    @if($resultat['moyenneT1'] !== null)
                        {{ number_format($resultat['moyenneT1'], 2) }}
                    @else
                        -
                    @endif
                </td>
                <td>
                    @if(isset($resultat['rangT1']))
                        <strong>{{ $resultat['rangT1'] }}</strong>
                    @else
                        -
                    @endif
                </td>
                <td>
                    @if($resultat['moyenneT2'] !== null)
                        {{ number_format($resultat['moyenneT2'], 2) }}
                    @else
                        -
                    @endif
                </td>
                <td>
                    @if(isset($resultat['rangT2']))
                        <strong>{{ $resultat['rangT2'] }}</strong>
                    @else
                        -
                    @endif
                </td>
                @if($isPrimaire)
                <td>
                    @if($resultat['moyenneT3'] !== null)
                        {{ number_format($resultat['moyenneT3'], 2) }}
                    @else
                        -
                    @endif
                </td>
                <td>
                    @if(isset($resultat['rangT3']))
                        <strong>{{ $resultat['rangT3'] }}</strong>
                    @else
                        -
                    @endif
                </td>
                @endif
                <td>
                    @if($resultat['moyenneAnnuelle'] !== null)
                        <strong>{{ number_format($resultat['moyenneAnnuelle'], 2) }}</strong>
                    @else
                        -
                    @endif
                </td>
                <td>
                    @if(isset($resultat['rangAnnuel']))
                        @if($resultat['rangAnnuel'] == 1)
                            <span class="badge badge-success">🏆 {{ $resultat['rangAnnuel'] }}</span>
                        @elseif($resultat['rangAnnuel'] == 2)
                            <span class="badge badge-info">🥈 {{ $resultat['rangAnnuel'] }}</span>
                        @elseif($resultat['rangAnnuel'] == 3)
                            <span class="badge badge-warning">🥉 {{ $resultat['rangAnnuel'] }}</span>
                        @else
                            <strong>{{ $resultat['rangAnnuel'] }}</strong>
                        @endif
                    @else
                        -
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div style="text-align: center; padding: 40px; color: #999;">
        <p style="font-size: 14px;">Aucun résultat disponible pour cette classe.</p>
    </div>
    @endif

    <!-- Pied de page -->
    <div class="footer">
        <p>Document généré par le système de gestion scolaire - {{ config('app.name') }}</p>
        <p>Date d'impression : {{ now()->format('d/m/Y à H:i') }}</p>
    </div>
</body>
</html>
