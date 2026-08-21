<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture {{ $facture->numero_facture }}</title>
    <style>
        * { box-sizing: border-box; }

        html, body {
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            color: #333;
            font-size: 14px;
            background: #f0f0f0;
        }

        .no-print { margin: 12px; }

        /*
         * Feuille A4 paysage (297 × 210 mm)
         * Grille 2 colonnes = 2 factures A5 portrait (148 × 210 mm)
         */
        .sheet-landscape {
            width: 297mm;
            height: 210mm;
            margin: 0 auto;
            background: #fff;
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-template-rows: 210mm;
        }

        .zone-a5 {
            width: 100%;
            height: 210mm;
            max-height: 210mm;
            overflow: hidden;
            padding: 5mm 5mm 4mm;
            position: relative;
        }

        .zone-a5:first-child {
            border-right: 1px dashed #888;
        }

        .facture-copy {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .copy-label {
            position: absolute;
            top: 3mm;
            right: 5mm;
            font-size: 12px;
            font-weight: bold;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .header {
            border-bottom: 2px solid #28a745;
            padding-bottom: 4px;
            margin-bottom: 5px;
            flex-shrink: 0;
        }

        .header-top {
            display: flex;
            align-items: flex-start;
            gap: 5px;
            margin-bottom: 3px;
        }

        .header-logo {
            height: 42px;
            max-width: 60px;
            object-fit: contain;
            flex-shrink: 0;
        }

        .header h1 {
            margin: 0;
            color: #28a745;
            font-size: 16px;
            line-height: 1.15;
        }

        .school-meta {
            font-size: 11px;
            line-height: 1.2;
            color: #555;
        }

        .facture-ref {
            text-align: center;
            font-size: 13px;
            line-height: 1.3;
        }

        .facture-title {
            font-size: 16px;
            font-weight: bold;
        }

        .meta {
            margin-bottom: 5px;
            font-size: 13px;
            line-height: 1.3;
            flex-shrink: 0;
        }

        .meta div { margin-bottom: 1px; }

        .lignes-table {
            width: 100%;
            border-collapse: collapse;
            margin: 3px 0;
            font-size: 14px;
            flex-shrink: 0;
        }

        .lignes-table th,
        .lignes-table td {
            border: 1px solid #dee2e6;
            padding: 4px 6px;
            text-align: left;
        }

        .lignes-table th { background: #f8f9fa; }

        .text-end { text-align: right; }

        .totaux {
            width: 100%;
            font-size: 14px;
            margin-top: 2px;
            flex-shrink: 0;
        }

        .totaux td {
            border: none;
            padding: 2px 4px;
        }

        .total-row {
            font-size: 15px;
            font-weight: bold;
            color: #28a745;
            border-top: 1px solid #28a745 !important;
        }

        .footer-info {
            margin-top: auto;
            padding-top: 3px;
            font-size: 11px;
            color: #444;
            line-height: 1.25;
            flex-shrink: 0;
        }

        @media print {
            @page {
                size: A4 landscape;
                margin: 0;
            }

            html, body {
                width: 297mm;
                height: 210mm;
                background: #fff;
            }

            .no-print { display: none !important; }

            .sheet-landscape {
                width: 297mm;
                height: 210mm;
                page-break-after: avoid;
                page-break-inside: avoid;
            }

            .zone-a5 {
                page-break-inside: avoid;
            }
        }

        @media screen {
            .sheet-landscape {
                margin: 12px auto;
                box-shadow: 0 2px 8px rgba(0, 0, 0, .12);
            }
        }
    </style>
</head>
<body>
    @unless(request('print'))
        <div class="no-print">
            <button type="button" onclick="window.print()">Imprimer</button>
        </div>
    @endunless

    <div class="sheet-landscape">
        <section class="zone-a5">
            @include('factures.partials.pdf-corps', [
                'facture' => $facture,
                'schoolInfo' => $schoolInfo,
                'copyLabel' => 'Copie établissement',
            ])
        </section>

        <section class="zone-a5">
            @include('factures.partials.pdf-corps', [
                'facture' => $facture,
                'schoolInfo' => $schoolInfo,
                'copyLabel' => 'Copie parent',
            ])
        </section>
    </div>

    @if(request('print'))
        <script>
        (function () {
            var returnUrl = @json(route('factures.show', $facture));

            function retourFacture() {
                window.location.replace(returnUrl);
            }

            window.addEventListener('load', function () {
                window.print();
            });

            window.addEventListener('afterprint', retourFacture);

            if (window.matchMedia) {
                window.matchMedia('print').addEventListener('change', function (event) {
                    if (!event.matches) {
                        setTimeout(retourFacture, 150);
                    }
                });
            }
        })();
        </script>
    @endif
</body>
</html>
