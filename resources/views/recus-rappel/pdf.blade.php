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
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 5px 10px;
            display: flex;
            align-items: center;
            flex-shrink: 0;
        }
        
        .header-logo {
            width: 50px;
            height: 50px;
            margin-right: 12px;
            flex-shrink: 0;
        }
        
        .header-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 5px;
        }
        
        .header-content {
            flex: 1;
            text-align: center;
        }
        
        .header h1 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
        }
        
        .header p {
            margin: 2px 0 0 0;
            font-size: 10px;
            opacity: 0.9;
        }
        
        .print-controls {
            margin-top: 10px;
        }
        
        .btn-print {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid white;
            color: white;
            padding: 6px 12px;
            border-radius: 15px;
            cursor: pointer;
            font-size: 11px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .btn-print:hover {
            background: white;
            color: #007bff;
            transform: translateY(-1px);
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
                margin: 0;
            }
            
            * {
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
            
            body {
                background: white !important;
                padding: 0 !important;
                margin: 0 !important;
                font-size: 11px !important;
            }
            
            .recu-container {
                box-shadow: none !important;
                border: none !important;
                max-width: none !important;
                height: auto !important;
                margin: 0 !important;
                border-radius: 0 !important;
                width: 100% !important;
                max-width: 842px !important; /* Largeur A5 paysage */
                height: 595px !important; /* Hauteur A5 paysage */
            }
            
            .header {
                background: #007bff !important;
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
                padding: 8px !important;
                display: flex !important;
                align-items: center !important;
                page-break-inside: avoid !important;
            }
            
            .header-logo {
                width: 45px !important;
                height: 45px !important;
                margin-right: 10px !important;
            }
            
            .header-content {
                flex: 1 !important;
                text-align: center !important;
            }
            
            .print-controls {
                display: none !important;
            }
            
            .content {
                padding: 6px !important;
                font-size: 11px !important;
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
                padding: 3px 5px !important;
                font-size: 9px !important;
            }
            
            /* Optimisations spécifiques A5 */
            .info-section {
                margin-bottom: 6px !important;
                page-break-inside: avoid !important;
            }
            
            .info-section h3 {
                font-size: 11px !important;
                margin-bottom: 4px !important;
            }
            
            .info-item {
                font-size: 10px !important;
                padding: 2px 0 !important;
            }
            
            .montant-total {
                margin: 0 !important;
                padding: 6px !important;
                height: fit-content !important;
            }
            
            .montant-total h2 {
                font-size: 11px !important;
            }
            
            .montant-box {
                padding: 8px !important;
                margin: 4px 0 0 0 !important;
            }
            
            .montant-value {
                font-size: 14px !important;
            }
            
            .montant-placeholder {
                font-size: 12px !important;
                padding: 10px !important;
            }
            
            .signature-section {
                margin-top: 8px !important;
                page-break-inside: avoid !important;
            }
            
            .signature-box p {
                font-size: 8px !important;
            }
        }
    </style>
</head>
<body>
    <div class="recu-container">
        <!-- En-tête -->
        <div class="header">
            <div class="header-logo">
                @if($schoolInfo['logo_url'])
                    <img src="{{ $schoolInfo['logo_url'] }}" alt="Logo de l'école">
                @else
                    <div style="width: 100%; height: 100%; background: rgba(255,255,255,0.2); border-radius: 5px; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                        🏫
                    </div>
                @endif
            </div>
            <div class="header-content">
                <h1>REÇU DE RAPPEL</h1>
                <p>{{ $schoolInfo['school_name'] ?? 'École' }}</p>
                <p>N° {{ $recuRappel->numero_recu_rappel }}</p>
                <div class="print-controls">
                    <button onclick="imprimerRecu()" class="btn-print">
                        <i class="fas fa-print"></i> Imprimer en PDF
                    </button>
                    <button onclick="retourPage()" class="btn-print" style="margin-left: 10px;">
                        <i class="fas fa-arrow-left"></i> Retour
                    </button>
                </div>
            </div>
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
                        <span class="info-value">{{ \Carbon\Carbon::parse($recuRappel->date_rappel)->format('d/m/Y à H:i') }}</span>
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
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 6px; margin-top: 4px;">
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
                            <div class="info-item" style="border-top: 1px solid #007bff; padding-top: 3px; margin-top: 3px;">
                                <span class="info-label" style="font-size: 9px;">Montant restant à payer :</span>
                                <span class="info-value" style="font-size: 10px; color: {{ $recuRappel->montant_restant > 0 ? '#dc3545' : '#28a745' }};">
                                    <strong>{{ number_format($recuRappel->montant_restant, 0, ',', ' ') }} GNF</strong>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Informations du reçu -->
                    <div class="info-section">
                        <h3>Informations du Reçu</h3>
                        <div class="paiement-details">
                            <div class="info-item">
                                <span class="info-label">Date de rappel :</span>
                                <span class="info-value">{{ \Carbon\Carbon::parse($recuRappel->date_rappel)->format('d/m/Y à H:i') }}</span>
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
                </div>
                
                <!-- Colonne droite : Montant à payer -->
                <div>
                    <div class="montant-total">
                        <h2>MONTANT À PAYER</h2>
                        <div class="montant-box" style="border: 2px solid #007bff; background: #f8f9ff;">
                            <div class="montant-box-label" style="color: #007bff; font-size: 8px;">Montant à payer</div>
                            @if($recuRappel->montant_a_payer)
                                <div class="montant-value" style="color: #007bff; font-size: 12px;">
                                    {{ number_format($recuRappel->montant_a_payer, 0, ',', ' ') }} GNF
                                </div>
                            @else
                                <div class="montant-placeholder" style="border: 2px dashed #007bff; background: white; padding: 8px;">
                                    <div style="font-size: 10px; font-weight: bold; color: #007bff; margin-bottom: 3px;">
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
            <div class="info-section" style="margin-bottom: 2px;">
                <div style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 2px; padding: 2px; text-align: center;">
                    <p style="color: #856404; margin: 0; font-size: 7px;">
                        <strong>Paiement Partiel</strong> - Reste: {{ number_format($recuRappel->montant_restant, 0, ',', ' ') }} GNF
                    </p>
                </div>
            </div>
            @else
            <div class="info-section" style="margin-bottom: 2px;">
                <div style="background: #d4edda; border: 1px solid #c3e6cb; border-radius: 2px; padding: 2px; text-align: center;">
                    <p style="color: #155724; margin: 0; font-size: 7px;">
                        <strong>Paiement Complet</strong>
                    </p>
                </div>
            </div>
            @endif

            
            <!-- Informations complémentaires (observations) -->
            @if($recuRappel->observations && trim($recuRappel->observations) != '')
            <div class="info-section" style="margin-bottom: 2px;">
                <h3 style="font-size: 8px;">Observations</h3>
                <div class="observations-box">
                    <p style="background: #f8f9fa; padding: 3px; border-radius: 2px; border-left: 2px solid #dc3545; font-size: 7px; margin: 0; line-height: 1.2;">
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
            <p>{{ $schoolInfo['school_address'] ?? 'Adresse de l\'école' }}</p>
            <p>Tél: {{ $schoolInfo['school_phone'] ?? 'Téléphone de l\'école' }}</p>
            <p>Reçu généré le {{ \Carbon\Carbon::now()->format('d/m/Y à H:i') }}</p>
            <p>Ce reçu de rappel fait foi de notification. Conservez-le précieusement.</p>
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