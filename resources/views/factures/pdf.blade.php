<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture {{ $facture->numero_facture }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; color: #333; font-size: 13px; }
        .header { display: flex; justify-content: space-between; border-bottom: 3px solid #28a745; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { margin: 0; color: #28a745; font-size: 22px; }
        .meta { margin-bottom: 20px; }
        .meta div { margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #dee2e6; padding: 8px; text-align: left; }
        th { background: #f8f9fa; }
        .text-end { text-align: right; }
        .totaux { width: 320px; margin-left: auto; }
        .totaux td { border: none; padding: 4px 8px; }
        .total-row { font-size: 16px; font-weight: bold; color: #28a745; border-top: 2px solid #28a745 !important; }
        @media print { body { padding: 10px; } .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom:15px;">
        <button onclick="window.print()">Imprimer</button>
    </div>

    <div class="header">
        <div>
            <h1>{{ $schoolInfo['school_name'] }}</h1>
            <div>{{ $schoolInfo['school_address'] }}</div>
            <div>{{ $schoolInfo['school_phone'] }} — {{ $schoolInfo['school_email'] }}</div>
        </div>
        <div style="text-align:right;">
            <div style="font-size:18px;font-weight:bold;">FACTURE</div>
            <div><strong>{{ $facture->numero_facture }}</strong></div>
            <div>Date : {{ $facture->date_facture->format('d/m/Y') }}</div>
        </div>
    </div>

    <div class="meta">
        <div><strong>Élève :</strong> {{ $facture->eleve->utilisateur->prenom }} {{ $facture->eleve->utilisateur->nom }}</div>
        <div><strong>Matricule :</strong> {{ $facture->eleve->numero_etudiant }}</div>
        <div><strong>Classe :</strong> {{ $facture->eleve->classe->nom ?? '—' }}</div>
        <div><strong>Année scolaire :</strong> {{ $facture->anneeScolaire->nom ?? '—' }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Libellé</th>
                <th class="text-end">Montant brut</th>
                <th class="text-end">Remise</th>
                <th class="text-end">Net</th>
            </tr>
        </thead>
        <tbody>
            @foreach($facture->lignes as $ligne)
                <tr>
                    <td>{{ $ligne->libelle }}</td>
                    <td class="text-end">{{ number_format($ligne->montant_brut, 0, ',', ' ') }} GNF</td>
                    <td class="text-end">{{ number_format($ligne->montant_remise, 0, ',', ' ') }} GNF</td>
                    <td class="text-end">{{ number_format($ligne->montant_net, 0, ',', ' ') }} GNF</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totaux">
        <tr><td>Sous-total</td><td class="text-end">{{ number_format($facture->sous_total, 0, ',', ' ') }} GNF</td></tr>
        <tr>
            <td>Remise {{ $facture->remise_type === 'pourcentage' ? '(' . number_format($facture->remise_valeur, 0) . '%)' : '' }}</td>
            <td class="text-end">-{{ number_format($facture->montant_remise, 0, ',', ' ') }} GNF</td>
        </tr>
        <tr class="total-row"><td>Total payé</td><td class="text-end">{{ number_format($facture->total, 0, ',', ' ') }} GNF</td></tr>
    </table>

    <p style="margin-top:30px;">
        <strong>Mode de paiement :</strong> {{ ucfirst(str_replace('_', ' ', $facture->mode_paiement)) }}<br>
        <strong>Statut :</strong> Payée<br>
        @if($facture->observations)
            <strong>Observations :</strong> {{ $facture->observations }}
        @endif
    </p>

    <p style="margin-top:40px;font-size:11px;color:#666;">
        Document généré le {{ now()->format('d/m/Y H:i') }} — {{ $facture->generePar->prenom ?? '' }} {{ $facture->generePar->nom ?? '' }}
    </p>
</body>
</html>
