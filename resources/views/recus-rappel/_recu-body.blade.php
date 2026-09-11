@php
    $sansBoutons = $sansBoutons ?? false;
@endphp
<div class="recu-container">
    <div class="header">
        <div class="header-row">
            <div class="header-logo-col">
                @if(!empty($schoolInfo['logo_url']))
                    <img src="{{ $schoolInfo['logo_url'] }}" alt="Logo de l'école">
                @endif
            </div>
            <div class="header-center-col">
                <h1 class="school-name">{{ $schoolInfo['school_name'] ?? 'École' }}</h1>
                @if(!empty($schoolInfo['school_slogan']))
                    <p class="school-slogan">{{ $schoolInfo['school_slogan'] }}</p>
                @endif
                @if(!empty($schoolInfo['year_name']))
                    <p class="school-year">Année scolaire : {{ $schoolInfo['year_name'] }}</p>
                @endif
                <h2 class="doc-title">Reçu de rappel de paiement</h2>
                <p class="doc-num">N° {{ $recuRappel->numero_recu_rappel }}</p>
            </div>
            <div class="header-right-col">
                @if(!empty($schoolInfo['school_address']))
                    <p>{{ $schoolInfo['school_address'] }}</p>
                @endif
                @if(!empty($schoolInfo['school_phone']))
                    <p>Tél : {{ $schoolInfo['school_phone'] }}</p>
                @endif
                @if(!empty($schoolInfo['school_email']))
                    <p>{{ $schoolInfo['school_email'] }}</p>
                @endif
            </div>
        </div>
    </div>

    @unless($sansBoutons)
        <div class="print-controls">
            <button onclick="imprimerRecu()" class="btn-print">
                <i class="fas fa-print"></i> Imprimer
            </button>
            <button onclick="retourPage()" class="btn-print" style="margin-left: 10px; background: #6c757d; border-color: #6c757d;">
                <i class="fas fa-arrow-left"></i> Retour
            </button>
        </div>
    @endunless

    <div class="content">
        <div class="info-section">
            <h3>Informations de l'Élève</h3>
            <div class="info-grid">
                <div>
                    <div class="info-item">
                        <span class="info-label">Nom complet :</span>
                        <span class="info-value">{{ $recuRappel->eleve->utilisateur->nom }} {{ $recuRappel->eleve->utilisateur->prenom }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Matricule :</span>
                        <span class="info-value"><strong>{{ $recuRappel->eleve->numero_etudiant ?? 'N/A' }}</strong></span>
                    </div>
                </div>
                <div>
                    <div class="info-item">
                        <span class="info-label">Classe :</span>
                        <span class="info-value">{{ $recuRappel->eleve->classe->nom ?? 'Non assignée' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Année scolaire :</span>
                        <span class="info-value">{{ $recuRappel->fraisScolarite->annee_scolaire ?? ($schoolInfo['year_name'] ?? (date('Y') . '/' . (date('Y') + 1))) }}</span>
                    </div>
                </div>
                <div>
                    <div class="info-item">
                        <span class="info-label">Date de naissance :</span>
                        <span class="info-value">{{ $recuRappel->eleve->utilisateur->date_naissance ? \Carbon\Carbon::parse($recuRappel->eleve->utilisateur->date_naissance)->format('d/m/Y') : 'Non renseignée' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Statut du rappel :</span>
                        <span class="info-value">
                            <span class="status-badge status-{{ $recuRappel->statut }}">
                                @if($recuRappel->statut == 'actif')
                                    Actif
                                @elseif($recuRappel->statut == 'expire')
                                    Expiré
                                @else
                                    {{ ucfirst($recuRappel->statut) }}
                                @endif
                            </span>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="info-section">
            <h3>Détails du Rappel</h3>
            <div class="paiement-details">
                <div class="info-item">
                    <span class="info-label">Date de rappel :</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($recuRappel->date_rappel)->format('d/m/Y') }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Date d'échéance :</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($recuRappel->date_echeance)->format('d/m/Y') }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Frais concerné :</span>
                    <span class="info-value">{{ $recuRappel->fraisScolarite->libelle }}</span>
                </div>
                @if($recuRappel->generePar)
                <div class="info-item">
                    <span class="info-label">Généré par :</span>
                    <span class="info-value">{{ $recuRappel->generePar->nom }} {{ $recuRappel->generePar->prenom }}</span>
                </div>
                @endif
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 3px; margin-top: 2px;">
            <div>
                <div class="info-section">
                    <h3>Détails Financiers</h3>
                    <div class="paiement-details">
                        <div class="info-item">
                            <span class="info-label">Montant total des frais :</span>
                            <span class="info-value"><strong>{{ number_format($recuRappel->montant_total_du, 0, ',', ' ') }} GNF</strong></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Montant déjà payé :</span>
                            <span class="info-value"><strong>{{ number_format($recuRappel->montant_paye, 0, ',', ' ') }} GNF</strong></span>
                        </div>
                        <div class="info-item" style="border-top: 1px solid #007bff; padding-top: 2px; margin-top: 2px;">
                            <span class="info-label">Montant restant à payer :</span>
                            <span class="info-value" style="color: {{ $recuRappel->montant_restant > 0 ? '#dc3545' : '#28a745' }};">
                                <strong>{{ number_format($recuRappel->montant_restant, 0, ',', ' ') }} GNF</strong>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <div class="montant-total">
                    <h2>MONTANT À PAYER</h2>
                    <div class="montant-box" style="border: 2px solid #007bff; background: #f8f9ff;">
                        <div class="montant-box-label" style="color: #007bff;">Montant à payer</div>
                        @if($recuRappel->montant_a_payer)
                            <div class="montant-value" style="color: #007bff;">
                                {{ number_format($recuRappel->montant_a_payer, 0, ',', ' ') }} GNF
                            </div>
                        @else
                            <div class="montant-placeholder" style="border: 2px dashed #007bff; background: white; padding: 4px;">
                                <div style="font-size: 8px; font-weight: bold; color: #007bff; margin-bottom: 2px;">
                                    CASE VIDE
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if($recuRappel->montant_restant > 0)
        <div class="info-section recu-alerte">
            <div class="alerte-box alerte-warning">
                <p>
                    <strong>Paiement partiel</strong> — Reste : {{ number_format($recuRappel->montant_restant, 0, ',', ' ') }} GNF
                </p>
            </div>
        </div>
        @else
        <div class="info-section recu-alerte">
            <div class="alerte-box alerte-success">
                <p><strong>Paiement complet</strong></p>
            </div>
        </div>
        @endif

        @if($recuRappel->observations && trim($recuRappel->observations) != '')
        <div class="info-section">
            <h3>Observations</h3>
            <div class="observations-box">
                <p>{{ $recuRappel->observations }}</p>
            </div>
        </div>
        @endif

        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line"></div>
                <p><strong>Signature du Comptable</strong></p>
            </div>
            <div class="signature-box">
                <div class="signature-line"></div>
                <p><strong>Signature du Parent/Responsable</strong></p>
            </div>
        </div>
    </div>

    <div class="footer">
        <p><strong>{{ $schoolInfo['school_name'] ?? 'École' }}</strong></p>
        <p>
            {{ $schoolInfo['school_address'] ?? 'Adresse de l\'école' }}
            | Tél: {{ $schoolInfo['school_phone'] ?? 'Téléphone de l\'école' }}
        </p>
        <p>
            Reçu généré le {{ \Carbon\Carbon::now()->format('d/m/Y à H:i') }}
            | Ce reçu de rappel fait foi de notification. Conservez-le précieusement.
        </p>
    </div>
</div>
