<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reçu de Paiement - {{ $frais->eleve->utilisateur->nom }} {{ $frais->eleve->utilisateur->prenom }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f8f9fa;
            color: #333;
        }
        
        .recu-container {
            max-width: 800px; /* Largeur A5 paysage avec marges */
            height: auto; /* Hauteur automatique */
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            font-size: 10px;
            margin-left: 20px;
            margin-right: 20px;
        }
        
        .header {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 3px 8px;
            display: flex;
            align-items: center;
            flex-shrink: 0;
        }
        
        .header-logo {
            width: 40px;
            height: 40px;
            margin-right: 8px;
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
            font-size: 14px;
            font-weight: bold;
        }
        
        .header p {
            margin: 1px 0 0 0;
            font-size: 9px;
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
            padding: 3px 8px;
            flex: 1;
            overflow-y: auto;
        }
        
        .info-section {
            margin-bottom: 2px;
        }
        
        .info-section h3 {
            color: #007bff;
            border-bottom: 1px solid #007bff;
            padding-bottom: 1px;
            margin-bottom: 2px;
            font-size: 10px;
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
            padding: 1px 0;
            border-bottom: 1px solid #eee;
            font-size: 9px;
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
            background: #6c757d;
            color: white;
            padding: 4px;
            text-align: center;
            border-radius: 3px;
            margin: 2px 0;
        }
        
        .montant-total h2 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
        }
        
        .montant-total p {
            margin: 1px 0 0 0;
            font-size: 10px;
            opacity: 0.9;
        }
        
        
        .signature-section {
            margin-top: 2px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4px;
        }
        
        .signature-box {
            text-align: center;
        }
        
        .signature-line {
            border-bottom: 1px solid #333;
            height: 10px;
            margin-bottom: 1px;
        }
        
        .signature-box p {
            font-size: 6px;
            margin: 0;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 10px;
            font-weight: bold;
            font-size: 10px;
        }
        
        .status-paye {
            background: #d4edda;
            color: #155724;
        }
        
        .status-en-attente {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-en-retard {
            background: #f8d7da;
            color: #721c24;
        }
        
        @media print {
            @page {
                size: A5 landscape;
                margin: 5mm 8mm;
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
                max-width: 820px !important; /* Largeur A5 paysage avec marges réduites */
            }
            
            .header {
                background: #007bff !important;
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
                padding: 3px 6px !important;
                display: flex !important;
                align-items: center !important;
                page-break-inside: avoid !important;
            }
            
            .header-logo {
                width: 40px !important;
                height: 40px !important;
                margin-right: 8px !important;
            }
            
            .header h1 {
                font-size: 14px !important;
                margin: 0 !important;
            }
            
            .header p {
                font-size: 9px !important;
                margin: 1px 0 0 0 !important;
            }
            
            .header-content {
                flex: 1 !important;
                text-align: center !important;
            }
            
            .print-controls {
                display: none !important;
            }
            
            .content {
                padding: 2px !important;
                font-size: 9px !important;
                flex: 1 !important;
                overflow: hidden !important;
            }
            
            .montant-total {
                background: #6c757d !important;
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
                margin: 1px 0 !important;
                padding: 3px !important;
            }
            
            .montant-total h2 {
                font-size: 14px !important;
                margin: 0 !important;
            }
            
            .montant-total p {
                font-size: 8px !important;
                margin: 0px 0 0 0 !important;
            }
            
            .status-badge {
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
            
            .paiement-details {
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
            
            
            /* Optimisations spécifiques A5 */
            .info-section {
                margin-bottom: 1px !important;
                page-break-inside: avoid !important;
            }
            
            .info-section h3 {
                font-size: 8px !important;
                margin-bottom: 0px !important;
                padding-bottom: 0px !important;
            }
            
            .info-item {
                font-size: 8px !important;
                padding: 0px 0 !important;
            }
            
            .observations-box p {
                font-size: 5px !important;
                padding: 1px !important;
                line-height: 1.0 !important;
            }
            
            .signature-section {
                margin-top: 1px !important;
                page-break-inside: avoid !important;
            }
            
            .signature-box p {
                font-size: 5px !important;
                margin: 0 !important;
            }
            
            .signature-line {
                height: 6px !important;
                margin-bottom: 0px !important;
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
                <h1>REÇU DE PAIEMENT</h1>
                <p>{{ $schoolInfo['school_name'] ?? 'École' }}</p>
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
                            <span class="info-value">{{ $frais->eleve->utilisateur->nom }} {{ $frais->eleve->utilisateur->prenom }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Matricule :</span>
                            <span class="info-value"><strong>{{ $frais->eleve->numero_etudiant ?? 'N/A' }}</strong></span>
                        </div>
                    </div>
                    <div>
                        <div class="info-item">
                            <span class="info-label">Classe :</span>
                            <span class="info-value">{{ $frais->eleve->classe->nom ?? 'Non assignée' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Année scolaire :</span>
                            <span class="info-value">{{ $frais->annee_scolaire ?? date('Y') . '/' . (date('Y') + 1) }}</span>
                        </div>
                    </div>
                    <div>
                        <div class="info-item">
                            <span class="info-label">Date de naissance :</span>
                            <span class="info-value">{{ $frais->eleve->utilisateur->date_naissance ? \Carbon\Carbon::parse($frais->eleve->utilisateur->date_naissance)->format('d/m/Y') : 'Non renseignée' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Statut du paiement :</span>
                            <span class="info-value">
                                <span class="status-badge status-{{ str_replace('_', '-', $frais->statut) }}">
                                    @if($frais->statut == 'paye')
                                        Entièrement Payé
                                    @elseif($frais->statut == 'en_attente')
                                        Paiement Partiel
                                    @else
                                        {{ ucfirst(str_replace('_', ' ', $frais->statut)) }}
                                    @endif
                                </span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            
            <!-- Section principale : Détails à gauche, Montant à droite -->
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 6px; margin-top: 4px;">
                <!-- Colonne gauche : Détails du paiement -->
                <div>
                    <!-- Détails du paiement -->
                    <div class="info-section">
                        <h3>Détails du Paiement</h3>
                        <div class="paiement-details">
                            <div class="info-item">
                                <span class="info-label">Référence du paiement :</span>
                                <span class="info-value">#{{ $paiement->id }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Date du paiement :</span>
                                <span class="info-value">{{ \Carbon\Carbon::parse($paiement->date_paiement)->format('d/m/Y à H:i') }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Mode de paiement :</span>
                                <span class="info-value">{{ ucfirst(str_replace('_', ' ', $paiement->mode_paiement)) }}</span>
                            </div>
                            @if($paiement->reference_paiement)
                            <div class="info-item">
                                <span class="info-label">Référence transaction :</span>
                                <span class="info-value">{{ $paiement->reference_paiement }}</span>
                            </div>
                            @endif
                            @if($paiement->encaissePar)
                            <div class="info-item">
                                <span class="info-label">Encaissé par :</span>
                                <span class="info-value">{{ $paiement->encaissePar->nom }} {{ $paiement->encaissePar->prenom }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Détails financiers -->
                    <div class="info-section">
                        <h3>Détails Financiers</h3>
                        <div class="paiement-details">
                            <div class="info-item">
                                <span class="info-label">Montant total des frais :</span>
                                <span class="info-value"><strong>{{ number_format($frais->montant, 0, ',', ' ') }} GNF</strong></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Montant déjà payé :</span>
                                <span class="info-value"><strong>{{ number_format($frais->montant_paye, 0, ',', ' ') }} GNF</strong></span>
                            </div>
                            <div class="info-item" style="border-top: 1px solid #007bff; padding-top: 3px; margin-top: 3px;">
                                <span class="info-label" style="font-size: 9px;">Montant restant à payer :</span>
                                <span class="info-value" style="font-size: 10px; color: {{ $frais->montant_restant > 0 ? '#dc3545' : '#28a745' }};">
                                    <strong>{{ number_format($frais->montant_restant, 0, ',', ' ') }} GNF</strong>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Colonne droite : Montant payé -->
                <div style="display: flex; justify-content: center; align-items: center;">
                    <div class="montant-total" style="width: 100%; max-width: 200px;">
                        <h2>{{ number_format($paiement->montant_paye, 0, ',', ' ') }} GNF</h2>
                        <p>Montant de ce paiement</p>
                    </div>
                </div>
            </div>
            
            <!-- Message d'information compact -->
            @if($frais->montant_restant > 0)
            <div class="info-section" style="margin-bottom: 0px;">
                <div style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 1px; padding: 0px; text-align: center;">
                    <p style="color: #856404; margin: 0; font-size: 5px;">
                        <strong>Paiement Partiel</strong> - Reste: {{ number_format($frais->montant_restant, 0, ',', ' ') }} GNF
                    </p>
                </div>
            </div>
            @else
            <div class="info-section" style="margin-bottom: 0px;">
                <div style="background: #d4edda; border: 1px solid #c3e6cb; border-radius: 1px; padding: 0px; text-align: center;">
                    <p style="color: #155724; margin: 0; font-size: 5px;">
                        <strong>Paiement Complet</strong>
                    </p>
                </div>
            </div>
            @endif
            
            <!-- Informations complémentaires (observations) -->
            @if($paiement->observations && trim($paiement->observations) != '')
            <div class="info-section" style="margin-bottom: 0px;">
                <h3 style="font-size: 6px;">Observations</h3>
                <div class="observations-box">
                    <p style="background: #f8f9fa; padding: 1px; border-radius: 1px; border-left: 1px solid #007bff; font-size: 5px; margin: 0; line-height: 1.0;">
                        {{ $paiement->observations }}
                    </p>
                </div>
            </div>
            @endif
            
            <!-- Signatures -->
            <div class="signature-section">
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <p><strong>Signature du Caissier</strong></p>
                </div>
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <p><strong>Signature du Parent/Responsable</strong></p>
                </div>
            </div>
            
            <!-- Pied de page intégré -->
            <div class="info-section" style="margin-top: 2px; border-top: 1px solid #dee2e6; padding-top: 2px;">
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 5px; margin-bottom: 1px;">
                    <p style="margin: 0; font-size: 7px; text-align: left;"><strong>{{ $schoolInfo['school_name'] ?? 'École' }}</strong></p>
                    <p style="margin: 0; font-size: 7px; text-align: center;">{{ $schoolInfo['school_address'] ?? 'Adresse' }}</p>
                    <p style="margin: 0; font-size: 7px; text-align: right;">Tél: {{ $schoolInfo['school_phone'] ?? 'Téléphone' }}</p>
                </div>
                <p style="margin: 0; font-size: 6px; text-align: center; color: #6c757d;">
                    Reçu généré le {{ \Carbon\Carbon::now()->format('d/m/Y à H:i') }} | Ce reçu fait foi de paiement
                </p>
            </div>
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
                // Si pas d'historique, rediriger vers la liste des paiements
                window.location.href = '{{ route("paiements.index") }}';
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
