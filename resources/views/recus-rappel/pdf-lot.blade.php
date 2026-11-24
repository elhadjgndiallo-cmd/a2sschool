<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reçus de rappel — {{ $recus->count() }} document(s)</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    @include('recus-rappel._recu-styles')
    <style>
        .toolbar {
            text-align: center;
            padding: 14px;
            background: #fff;
            border-bottom: 1px solid #dee2e6;
            position: sticky;
            top: 0;
            z-index: 20;
        }

        .toolbar p {
            margin: 0 0 10px;
            font-size: 14px;
            color: #495057;
        }

        .pages { padding: 16px 0 32px; }

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
            padding: 5mm;
            border-bottom: 1px dashed #999;
            page-break-inside: avoid;
        }

        .recu-slot:last-child { border-bottom: none; }

        .recu-slot .recu-container {
            height: 100%;
            max-width: none;
            min-height: 0;
            margin: 0;
        }

        .recu-slot .fiche-item strong { font-size: 13px; }
        .recu-slot .fiche-montant strong { font-size: 16px; }

        @media print {
            @page {
                size: A4 portrait;
                margin: 0;
            }

            .pages { padding: 0; }

            .a4-page {
                width: 210mm;
                height: 297mm;
                min-height: 297mm;
                margin: 0;
                box-shadow: none;
                page-break-after: always;
            }

            .a4-page:last-child { page-break-after: auto; }

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
            <strong>{{ $recus->count() }}</strong> reçu(s) de rappel
            — 2 par page A4
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
