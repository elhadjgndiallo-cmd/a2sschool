<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Satisfécits - {{ $classe->nom }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Georgia', 'Times New Roman', serif;
            background: white;
        }

        .page-break {
            page-break-after: always;
        }

        .satisfecit {
            width: 297mm;
            height: 210mm;
            padding: 15mm;
            position: relative;
            background: white;
            border: 8px solid #2c3e50;
            box-shadow: inset 0 0 0 4px #f39c12;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 3px solid #2c3e50;
        }

        .logo-left, .logo-right {
            width: 70px;
            height: 70px;
            object-fit: contain;
        }

        .school-info {
            text-align: center;
            flex-grow: 1;
        }

        .school-name {
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .school-slogan {
            font-size: 12px;
            font-style: italic;
            color: #7f8c8d;
        }

        .satisfecit-title {
            text-align: center;
            margin: 12px 0;
        }

        .satisfecit-title h1 {
            font-size: 38px;
            color: #e74c3c;
            text-transform: uppercase;
            letter-spacing: 3px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 5px;
        }

        .satisfecit-subtitle {
            font-size: 15px;
            color: #34495e;
            font-style: italic;
        }

        .content {
            text-align: center;
            margin: 12px 0;
        }

        .felicitation-text {
            font-size: 16px;
            color: #2c3e50;
            margin-bottom: 15px;
            line-height: 1.3;
        }

        .student-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            border-radius: 12px;
            margin: 15px auto;
            max-width: 550px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .student-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .student-details {
            font-size: 14px;
            margin: 5px 0;
        }

        .medal {
            font-size: 40px;
            margin-bottom: 5px;
        }

        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 18px;
            padding-top: 10px;
        }

        .signature-box {
            text-align: center;
            width: 30%;
        }

        .signature-line {
            border-top: 2px solid #2c3e50;
            margin-top: 70px;
            padding-top: 8px;
        }

        .signature-title {
            font-size: 13px;
            font-weight: bold;
            color: #2c3e50;
        }

        .footer {
            position: absolute;
            bottom: 10mm;
            left: 15mm;
            right: 15mm;
            text-align: center;
            font-size: 11px;
            color: #7f8c8d;
            border-top: 2px solid #ecf0f1;
            padding-top: 8px;
        }

        @media print {
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    @foreach($top5 as $index => $resultat)
    <div class="satisfecit {{ $index < count($top5) - 1 ? 'page-break' : '' }}">
        <!-- En-tête avec logos -->
        <div class="header">
            @if($etablissement && $etablissement->logo)
            <img src="{{ asset('storage/' . $etablissement->logo) }}" alt="Logo" class="logo-left">
            @else
            <div class="logo-left"></div>
            @endif
            
            <div class="school-info">
                <div class="school-name">{{ $etablissement->nom ?? 'École' }}</div>
                <div class="school-slogan">{{ $etablissement->slogan ?? 'Excellence et réussite' }}</div>
            </div>
            
            @if($etablissement && $etablissement->logo)
            <img src="{{ asset('storage/' . $etablissement->logo) }}" alt="Logo" class="logo-right">
            @else
            <div class="logo-right"></div>
            @endif
        </div>

        <!-- Titre SATISFÉCIT -->
        <div class="satisfecit-title">
            <h1>SATISFÉCIT</h1>
            <div class="satisfecit-subtitle">Année Scolaire {{ $anneeScolaireActive->nom }}</div>
        </div>

        <!-- Contenu principal -->
        <div class="content">
            <div class="felicitation-text">
                Le Conseil de l'Établissement décerne ce <strong>Satisfécit</strong> à
            </div>

            <div class="student-info">
                <div class="medal">
                    @if($resultat['rang'] == 1)
                        🏆
                    @elseif($resultat['rang'] == 2)
                        🥈
                    @elseif($resultat['rang'] == 3)
                        🥉
                    @else
                        ⭐
                    @endif
                </div>
                <div class="student-name">
                    {{ $resultat['eleve']->utilisateur->prenom }} {{ $resultat['eleve']->utilisateur->nom }}
                </div>
                <div class="student-details">
                    <strong>Classe :</strong> {{ $classe->nom }}
                </div>
                <div class="student-details">
                    <strong>Rang :</strong> {{ $resultat['rang'] }}{{ $resultat['rang'] == 1 ? 'er' : 'ème' }}
                </div>
                <div class="student-details">
                    <strong>Moyenne Annuelle :</strong> {{ number_format($resultat['moyenneAnnuelle'], 2) }}/20
                </div>
            </div>

            <div class="felicitation-text" style="margin-top: 12px;">
                Pour son excellent travail et ses résultats remarquables<br>
                tout au long de l'année scolaire {{ $anneeScolaireActive->nom }}
            </div>
        </div>

        <!-- Signatures -->
        <div class="signatures">
            <div class="signature-box">
                <div class="signature-line">
                    <div class="signature-title">Le Directeur des Études</div>
                </div>
            </div>
            <div class="signature-box">
                <div class="signature-line">
                    <div class="signature-title">Le Directeur Général</div>
                </div>
            </div>
            <div class="signature-box">
                <div class="signature-line">
                    <div class="signature-title">Le Comptable</div>
                </div>
            </div>
        </div>

        <!-- Pied de page -->
        <div class="footer">
            Fait à {{ $etablissement->adresse ?? 'l\'établissement' }}, le {{ now()->format('d/m/Y') }}
        </div>
    </div>
    @endforeach

    <script>
        // Impression automatique au chargement
        window.onload = function() { 
            window.print(); 
        }
    </script>
</body>
</html>
