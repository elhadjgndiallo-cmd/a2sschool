<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bulletins Annuels - {{ $classe->nom }}</title>
    <style>
        @page {
            size: landscape;
            margin: 1cm;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 10px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h3 {
            margin: 5px 0;
        }
        .eleve-info {
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table th, .table td {
            border: 1px solid #333;
            padding: 8px;
            text-align: center;
        }
        .table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .table td.text-left {
            text-align: left;
        }
        .moyenne-generale {
            background-color: #e8f5e8;
            font-weight: bold;
        }
        .appreciation {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #333;
        }
        .page-break {
            page-break-before: always;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    @foreach($bulletins as $bulletin)
        @if(!$loop->first)
        <div class="page-break"></div>
        @endif

        <div class="header">
            <h3>ÉCOLE PRIMAIRE SECONDAIRE</h3>
            <h4>BULLETIN ANNUEL</h4>
            <h5>Année Scolaire {{ $anneeScolaire->annee }}</h5>
        </div>

        <div class="eleve-info">
            <p><strong>Nom & Prénom:</strong> {{ $bulletin['eleve']->utilisateur->nom }} {{ $bulletin['eleve']->utilisateur->prenom }}</p>
            <p><strong>Classe:</strong> {{ $bulletin['classe']->nom }}</p>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th rowspan="2">Matières</th>
                    @foreach($bulletin['periodes'] as $periode)
                    <th colspan="2">{{ ucfirst(str_replace('trimestre', 'Trimestre ', $periode)) }}</th>
                    @endforeach
                    <th rowspan="2">Moyenne Annuelle</th>
                </tr>
                <tr>
                    @foreach($bulletin['periodes'] as $periode)
                    <th>Note</th>
                    <th>Coeff</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($bulletin['notesParPeriode'] as $periode => $notes)
                    @foreach($notes as $index => $note)
                        @if($loop->first)
                        <tr>
                            <td class="text-left">{{ $note->matiere->nom }}</td>
                            @foreach($bulletin['periodes'] as $p)
                                @php
                                $notePeriode = $bulletin['notesParPeriode'][$p]->where('matiere_id', $note->matiere_id)->first();
                                @endphp
                                <td>
                                    {{ $notePeriode ? number_format($notePeriode->note_finale, 2) : '-' }}
                                </td>
                                <td>
                                    {{ $notePeriode ? $notePeriode->coefficient : '-' }}
                                </td>
                            @endforeach
                            <td>
                                @php
                                $totalPoints = 0;
                                $totalCoeffs = 0;
                                @endphp
                                @foreach($bulletin['periodes'] as $p)
                                    @php
                                    $n = $bulletin['notesParPeriode'][$p]->where('matiere_id', $note->matiere_id)->first();
                                    if($n && $n->note_finale !== null) {
                                        $totalPoints += $n->note_finale * ($n->coefficient ?? 1);
                                        $totalCoeffs += ($n->coefficient ?? 1);
                                    }
                                    @endphp
                                @endforeach
                                @php
                                $moyenneAnnuelleMat = $totalCoeffs > 0 ? $totalPoints / $totalCoeffs : 0;
                                @endphp
                                {{ number_format($moyenneAnnuelleMat, 2) }}
                            </td>
                        </tr>
                        @endif
                    @endforeach
                @endforeach
            </tbody>
            <tfoot>
                <tr class="moyenne-generale">
                    <td><strong>Moyenne Période</strong></td>
                    @foreach($bulletin['periodes'] as $periode)
                    <td colspan="2">
                        <strong>{{ number_format($bulletin['moyennesParPeriode'][$periode], 2) }}</strong>
                    </td>
                    @endforeach
                    <td>
                        <strong>{{ number_format($bulletin['moyenneAnnuelle'], 2) }}</strong>
                    </td>
                </tr>
            </tfoot>
        </table>

        <div class="appreciation">
            <p><strong>Moyenne Générale Annuelle:</strong> {{ number_format($bulletin['moyenneAnnuelle'], 2) }}/20</p>
            <p><strong>Rang dans la classe:</strong> {{ $bulletin['rangAnnuel'] }}/{{ count($bulletins) }}</p>
            <p><strong>Appréciation:</strong>
                @if($bulletin['moyenneAnnuelle'] >= 16)
                    Excellent
                @elseif($bulletin['moyenneAnnuelle'] >= 14)
                    Très Bien
                @elseif($bulletin['moyenneAnnuelle'] >= 12)
                    Bien
                @elseif($bulletin['moyenneAnnuelle'] >= 10)
                    Assez Bien
                @else
                    À Améliorer
                @endif
            </p>
        </div>

        <div class="footer">
            <p>Généré le: {{ $dateGeneration }}</p>
            <p>Code de vérification: {{ substr($bulletin['token'], 0, 10) }}...</p>
        </div>
    @endforeach
</body>
</html>
