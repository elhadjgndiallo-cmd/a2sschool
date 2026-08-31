<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carte Enseignant - {{ $cartes_enseignant->enseignant->utilisateur->nom }} {{ $cartes_enseignant->enseignant->utilisateur->prenom }}</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    @include('cartes-enseignants._carte-styles')
    <style>
        @page {
            size: A4;
            margin: 0.5cm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 0;
            background: white;
        }

        .print-card-wrap {
            display: flex;
            justify-content: center;
            padding: 8mm 0;
        }

        @media print {
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            body { margin: 0; background: white; }
            .no-print { display: none !important; }
            .print-card-wrap { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin: 20px;">
        <h2>Carte Enseignant - {{ $cartes_enseignant->enseignant->utilisateur->nom }} {{ $cartes_enseignant->enseignant->utilisateur->prenom }}</h2>
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">
            Imprimer
        </button>
        <a href="{{ route('cartes-enseignants.show', $cartes_enseignant) }}" style="padding: 10px 20px; font-size: 16px; background: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer; margin-left: 10px; text-decoration: none; display: inline-block;">
            Retour
        </a>
    </div>

    <div class="print-card-wrap">
        @include('cartes-enseignants._carte', ['carte' => $cartes_enseignant])
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 1000);
        };
    </script>
</body>
</html>
