<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <title>Impression de Cartes Scolaires</title>
    @include('cartes-scolaires._carte-styles')
    <style>
        @page {
            size: A4;
            margin: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 0;
            background: white;
        }

        .page {
            width: 210mm;
            height: 297mm;
            page-break-after: always;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            grid-template-rows: repeat(5, 1fr);
            gap: 2.5mm;
            padding: 3mm;
            justify-items: center;
            align-items: start;
            box-sizing: border-box;
        }

        .page:last-child {
            page-break-after: auto;
        }

        .carte-slot {
            width: 86mm;
            height: 54mm;
        }

        @media print {
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            body { margin: 0; padding: 0; }
            .no-print { display: none !important; }
        }

        @media screen {
            .no-print {
                position: sticky;
                top: 0;
                left: 0;
                right: 0;
                background: #0d6efd;
                color: white;
                padding: 15px;
                text-align: center;
                z-index: 1000;
            }

            .no-print button {
                background: white;
                color: #0d6efd;
                border: none;
                padding: 10px 20px;
                margin: 0 8px;
                border-radius: 5px;
                cursor: pointer;
                font-weight: bold;
            }

            body {
                background: #e9ecef;
            }

            .page {
                background: white;
                margin: 16px auto;
                box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <h3 style="margin: 0 0 10px 0;">Impression de {{ $cartes->count() }} carte(s) — 10 par page A4</h3>
        <button type="button" onclick="window.print()">Imprimer</button>
        <button type="button" onclick="window.close()">Fermer</button>
    </div>

    @foreach($cartesParPage as $pageCartes)
        <div class="page">
            @foreach($pageCartes as $carte)
                <div class="carte-slot">
                    @include('cartes-scolaires._carte', ['carte' => $carte])
                </div>
            @endforeach

            @for($i = $pageCartes->count(); $i < 10; $i++)
                <div class="carte-slot" style="visibility: hidden;"></div>
            @endfor
        </div>
    @endforeach

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 800);
        };
    </script>
</body>
</html>
