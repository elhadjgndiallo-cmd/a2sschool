<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bon de salaire {{ $bon->numero_bon }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 13px; margin: 20px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #f0ad4e; padding-bottom: 12px; margin-bottom: 20px; }
        .title { font-size: 20px; font-weight: bold; color: #856404; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin: 16px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background: #fff3cd; text-align: left; }
        .amount { font-size: 22px; font-weight: bold; color: #856404; text-align: center; margin: 20px 0; }
        .signatures { display: flex; justify-content: space-between; margin-top: 50px; }
        .sig { width: 30%; text-align: center; border-top: 1px solid #333; padding-top: 5px; font-size: 11px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom:15px;">
        <button onclick="window.print()">Imprimer</button>
        <a href="{{ route('salaires.bons.index') }}">Retour</a>
    </div>

    <div class="header">
        @if($etablissement)<h2 style="margin:0">{{ $etablissement->nom }}</h2>@endif
        <div class="title">Bon de salaire — Avance</div>
        <div>N° {{ $bon->numero_bon }} — {{ $bon->date_bon->format('d/m/Y') }}</div>
    </div>

    <table>
        <tr><th>Enseignant</th><td>{{ $bon->enseignant->utilisateur->nom }} {{ $bon->enseignant->utilisateur->prenom }}</td></tr>
        <tr><th>Date</th><td>{{ $bon->date_bon->format('d/m/Y') }}</td></tr>
        <tr><th>Mode de paiement</th><td>{{ ucfirst($bon->mode_paiement) }}</td></tr>
        @if($bon->mois_reference)<tr><th>Mois référence</th><td>{{ $bon->mois_reference->format('m/Y') }}</td></tr>@endif
        <tr><th>Statut</th><td>{{ $bon->statut_libelle }}</td></tr>
        @if($bon->observations)<tr><th>Observations</th><td>{{ $bon->observations }}</td></tr>@endif
    </table>

    <div class="amount">Montant avancé : {{ number_format($bon->montant, 0, ',', ' ') }} GNF</div>

    <p><em>Ce montant sera déduit automatiquement du prochain bulletin de salaire de l'enseignant.</em></p>

    <div class="signatures">
        <div class="sig">L'enseignant</div>
        <div class="sig">La comptabilité</div>
        <div class="sig">La direction</div>
    </div>
</body>
</html>
