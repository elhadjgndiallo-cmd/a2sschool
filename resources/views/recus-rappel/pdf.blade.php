<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reçu de Rappel - {{ $recuRappel->eleve->utilisateur->nom }} {{ $recuRappel->eleve->utilisateur->prenom }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 10px;
            background-color: #f8f9fa;
            color: #333;
        }
        
        /* Clamp observations text when printing to keep within one page */
        @media print {
            .observations-box p {
                font-size: 5.5px !important;
                line-height: 1.05 !important;
                max-height: 10mm !important; /* make room for signatures */
                overflow: hidden !important;
                padding: 1px !important;
                margin: 0 !important;
            }
            
            .info-section h3[style*="font-size: 8px"] {
                font-size: 7px !important;
            }
        }
        
        .recu-container {
            max-width: 842px; /* Largeur A5 paysage */
            height: 595px; /* Hauteur A5 paysage */
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            font-size: 11px;
        }
        
        .header {
            background: white;
            color: #333;
            padding: 8px 12px;
            border-bottom: 2px solid #333;
            flex-shrink: 0;
        }

        .header-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .header-logo-col {
            flex: 0 0 70px;
            text-align: left;
        }

        .header-logo-col img {
            max-width: 70px;
            max-height: 70px;
            object-fit: contain;
        }

        .header-center-col {
            flex: 1 1 auto;
            text-align: center;
            padding: 0 8px;
        }

        .header-center-col .school-name {
            margin: 0;
            font-size: 15px;
            font-weight: bold;
            color: #1a1a1a;
        }

        .header-center-col .school-slogan,
        .header-center-col .school-year {
            margin: 2px 0 0 0;
            font-size: 9px;
            color: #555;
        }

        .header-center-col .doc-title {
            margin: 4px 0 0 0;
            font-size: 13px;
            font-weight: bold;
            color: #007bff;
            text-transform: uppercase;
        }

        .header-center-col .doc-num {
            margin: 2px 0 0 0;
            font-size: 9px;
            color: #666;
        }

        .header-right-col {
            flex: 0 0 28%;
            text-align: right;
            font-size: 9px;
            line-height: 1.35;
            color: #333;
        }

        .header-right-col p {
            margin: 0 0 3px 0;
        }

        .print-controls {
            margin: 10px auto 0;
            text-align: center;
            max-width: 842px;
        }

        .btn-print {
            background: #007bff;
            border: 1px solid #007bff;
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 11px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .btn-print:hover {
            background: #0056b3;
            border-color: #0056b3;
            color: white;
        }
        
        .btn-print i {
            margin-right: 4px;
        }
        
        .content {
            padding: 5px 10px;
            flex: 1;
            overflow-y: auto;
        }
        
        .info-section {
            margin-bottom: 3px;
        }
        
        .info-section h3 {
            color: #007bff;
            border-bottom: 1px solid #007bff;
            padding-bottom: 2px;
            margin-bottom: 4px;
            font-size: 12px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 5px;
            margin-bottom: 4px;
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 2px 0;
            border-bottom: 1px solid #eee;
            font-size: 10px;
        }
        
        .info-label {
            font-weight: bold;
            color: #555;
        }
        
        .info-value {
            color: #333;
        }
        
        .paiement-details {
            background: #f8f9fa;
            padding: 6px;
            border-radius: 3px;
            margin: 6px 0;
        }
        
        .montant-total {
            background: #007bff;
            color: white;
            padding: 6px;
            text-align: center;
            border-radius: 3px;
            margin: 0;
            height: fit-content;
        }
        
        .montant-total h2 {
            margin: 0 0 4px 0;
            font-size: 12px;
            font-weight: bold;
        }
        
        .montant-total p {
            margin: 1px 0 0 0;
            font-size: 9px;
            opacity: 0.9;
        }
        
        .montant-box {
            background: white;
            color: #007bff;
            padding: 8px;
            border-radius: 3px;
            border: 2px solid #007bff;
            margin: 4px 0 0 0;
            text-align: center;
        }
        
        .montant-box-label {
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 3px;
        }
        
        .montant-value {
            font-size: 16px;
            font-weight: bold;
            color: #007bff;
        }
        
        .montant-placeholder {
            font-size: 14px;
            font-weight: bold;
            color: #007bff;
            text-align: center;
            padding: 12px;
            border: 2px dashed #007bff;
            border-radius: 3px;
            background: white;
        }
        
        .montant-placeholder small {
            font-size: 10px;
            color: #999;
        }
        
        .footer {
            background: #f8f9fa;
            padding: 3px 10px;
            text-align: center;
            border-top: 1px solid #dee2e6;
            flex-shrink: 0;
            font-size: 9px;
        }
        
        .footer p {
            margin: 2px 0;
            color: #6c757d;
            font-size: 9px;
        }
        
        .signature-section {
            margin-top: 5px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }
        
        .signature-box {
            text-align: center;
        }
        
        .signature-line {
            border-bottom: 1px solid #333;
            height: 15px;
            margin-bottom: 2px;
        }
        
        .signature-box p {
            font-size: 8px;
            margin: 0;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 10px;
            font-weight: bold;
            font-size: 10px;
        }
        
        .status-actif {
            background: #d4edda;
            color: #155724;
        }
        
        .status-expire {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-paye {
            background: #d4edda;
            color: #155724;
        }
        
        @media print {
            @page {
                size: A5 landscape;
                /* Minimal margins to maximize content area */
                margin: 3mm 6mm 14mm 6mm; /* reduce bottom by 1mm to avoid rounding overflows */
            }
            html, body {
                width: 210mm;
                height: auto !important; /* avoid rounding that causes blank page */
                overflow: hidden !important;
            }
            
            * {
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
            
            body {
                background: white !important;
                padding: 0 !important;
                margin: 0 !important;
                font-size: 9px !important; /* slightly larger to fill page */
            }
            
            .recu-container {
                box-shadow: none !important;
                border: none !important;
                max-width: none !important;
                height: calc(148mm - 19mm) !important; /* add 1mm safety */
                max-height: calc(148mm - 19mm) !important;
                margin: 0 !important;
                border-radius: 0 !important;
                width: 100% !important;
                max-width: 842px !important; /* Largeur A5 paysage */
                min-height: calc(148mm - 19mm) !important; /* compenser les marges */
                overflow: hidden !important;
                page-break-after: avoid !important;
            }
            
            .header {
                background: white !important;
                color: #333 !important;
                padding: 2mm 3mm !important;
                min-height: 18mm !important;
                max-height: 22mm !important;
                border-bottom: 1.5px solid #333 !important;
                page-break-inside: avoid !important;
                page-break-after: avoid !important;
            }

            .header-row {
                display: flex !important;
                align-items: center !important;
                justify-content: space-between !important;
            }

            .header-logo-col {
                flex: 0 0 18mm !important;
            }

            .header-logo-col img {
                max-width: 16mm !important;
                max-height: 16mm !important;
            }

            .header-center-col .school-name {
                font-size: 11px !important;
            }

            .header-center-col .school-slogan,
            .header-center-col .school-year {
                font-size: 6px !important;
            }

            .header-center-col .doc-title {
                font-size: 9px !important;
            }

            .header-center-col .doc-num {
                font-size: 6px !important;
            }

            .header-right-col {
                font-size: 6px !important;
            }
            
            .print-controls {
                display: none !important;
            }
            
            .content {
                padding: 2px 4px !important;
                font-size: 9px !important;
                display: flex !important;
                flex-direction: column !important;
                /* Laisser le contenu déborder pour ne pas rogner les signatures */
                max-height: none !important;
                overflow: visible !important;
                overflow-y: visible !important;
                padding-bottom: 12mm !important; /* ensure clearance from fixed footer */
                page-break-inside: avoid !important;
            }
            
            .montant-total {
                background: #007bff !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            
            .status-badge {
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            
            .paiement-details {
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            
            .montant-box {
                border-color: #007bff !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            
            .footer {
                padding: 1px 3px !important;
                font-size: 7px !important;
                /* Fix the footer at the bottom of the printed page */
                position: fixed !important;
                left: 0;
                right: 0;
                bottom: 0;
                z-index: 10;
                height: 11.5mm !important; /* slightly smaller to guarantee fit */
                max-height: 11.5mm !important;
                page-break-inside: avoid !important;
            }
            
            .footer p {
                margin: 0 !important;
                font-size: 7px !important;
                line-height: 1.1 !important;
            }
            
            /* Optimisations spécifiques A5 */
            .info-section {
                margin-bottom: 3px !important;
                page-break-inside: avoid !important;
                page-break-before: avoid !important;
                page-break-after: avoid !important;
            }
            
            .info-section h3 {
                font-size: 9px !important;
                margin-bottom: 3px !important;
                padding-bottom: 1px !important;
            }
            
            .info-item {
                font-size: 8px !important;
                padding: 0 !important;
                line-height: 1.25 !important;
            }
            
            .paiement-details {
                padding: 3px !important;
                margin: 2px 0 !important;
            }
            
            .info-grid {
                gap: 4px !important;
                margin-bottom: 3px !important;
            }
            
            .info-label, .info-value {
                font-size: 8px !important;
            }
            
            .montant-total {
                margin: 0 !important;
                padding: 4px !important;
                height: fit-content !important;
            }
            
            .montant-total h2 {
                font-size: 9px !important;
                margin: 0 0 2px 0 !important;
            }
            
            .montant-box {
                padding: 4px !important;
                margin: 2px 0 0 0 !important;
            }
            
            .montant-box-label {
                font-size: 7px !important;
            }
            
            .montant-value {
                font-size: 11px !important;
            }
            
            .montant-placeholder {
                font-size: 8px !important;
                padding: 4px !important;
            }
            
            .signature-section {
                /* Keep signatures together on the same page as preceding content */
                page-break-inside: avoid !important;
                page-break-before: avoid !important;
                page-break-after: avoid !important;
                break-inside: avoid !important;
                break-before: avoid !important;
                margin-top: 8px !important;
                margin-bottom: 6px !important;
                gap: 8px !important;
                display: grid !important;
                grid-template-columns: 1fr 1fr !important;
                align-items: end !important;
                visibility: visible !important;
            }
            
            .signature-line {
                height: 12px !important;
                border-bottom: 1px solid #333 !important;
            }
            
            .signature-box p {
                font-size: 7px !important;
                margin: 2px 0 0 0 !important;
                text-align: center !important;
                font-weight: bold !important;
            }
            
            .signature-box p {
                font-size: 6px !important;
                margin: 0 !important;
            }
            
            /* Message d'information compact */
            .info-section[style*="margin-bottom: 2px"] {
                margin-bottom: 1px !important;
            }
            
            .info-section[style*="margin-bottom: 2px"] p {
                font-size: 6px !important;
                margin: 0 !important;
                padding: 1px !important;
            }
            
            .info-section[style*="margin-bottom: 2px"] div[style*="background"] {
                padding: 1px !important;
            }
            
            /* Prevent any page breaks */
            * {
                page-break-after: avoid !important;
            }
        }
    </style>
</head>
<body>
    <div class="recu-container">
        <!-- En-tête -->
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

        <div class="print-controls">
            <button onclick="imprimerRecu()" class="btn-print">
                <i class="fas fa-print"></i> Imprimer
            </button>
            <button onclick="retourPage()" class="btn-print" style="margin-left: 10px; background: #6c757d; border-color: #6c757d;">
                <i class="fas fa-arrow-left"></i> Retour
            </button>
        </div>
        
        <!-- Contenu principal -->
        <div class="content">
            <!-- Informations de l'élève -->
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
                            <span class="info-value">{{ $recuRappel->fraisScolarite->annee_scolaire ?? date('Y') . '/' . (date('Y') + 1) }}</span>
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
            
            <!-- Détails du rappel -->
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
            
            <!-- Section principale : Détails à gauche, Montant à droite -->
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 3px; margin-top: 2px;">
                <!-- Colonne gauche : Détails du rappel -->
                <div>
                    <!-- Détails financiers -->
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
                                <span class="info-label" style="font-size: 7px;">Montant restant à payer :</span>
                                <span class="info-value" style="font-size: 7px; color: {{ $recuRappel->montant_restant > 0 ? '#dc3545' : '#28a745' }};">
                                    <strong>{{ number_format($recuRappel->montant_restant, 0, ',', ' ') }} GNF</strong>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    
                </div>
                
                <!-- Colonne droite : Montant à payer -->
                <div>
                    <div class="montant-total">
                        <h2>MONTANT À PAYER</h2>
                        <div class="montant-box" style="border: 2px solid #007bff; background: #f8f9ff;">
                            <div class="montant-box-label" style="color: #007bff; font-size: 6px;">Montant à payer</div>
                            @if($recuRappel->montant_a_payer)
                                <div class="montant-value" style="color: #007bff; font-size: 10px;">
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
            
            <!-- Message d'information compact -->
            @if($recuRappel->montant_restant > 0)
            <div class="info-section" style="margin-bottom: 1px;">
                <div style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 1px; padding: 1px; text-align: center;">
                    <p style="color: #856404; margin: 0; font-size: 6px;">
                        <strong>Paiement Partiel</strong> - Reste: {{ number_format($recuRappel->montant_restant, 0, ',', ' ') }} GNF
                    </p>
                </div>
            </div>
            @else
            <div class="info-section" style="margin-bottom: 1px;">
                <div style="background: #d4edda; border: 1px solid #c3e6cb; border-radius: 1px; padding: 1px; text-align: center;">
                    <p style="color: #155724; margin: 0; font-size: 6px;">
                        <strong>Paiement Complet</strong>
                    </p>
                </div>
            </div>
            @endif

            
            <!-- Informations complémentaires (observations) -->
            @if($recuRappel->observations && trim($recuRappel->observations) != '')
            <div class="info-section" style="margin-bottom: 1px;">
                <h3 style="font-size: 7px;">Observations</h3>
                <div class="observations-box">
                    <p style="background: #f8f9fa; padding: 2px; border-radius: 1px; border-left: 1px solid #dc3545; font-size: 6px; margin: 0; line-height: 1.1;">
                        {{ $recuRappel->observations }}
                    </p>
                </div>
            </div>
            @endif
            
            <!-- Signatures -->
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
        
        <!-- Pied de page -->
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
    
    <script>
        // Fonction pour imprimer le reçu
        function imprimerRecu() {
            // Masquer le bouton d'impression avant d'imprimer
            const printBtn = document.querySelector('.btn-print');
            if (printBtn) {
                printBtn.style.display = 'none';
            }
            
            // Lancer l'impression
            window.print();
            
            // Remettre le bouton après impression
            setTimeout(() => {
                if (printBtn) {
                    printBtn.style.display = 'inline-block';
                }
            }, 1000);
        }
        
        // Fonction pour retourner à la page précédente
        function retourPage() {
            // Essayer de revenir à la page précédente
            if (window.history.length > 1) {
                window.history.back();
            } else {
                // Si pas d'historique, rediriger vers la liste des reçus de rappel
                window.location.href = '{{ route("recus-rappel.index") }}';
            }
        }
        
        // Auto-print après 1 seconde si on est en mode impression
        if (window.location.search.includes('print=1')) {
            setTimeout(imprimerRecu, 1000);
        }
        
        // Raccourci clavier Ctrl+P pour imprimer
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                imprimerRecu();
            }
        });
    </script>
</body>
</html>