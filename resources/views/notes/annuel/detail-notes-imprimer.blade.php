<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détail Notes Annuelles - {{ $classe->nom }}</title>
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
            font-size: 11px;
            line-height: 1.4;
            color: #000;
        }

        .no-print {
            position: fixed;
            top: 10px;
            left: 10px;
            z-index: 1000;
        }

        .btn-retour {
            display: inline-block;
            padding: 8px 16px;
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
        }

        .btn-retour:hover {
            background-color: #5a6268;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #000;
        }

        .header h1 {
            font-size: 18px;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .header .info {
            font-size: 12px;
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
            padding: 6px 8px;
            text-align: left;
        }

        table thead {
            background-color: #343a40;
            color: white;
        }

        table thead th {
            font-weight: bold;
            text-align: center;
            font-size: 9px;
            vertical-align: middle;
            padding: 4px 2px;
        }

        table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        table tbody td {
            font-size: 10px;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }

        .badge-success {
            background-color: #28a745;
            color: white;
        }

        .badge-warning {
            background-color: #ffc107;
            color: #000;
        }

        .badge-danger {
            background-color: #dc3545;
            color: white;
        }

        .badge-secondary {
            background-color: #6c757d;
            color: white;
        }

        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ccc;
            text-align: center;
            font-size: 9px;
            color: #666;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            table {
                page-break-inside: auto;
            }

            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }

            thead {
                display: table-header-group;
            }
        }
    </style>
</head>
<body>
    <!-- Bouton retour (caché à l'impression) -->
    <a href="{{ route('notes.annuel.resultats', $classe->id) }}" class="btn-retour no-print">
        <i class="fas fa-arrow-left" style="margin-right: 5px;"></i>
        Retour
    </a>

    <!-- En-tête -->
    <div class="header">
        <h1>Détail des Notes Annuelles</h1>
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
            <strong>Effectif :</strong> {{ count($detailNotes) }} élèves
        </div>
        <div>
            <strong>Année scolaire :</strong> {{ $anneeScolaireActive->nom }}
        </div>
    </div>

    <!-- Tableau des notes (Format horizontal : 1 élève = 1 ligne) -->
    @if(count($detailNotes) > 0 && count($matieres) > 0)
    <table>
        <thead>
            <tr>
                <th style="width: 60px;">Matricule</th>
                <th style="width: 100px;">Nom</th>
                <th style="width: 100px;">Prénom</th>
                @foreach($matieres as $matiere)
                <th style="text-align: center; font-size: 8px; padding: 4px 2px; max-width: 60px; word-wrap: break-word; white-space: normal;">
                    {{ $matiere->nom }}
                </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($detailNotes as $detail)
            <tr>
                <td style="font-weight: bold; text-align: center; font-size: 9px;">{{ $detail['matricule'] }}</td>
                <td style="font-weight: bold; font-size: 9px;">{{ $detail['eleve']->utilisateur->nom }}</td>
                <td style="font-weight: bold; font-size: 9px;">{{ $detail['eleve']->utilisateur->prenom }}</td>
                @foreach($matieres as $matiere)
                    @php
                        $noteMatiere = collect($detail['notes'])->firstWhere('matiere', $matiere->nom);
                    @endphp
                    <td style="text-align: center; font-size: 9px;">
                        @if($noteMatiere && $noteMatiere['moyenne_annuelle'] !== null)
                            {{ number_format($noteMatiere['moyenne_annuelle'], 2) }}
                        @else
                            -
                        @endif
                    </td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div style="text-align: center; padding: 40px; color: #999;">
        <p style="font-size: 16px;">Aucune donnée disponible pour cette classe.</p>
    </div>
    @endif

    <!-- Pied de page -->
    <div class="footer">
        <p>Document généré par le système de gestion scolaire - {{ config('app.name') }}</p>
        <p>Date d'impression : {{ now()->format('d/m/Y à H:i') }}</p>
    </div>

    <!-- Script pour impression automatique -->
    <script>
        // Imprimer automatiquement au chargement (optionnel)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
