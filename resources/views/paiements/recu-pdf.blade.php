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
            padding: 10px;
            background-color: #f8f9fa;
            color: #333;
        }
        
        .recu-container {
            max-width: 595px; /* Largeur A5 */
            height: 842px; /* Hauteur A5 */
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        
        .header {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            flex-shrink: 0;
        }
        
        .header-logo {
            width: 60px;
            height: 60px;
            margin-right: 15px;
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
            font-size: 18px;
            font-weight: bold;
        }
        
        .header p {
            margin: 3px 0 0 0;
            font-size: 11px;
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
            padding: 15px 20px;
            flex: 1;
            overflow-y: auto;
        }
        
        .info-section {
            margin-bottom: 8px;
        }
        
        .info-section h3 {
            color: #007bff;
            border-bottom: 1px solid #007bff;
            padding-bottom: 3px;
            margin-bottom: 6px;
            font-size: 12px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-bottom: 6px;
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 3px 0;
            border-bottom: 1px solid #eee;
            font-size: 11px;
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
            background: #28a745;
            color: white;
            padding: 8px;
            text-align: center;
            border-radius: 3px;
            margin: 6px 0;
        }
        
        .montant-total h2 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
        }
        
        .montant-total p {
            margin: 2px 0 0 0;
            font-size: 10px;
            opacity: 0.9;
        }
        
        
        .footer {
            background: #f8f9fa;
            padding: 5px 15px;
            text-align: center;
            border-top: 1px solid #dee2e6;
            flex-shrink: 0;
        }
        
        .footer p {
            margin: 2px 0;
            color: #6c757d;
            font-size: 8px;
        }
        
        .signature-section {
            margin-top: 10px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .signature-box {
            text-align: center;
        }
        
        .signature-line {
            border-bottom: 1px solid #333;
            height: 20px;
            margin-bottom: 3px;
        }
        
        .signature-box p {
            font-size: 9px;
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
                size: A5;
                margin: 5mm;
            }
            
            body {
                background: white;
                padding: 0;
                margin: 0;
            }
            
            .recu-container {
                box-shadow: none;
                border: none;
                max-width: none;
                height: auto;
                margin: 0;
                border-radius: 0;
            }
            
            .header {
                background: #007bff !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
                padding: 8px;
                display: flex !important;
                align-items: center !important;
            }
            
            .header-logo {
                width: 50px !important;
                height: 50px !important;
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
                padding: 5px;
            }
            
            .montant-total {
                background: #28a745 !important;
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
            
            .footer {
                padding: 3px 5px;
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
                <p>{{ $schoolInfo['school_name'] ?? 'École GSHFD' }}</p>
                <div class="print-controls">
                    <button onclick="imprimerRecu()" class="btn-print">
                        <i class="fas fa-print"></i> Imprimer en PDF
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
            
            <!-- Montant -->
            <div class="montant-total">
                <h2>{{ number_format($paiement->montant_paye, 0, ',', ' ') }} GNF</h2>
                <p>Montant de ce paiement</p>
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
                    <div class="info-item" style="border-top: 2px solid #007bff; padding-top: 15px; margin-top: 10px;">
                        <span class="info-label" style="font-size: 16px;">Montant restant à payer :</span>
                        <span class="info-value" style="font-size: 18px; color: {{ $frais->montant_restant > 0 ? '#dc3545' : '#28a745' }};">
                            <strong>{{ number_format($frais->montant_restant, 0, ',', ' ') }} GNF</strong>
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Message d'information compact -->
            @if($frais->montant_restant > 0)
            <div class="info-section">
                <div style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 3px; padding: 5px; text-align: center;">
                    <p style="color: #856404; margin: 0; font-size: 10px;">
                        <strong>Paiement Partiel</strong> - Reste: {{ number_format($frais->montant_restant, 0, ',', ' ') }} GNF
                    </p>
                </div>
            </div>
            @else
            <div class="info-section">
                <div style="background: #d4edda; border: 1px solid #c3e6cb; border-radius: 3px; padding: 5px; text-align: center;">
                    <p style="color: #155724; margin: 0; font-size: 10px;">
                        <strong>Paiement Complet</strong>
                    </p>
                </div>
            </div>
            @endif
            
            <!-- Informations complémentaires (seulement si importantes) -->
            @if($paiement->observations && strlen($paiement->observations) > 10)
            <div class="info-section">
                <p style="background: #f8f9fa; padding: 4px; border-radius: 2px; border-left: 2px solid #007bff; font-size: 9px; margin: 0;">
                    <strong>Obs:</strong> {{ $paiement->observations }}
                </p>
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
        </div>
        
        <!-- Pied de page -->
        <div class="footer">
            <p><strong>{{ $schoolInfo['school_name'] ?? 'École GSHFD' }}</strong></p>
            <p>Reçu généré le {{ \Carbon\Carbon::now()->format('d/m/Y à H:i') }}</p>
            <p>Ce reçu fait foi de paiement. Conservez-le précieusement.</p>
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
