<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reçus de rappel — {{ $recus->count() }} document(s)</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 0;
            background: #e9ecef;
            color: #333;
        }

        .toolbar {
            text-align: center;
            padding: 16px;
            background: #fff;
            border-bottom: 1px solid #dee2e6;
            position: sticky;
            top: 0;
            z-index: 20;
        }

        .toolbar p {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #495057;
        }

        .btn-print {
            background: #007bff;
            border: 1px solid #007bff;
            color: #fff;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
        }

        .btn-print.secondary {
            background: #6c757d;
            border-color: #6c757d;
            margin-left: 8px;
        }

        .pages {
            padding: 16px 0 32px;
        }

        .a4-page {
            width: 210mm;
            min-height: 297mm;
            margin: 12px auto;
            background: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
        }

        .recu-slot {
            height: 148.5mm;
            overflow: hidden;
            padding: 6mm 8mm 5mm;
            border-bottom: 1px dashed #999;
            page-break-inside: avoid;
        }

        .recu-slot:last-child {
            border-bottom: none;
        }

        .recu-container {
            height: 100%;
            max-width: none;
            margin: 0;
            background: #fff;
            border: 1px solid #333;
            border-radius: 0;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            font-size: 10px;
        }

        .header {
            background: #fff;
            padding: 4px 8px;
            border-bottom: 1.5px solid #333;
            flex-shrink: 0;
        }

        .header-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        .header-logo-col {
            flex: 0 0 52px;
        }

        .header-logo-col img {
            max-width: 50px;
            max-height: 50px;
            object-fit: contain;
        }

        .header-center-col {
            flex: 1;
            text-align: center;
        }

        .header-center-col .school-name {
            margin: 0;
            font-size: 13px;
            font-weight: bold;
        }

        .header-center-col .school-slogan,
        .header-center-col .school-year {
            margin: 1px 0 0;
            font-size: 8px;
            color: #555;
        }

        .header-center-col .doc-title {
            margin: 3px 0 0;
            font-size: 11px;
            font-weight: bold;
            color: #007bff;
            text-transform: uppercase;
        }

        .header-center-col .doc-num {
            margin: 1px 0 0;
            font-size: 8px;
            color: #666;
        }

        .header-right-col {
            flex: 0 0 26%;
            text-align: right;
            font-size: 8px;
            line-height: 1.3;
        }

        .header-right-col p { margin: 0 0 2px; }

        .content {
            padding: 4px 8px;
            flex: 1;
            overflow: hidden;
        }

        .info-section { margin-bottom: 3px; }

        .info-section h3 {
            color: #007bff;
            border-bottom: 1px solid #007bff;
            padding-bottom: 1px;
            margin: 0 0 3px;
            font-size: 10px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 4px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 1px 0;
            border-bottom: 1px solid #eee;
            font-size: 9px;
            gap: 6px;
        }

        .info-label { font-weight: bold; color: #555; }
        .info-value { color: #333; text-align: right; }

        .paiement-details {
            background: #f8f9fa;
            padding: 4px 6px;
            border-radius: 3px;
            margin: 3px 0;
        }

        .montant-total {
            background: #007bff;
            color: #fff;
            padding: 5px;
            text-align: center;
            border-radius: 3px;
        }

        .montant-total h2 {
            margin: 0 0 4px;
            font-size: 10px;
        }

        .montant-box {
            background: #fff;
            padding: 6px;
            border-radius: 3px;
            border: 2px solid #007bff;
        }

        .montant-box-label {
            font-size: 8px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .montant-value {
            font-size: 13px;
            font-weight: bold;
            color: #007bff;
        }

        .footer {
            background: #f8f9fa;
            padding: 3px 8px;
            text-align: center;
            border-top: 1px solid #dee2e6;
            flex-shrink: 0;
            font-size: 8px;
        }

        .footer p {
            margin: 1px 0;
            color: #6c757d;
        }

        .signature-section {
            margin-top: 8px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .signature-box { text-align: center; }

        .signature-line {
            border-bottom: 1px solid #333;
            height: 16px;
            margin-bottom: 3px;
        }

        .signature-box p {
            font-size: 8px;
            margin: 0;
        }

        .status-badge {
            display: inline-block;
            padding: 1px 6px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 8px;
        }

        .status-actif, .status-paye { background: #d4edda; color: #155724; }
        .status-expire { background: #f8d7da; color: #721c24; }

        .alerte-box {
            border-radius: 2px;
            padding: 3px;
            text-align: center;
        }

        .alerte-warning { background: #fff3cd; border: 1px solid #ffeaa7; }
        .alerte-warning p { color: #856404; margin: 0; font-size: 8px; }
        .alerte-success { background: #d4edda; border: 1px solid #c3e6cb; }
        .alerte-success p { color: #155724; margin: 0; font-size: 8px; }

        .observations-box p {
            background: #f8f9fa;
            padding: 3px 5px;
            border-left: 2px solid #dc3545;
            font-size: 8px;
            margin: 0;
            line-height: 1.25;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 0;
            }

            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            html, body {
                background: #fff !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 210mm;
            }

            .no-print { display: none !important; }

            .pages { padding: 0; }

            .a4-page {
                width: 210mm;
                height: 297mm;
                min-height: 297mm;
                margin: 0;
                box-shadow: none;
                page-break-after: always;
                page-break-inside: avoid;
            }

            .a4-page:last-child {
                page-break-after: auto;
            }

            .recu-slot {
                height: 148.5mm;
                max-height: 148.5mm;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar no-print">
        <p>
            <strong>{{ $recus->count() }}</strong> reçu(s) de rappel généré(s)
            — impression <strong>2 par page A4</strong>
        </p>
        <button type="button" class="btn-print" onclick="window.print()">
            <i class="fas fa-print"></i> Imprimer
        </button>
        <button type="button" class="btn-print secondary" onclick="retourListe()">
            <i class="fas fa-arrow-left"></i> Retour
        </button>
    </div>

    <div class="pages">
        @foreach($recus->chunk(2) as $paire)
            <div class="a4-page">
                @foreach($paire as $recuRappel)
                    <div class="recu-slot">
                        @include('recus-rappel._recu-body', [
                            'recuRappel' => $recuRappel,
                            'schoolInfo' => $schoolInfo,
                            'sansBoutons' => true,
                        ])
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>

    <script>
        function retourListe() {
            window.close();
            window.location.href = '{{ route("comptabilite.impayes-mensuels") }}';
        }

        window.addEventListener('load', function () {
            setTimeout(function () {
                window.print();
            }, 700);
        });
    </script>
</body>
</html>
