<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rappel de paiement - {{ $recuRappel->eleve->utilisateur->nom }} {{ $recuRappel->eleve->utilisateur->prenom }}</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    @include('recus-rappel._recu-styles')
</head>
<body>
    @include('recus-rappel._recu-body')

    <script>
        function imprimerRecu() {
            window.print();
        }

        function retourPage() {
            if (window.history.length > 1) {
                window.history.back();
            } else {
                window.location.href = '{{ route("recus-rappel.index") }}';
            }
        }

        if (window.location.search.includes('print=1')) {
            setTimeout(imprimerRecu, 600);
        }
    </script>
</body>
</html>
