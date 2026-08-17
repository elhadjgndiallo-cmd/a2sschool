@php
    $totalApresRemise = round((float) $facture->sous_total - (float) $facture->montant_remise, 2);
    $resteAPayer = max(0, round($totalApresRemise - (float) $facture->total, 2));
    $facture->lignes->each(fn ($ligne) => $ligne->setRelation('facture', $facture));
@endphp

<div class="facture-copy">
    @if(!empty($copyLabel))
        <div class="copy-label">{{ $copyLabel }}</div>
    @endif

    <div class="header">
        <div class="header-top">
            @if(!empty($schoolInfo['logo_url']))
                <img src="{{ $schoolInfo['logo_url'] }}" alt="Logo {{ $schoolInfo['school_name'] }}" class="header-logo">
            @endif
            <div>
                <h1>{{ $schoolInfo['school_name'] }}</h1>
                <div class="school-meta">{{ $schoolInfo['school_address'] }}</div>
                <div class="school-meta">{{ $schoolInfo['school_phone'] }} — {{ $schoolInfo['school_email'] }}</div>
            </div>
        </div>
        <div class="facture-ref">
            <div class="facture-title">FACTURE</div>
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

    <table class="lignes-table">
        <thead>
            <tr>
                <th>Libellé</th>
                <th class="text-end">Montant</th>
            </tr>
        </thead>
        <tbody>
            @foreach($facture->lignes as $ligne)
                <tr>
                    <td>{{ $ligne->libelleAffiche() }}</td>
                    <td class="text-end">{{ number_format($ligne->montantAffiche(), 0, ',', ' ') }} GNF</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totaux">
        <tr><td>Total brut</td><td class="text-end">{{ number_format($facture->sous_total, 0, ',', ' ') }} GNF</td></tr>
        <tr>
            <td>Remise {{ $facture->remise_type === 'pourcentage' ? '(' . number_format($facture->remise_valeur, 0) . '%)' : '' }}</td>
            <td class="text-end">−{{ number_format($facture->montant_remise, 0, ',', ' ') }} GNF</td>
        </tr>
        <tr><td><strong>Total</strong></td><td class="text-end"><strong>{{ number_format($totalApresRemise, 0, ',', ' ') }} GNF</strong></td></tr>
        <tr class="total-row"><td>Total payé</td><td class="text-end">{{ number_format($facture->total, 0, ',', ' ') }} GNF</td></tr>
        @if($resteAPayer > 0)
            <tr><td>Reste à payer</td><td class="text-end">{{ number_format($resteAPayer, 0, ',', ' ') }} GNF</td></tr>
        @endif
    </table>

    <p class="footer-info">
        <strong>Mode de paiement :</strong> {{ ucfirst(str_replace('_', ' ', $facture->mode_paiement)) }}
        — <strong>Statut :</strong> {{ $facture->statutLibelle() }}
    </p>
</div>
