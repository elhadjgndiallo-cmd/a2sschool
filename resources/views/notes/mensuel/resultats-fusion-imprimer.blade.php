<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Classement fusionné - {{ $nomsClasses }}</title>
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
            margin-bottom: 16px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .school-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .school-header .logo-slot {
            width: 80px;
            flex-shrink: 0;
            text-align: center;
        }

        .school-header img {
            max-width: 70px;
            max-height: 70px;
            object-fit: contain;
        }

        .school-header-center {
            flex: 1;
            text-align: center;
            padding: 0 12px;
        }

        .school-name {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .school-slogan {
            margin: 3px 0 0 0;
            font-size: 11px;
            font-style: italic;
            color: #555;
        }

        .header h1 {
            margin: 8px 0 0 0;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
        }

        .header h2 {
            margin: 4px 0 0 0;
            font-size: 13px;
            color: #666;
            text-align: center;
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

        .bold {
            font-weight: bold;
        }

        .footer {
            margin-top: 30px;
            border-top: 1px solid #333;
            padding-top: 8px;
            text-align: center;
            font-size: 10px;
            color: #444;
        }

        .footer .school-contact {
            margin: 0 0 4px 0;
            font-size: 11px;
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
    @php
        $school = \App\Helpers\SchoolHelper::getDocumentInfo();
    @endphp
    <a href="{{ route('notes.mensuel.resultats-fusion') }}?{{ $queryFusion }}" class="btn-retour no-print">
        <i class="fas fa-arrow-left" style="margin-right: 5px;"></i>
        Retour
    </a>

    <div class="header">
        <div class="school-header">
            <div class="logo-slot">
                @if(!empty($school['logo_url']))
                    <img src="{{ $school['logo_url'] }}" alt="Logo">
                @endif
            </div>
            <div class="school-header-center">
                <p class="school-name">{{ $school['school_name'] }}</p>
                @if(!empty($school['school_slogan']))
                    <p class="school-slogan">{{ $school['school_slogan'] }}</p>
                @endif
            </div>
            <div class="logo-slot">
                @if(!empty($school['logo_url']))
                    <img src="{{ $school['logo_url'] }}" alt="Logo">
                @endif
            </div>
        </div>
        <h1>RÉSULTATS DES TESTS MENSUELS</h1>
        <h2>Fusion de classes — {{ $moisListe[$mois] }} {{ $annee }}</h2>
    </div>

    <div class="info-section">
        <h3>Informations générales</h3>
        <p><strong>Classes:</strong> {{ $nomsClasses }}</p>
        <p><strong>Période:</strong> {{ $moisListe[$mois] }} {{ $annee }}</p>
        <p><strong>Effectif:</strong> {{ count($resultats) }} élèves classés</p>
        <p><strong>Date d'impression:</strong> {{ date('d/m/Y à H:i') }}</p>
    </div>

    @if(count($resultats) > 0)
    <div class="info-section">
        <h3>Classement global</h3>
        <table>
            <thead>
                <tr>
                    <th style="width: 8%;">Rang</th>
                    <th style="width: 14%;">Matricule</th>
                    <th style="width: 20%;">Nom</th>
                    <th style="width: 20%;">Prénom</th>
                    <th style="width: 14%;">Classe</th>
                    <th style="width: 12%;">Moyenne</th>
                    <th style="width: 12%;">Appréciation</th>
                </tr>
            </thead>
            <tbody>
                @foreach($resultats as $resultat)
                @php
                    $eleve = $resultat['eleve'];
                    $moyenne = $resultat['moyenne'];
                    $rang = $resultat['rang'];
                    $classeEleve = $eleve->classe;
                    $noteMax = $classeEleve->note_max ?? 20;

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
                    <td class="bold">{{ $eleve->matricule ?: $eleve->numero_etudiant }}</td>
                    <td class="text-left">{{ $eleve->nom }}</td>
                    <td class="text-left">{{ $eleve->prenom }}</td>
                    <td>{{ $classeEleve->nom ?? '—' }}</td>
                    <td class="bold">
                        @if($moyenne == 0.00)
                            00/{{ $noteMax }}
                        @else
                            {{ number_format($moyenne, 2) }}/{{ $noteMax }}
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
        <p>Aucun élève trouvé pour les classes sélectionnées ({{ $moisListe[$mois] }} {{ $annee }})</p>
    </div>
    @endif

    <div class="footer">
        @if(!empty($school['school_address']) || !empty($school['school_phone']))
        <p class="school-contact">
            @if(!empty($school['school_address']))
                {{ $school['school_address'] }}
            @endif
            @if(!empty($school['school_address']) && !empty($school['school_phone']))
                &nbsp;|&nbsp;
            @endif
            @if(!empty($school['school_phone']))
                Tél. {{ $school['school_phone'] }}
            @endif
        </p>
        @endif
        <p>Document généré le {{ date('d/m/Y à H:i') }}</p>
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>
