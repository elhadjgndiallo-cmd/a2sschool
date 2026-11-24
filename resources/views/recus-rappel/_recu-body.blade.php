@php
    $sansBoutons = $sansBoutons ?? false;
    $utilisateur = $recuRappel->eleve->utilisateur;
    $nomComplet = trim(($utilisateur->prenom ?? '') . ' ' . ($utilisateur->nom ?? ''));
    $obs = trim((string) ($recuRappel->observations ?? ''));
    $moisImpayes = $obs;
    if (preg_match('/Mois impayés\s*:\s*(.+)$/iu', $obs, $matches)) {
        $moisImpayes = trim($matches[1]);
    }
    $moisImpayes = trim(preg_replace('/\s*\([^)]*\)/', '', $moisImpayes));
    $montant = (float) ($recuRappel->montant_a_payer ?: $recuRappel->montant_restant ?: $recuRappel->montant_total_du);
@endphp
<div class="recu-container">
    <header class="header">
        <div class="header-row">
            <div class="header-logo-col">
                @if(!empty($schoolInfo['logo_url']))
                    <img src="{{ $schoolInfo['logo_url'] }}" alt="Logo">
                @endif
            </div>
            <div class="header-center-col">
                <h1 class="school-name">{{ $schoolInfo['school_name'] ?? 'École' }}</h1>
                @if(!empty($schoolInfo['year_name']))
                    <p class="school-year">Année scolaire {{ $schoolInfo['year_name'] }}</p>
                @endif
                <h2 class="doc-title">Rappel de paiement</h2>
                <p class="doc-num">N° {{ $recuRappel->numero_recu_rappel }}</p>
            </div>
            <div class="header-right-col"></div>
        </div>
    </header>

    @unless($sansBoutons)
        <div class="print-controls no-print">
            <button type="button" onclick="imprimerRecu()" class="btn-print">
                <i class="fas fa-print"></i> Imprimer
            </button>
            <button type="button" onclick="retourPage()" class="btn-print btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </button>
        </div>
    @endunless

    <div class="content">
        <div class="fiche">
            <div class="fiche-grid">
                <div class="fiche-item">
                    <span>Élève</span>
                    <strong class="fiche-nom">{{ $nomComplet !== '' ? $nomComplet : '—' }}</strong>
                </div>
                <div class="fiche-item">
                    <span>Matricule</span>
                    <strong>{{ $recuRappel->eleve->numero_etudiant ?? '—' }}</strong>
                </div>
                <div class="fiche-item">
                    <span>Classe</span>
                    <strong>{{ $recuRappel->eleve->classe->nom ?? '—' }}</strong>
                </div>
                <div class="fiche-item">
                    <span>Date d'échéance</span>
                    <strong>{{ $recuRappel->date_echeance?->format('d/m/Y') ?? '—' }}</strong>
                </div>
                <div class="fiche-item fiche-item-full">
                    <span>Mois non payés</span>
                    <strong>{{ $moisImpayes !== '' ? $moisImpayes : '—' }}</strong>
                </div>
            </div>
            <div class="fiche-montant">
                <span>Montant total à payer</span>
                <strong>{{ number_format($montant, 0, ',', ' ') }} GNF</strong>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p><strong>{{ $schoolInfo['school_name'] ?? 'École' }}</strong></p>
        <p>
            {{ $schoolInfo['school_address'] ?? '' }}
            @if(!empty($schoolInfo['school_phone']))
                · Tél : {{ $schoolInfo['school_phone'] }}
            @endif
        </p>
    </footer>
</div>
