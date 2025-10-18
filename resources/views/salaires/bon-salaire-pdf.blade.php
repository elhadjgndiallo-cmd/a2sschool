<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bon de Salaire - {{ $salaire->enseignant->utilisateur->nom }} {{ $salaire->enseignant->utilisateur->prenom }}</title>
    
    <!-- Instructions pour l'impression PDF -->
    <script>
        // Fonction pour afficher les instructions
        function showInstructions() {
            alert('Pour sauvegarder ce bon de salaire en PDF :\n\n1. Cliquez sur le bouton "Imprimer / Sauvegarder en PDF" ci-dessus\n2. Ou utilisez Ctrl+P (Windows) ou Cmd+P (Mac)\n3. Dans la boîte de dialogue d\'impression, sélectionnez "Enregistrer au format PDF" comme destination\n4. Cliquez sur "Enregistrer"');
        }
    </script>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 11px;
            line-height: 1.3;
            color: #333;
            margin: 0;
            padding: 10px;
            background: white;
            width: 210mm; /* Largeur A4 */
            height: 297mm; /* Hauteur A4 fixe */
            max-width: 100%;
            overflow: hidden;
        }
        
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 10px;
        }
        
        .school-logo-section {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 5px;
        }
        
        .school-logo {
            max-width: 50px;
            max-height: 50px;
            object-fit: contain;
        }
        
        .logo {
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 3px;
        }
        
        .etablissement-info {
            font-size: 10px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .document-title {
            font-size: 14px;
            font-weight: bold;
            color: #2c3e50;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 5px;
        }
        
        .document-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            background: #f8f9fa;
            padding: 5px;
            border-radius: 2px;
        }
        
        .info-section {
            flex: 1;
            margin-right: 8px;
        }
        
        .info-section:last-child {
            margin-right: 0;
        }
        
        .info-section h4 {
            margin: 0 0 4px 0;
            color: #2c3e50;
            font-size: 10px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 2px;
        }
        
        .info-section p {
            margin: 2px 0;
            font-size: 9px;
        }
        
        .salaire-details {
            margin-bottom: 15px;
        }
        
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        
        .details-table th,
        .details-table td {
            border: 1px solid #ddd;
            padding: 8px 10px;
            text-align: left;
        }
        
        .details-table th {
            background-color: #2c3e50;
            color: white;
            font-weight: bold;
            font-size: 10px;
        }
        
        .details-table td {
            font-size: 10px;
        }
        
        .details-table .amount {
            text-align: right;
            font-weight: bold;
            padding-right: 15px;
            min-width: 100px;
        }
        
        .details-table .total-row {
            background-color: #e8f4f8;
            font-weight: bold;
        }
        
        /* Largeurs spécifiques des colonnes */
        .details-table th:nth-child(1),
        .details-table td:nth-child(1) {
            width: 35%;
            min-width: 120px;
        }
        
        .details-table th:nth-child(2),
        .details-table td:nth-child(2) {
            width: 40%;
            min-width: 150px;
        }
        
        .details-table th:nth-child(3),
        .details-table td:nth-child(3) {
            width: 25%;
            min-width: 100px;
            text-align: right;
            padding-right: 15px;
        }
        
        .details-table .final-total {
            background-color: #2c3e50;
            color: white;
            font-size: 12px;
        }
        
        .calculation-section {
            display: flex;
            gap: 6px;
            margin-bottom: 12px;
        }
        
        .calculation-box {
            flex: 1;
            border: 1px solid #ddd;
            border-radius: 2px;
            padding: 6px;
        }
        
        .calculation-box h4 {
            margin: 0 0 6px 0;
            color: #2c3e50;
            font-size: 8px;
            text-align: center;
            background: #f8f9fa;
            padding: 3px;
            border-radius: 1px;
        }
        
        .calculation-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
            padding: 1px 0;
            border-bottom: 1px dotted #ccc;
        }
        
        .calculation-item:last-child {
            border-bottom: none;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .signature-section {
            margin-top: 15px;
            display: flex;
            justify-content: space-between;
        }
        
        .signature-box {
            width: 120px;
            text-align: center;
        }
        
        .signature-line {
            border-bottom: 1px solid #333;
            height: 20px;
            margin-bottom: 2px;
        }
        
        .signature-label {
            font-size: 7px;
            color: #666;
        }
        
        .footer {
            margin-top: 15px;
            text-align: center;
            font-size: 7px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 6px;
        }
        
        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        
        .footer-school-info {
            flex: 1;
            text-align: left;
        }
        
        .footer-document-info {
            flex: 1;
            text-align: right;
        }
        
        .footer-school-info p,
        .footer-document-info p {
            margin: 1px 0;
            font-size: 7px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 1px;
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-calculé { background-color: #ffc107; color: #000; }
        .status-validé { background-color: #17a2b8; color: #fff; }
        .status-payé { background-color: #28a745; color: #fff; }
        .status-annulé { background-color: #dc3545; color: #fff; }
        
        .currency {
            font-weight: bold;
            color: #2c3e50;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        /* Styles d'impression */
        @media print {
            @page {
                size: A4 portrait;
                margin: 1cm;
            }
            
            body {
                margin: 0;
                padding: 8px;
                font-size: 10px;
                width: 100%;
                height: 100%;
                overflow: hidden;
            }
            
            * {
                page-break-inside: avoid;
            }
            
            .no-print {
                display: none !important;
            }
            
            .header {
                border-bottom: 2px solid #2c3e50;
                margin-bottom: 20px;
                padding-bottom: 15px;
            }
            
            .document-info {
                background: #f8f9fa !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            .details-table th {
                background-color: #2c3e50 !important;
                color: white !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            .calculation-box {
                border: 1px solid #ddd !important;
            }
            
            .calculation-box[style*="background-color: #e8f4f8"] {
                background-color: #e8f4f8 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            .calculation-box h4[style*="background-color: #2c3e50"] {
                background-color: #2c3e50 !important;
                color: white !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
        
        /* Bouton d'impression */
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #2c3e50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            z-index: 1000;
        }
        
        .print-button:hover {
            background: #34495e;
        }
    </style>
</head>
<body>
    <!-- Boutons d'action -->
    <div class="no-print" style="position: fixed; top: 20px; right: 20px; z-index: 1000;">
        <button class="print-button" onclick="window.print()" style="margin-right: 10px;">
            🖨️ Imprimer / Sauvegarder en PDF
        </button>
        <button class="print-button" onclick="showInstructions()" style="background: #17a2b8; margin-right: 10px;">
            ❓ Aide
        </button>
        <a href="{{ route('salaires.show', $salaire) }}" class="print-button" style="background: #6c757d; text-decoration: none; display: inline-block; margin-right: 10px;">
            ← Détails
        </a>
        <a href="{{ route('salaires.index') }}" class="print-button" style="background: #28a745; text-decoration: none; display: inline-block;">
            📋 Liste
        </a>
    </div>
    
    <!-- En-tête -->
    <div class="header">
        <div class="school-logo-section">
            @if($etablissement && $etablissement->logo)
                <img src="{{ asset('storage/' . $etablissement->logo) }}" alt="Logo de l'école" class="school-logo">
            @endif
            @if($etablissement)
                <div class="logo">{{ $etablissement->nom }}</div>
            @else
                <div class="logo">Établissement Scolaire</div>
            @endif
        </div>
        @if($etablissement)
            <div class="etablissement-info">
                @if($etablissement->slogan)
                    "{{ $etablissement->slogan }}"<br>
                @endif
                {{ $etablissement->adresse }}
            </div>
        @endif
        <div class="document-title">Bon de Salaire</div>
    </div>

    <!-- Informations du document -->
    <div class="document-info">
        <div class="info-section">
            <h4>Informations Enseignant</h4>
            <p><strong>Nom:</strong> {{ $salaire->enseignant->utilisateur->nom }} {{ $salaire->enseignant->utilisateur->prenom }}</p>
            <p><strong>Email:</strong> {{ $salaire->enseignant->utilisateur->email }}</p>
            <p><strong>Téléphone:</strong> {{ $salaire->enseignant->utilisateur->telephone ?? 'Non renseigné' }}</p>
        </div>
        
        <div class="info-section">
            <h4>Période de Salaire</h4>
            <p><strong>Du:</strong> {{ $salaire->periode_debut->format('d/m/Y') }}</p>
            <p><strong>Au:</strong> {{ $salaire->periode_fin->format('d/m/Y') }}</p>
            <p><strong>Durée:</strong> {{ $salaire->periode_debut->diffInDays($salaire->periode_fin) + 1 }} jours</p>
        </div>
        
        <div class="info-section">
            <h4>Statut</h4>
            <p><strong>Statut:</strong> <span class="status-badge status-{{ $salaire->statut }}">{{ ucfirst($salaire->statut) }}</span></p>
            <p><strong>Date de calcul:</strong> {{ $salaire->date_calcul ? $salaire->date_calcul->format('d/m/Y') : 'Non calculé' }}</p>
            @if($salaire->date_validation)
                <p><strong>Date de validation:</strong> {{ $salaire->date_validation->format('d/m/Y') }}</p>
            @endif
            @if($salaire->date_paiement)
                <p><strong>Date de paiement:</strong> {{ $salaire->date_paiement->format('d/m/Y') }}</p>
            @endif
        </div>
    </div>

    <!-- Détails du calcul -->
    <div class="salaire-details">
        <h3 style="color: #2c3e50; margin-bottom: 6px; font-size: 10px;">Détail du Calcul du Salaire</h3>
        
        <table class="details-table">
            <thead>
                <tr>
                    <th>Élément</th>
                    <th>Détail</th>
                    <th>Montant (GNF)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Salaire de base</strong></td>
                    <td>Montant fixe mensuel</td>
                    <td class="amount">{{ number_format($salaire->salaire_base, 0, ',', ' ') }}</td>
                </tr>
                <tr>
                    <td><strong>Salaire horaire</strong></td>
                    <td>{{ $salaire->nombre_heures }}h × {{ number_format($salaire->taux_horaire, 0, ',', ' ') }} GNF/h</td>
                    <td class="amount">{{ number_format($salaire->nombre_heures * $salaire->taux_horaire, 0, ',', ' ') }}</td>
                </tr>
                <tr class="total-row">
                    <td colspan="2"><strong>Sous-total salaire</strong></td>
                    <td class="amount">{{ number_format($salaire->salaire_base + ($salaire->nombre_heures * $salaire->taux_horaire), 0, ',', ' ') }}</td>
                </tr>
                <tr>
                    <td><strong>Prime d'ancienneté</strong></td>
                    <td>Prime basée sur l'ancienneté</td>
                    <td class="amount">{{ number_format($salaire->prime_anciennete, 0, ',', ' ') }}</td>
                </tr>
                <tr>
                    <td><strong>Prime de performance</strong></td>
                    <td>Prime basée sur les performances</td>
                    <td class="amount">{{ number_format($salaire->prime_performance, 0, ',', ' ') }}</td>
                </tr>
                <tr>
                    <td><strong>Heures supplémentaires</strong></td>
                    <td>Heures travaillées au-delà du temps normal</td>
                    <td class="amount">{{ number_format($salaire->prime_heures_supplementaires, 0, ',', ' ') }}</td>
                </tr>
                <tr class="total-row">
                    <td colspan="2"><strong>Total des primes</strong></td>
                    <td class="amount">{{ number_format($salaire->prime_anciennete + $salaire->prime_performance + $salaire->prime_heures_supplementaires, 0, ',', ' ') }}</td>
                </tr>
                <tr class="final-total">
                    <td colspan="2"><strong>SALAIRE BRUT</strong></td>
                    <td class="amount"><strong>{{ number_format($salaire->salaire_brut, 0, ',', ' ') }}</strong></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Déductions -->
    <div class="salaire-details">
        <h3 style="color: #2c3e50; margin-bottom: 6px; font-size: 10px;">Déductions</h3>
        
        <table class="details-table">
            <thead>
                <tr>
                    <th>Type de déduction</th>
                    <th>Description</th>
                    <th>Montant (GNF)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Déduction absences</strong></td>
                    <td>Absences non justifiées</td>
                    <td class="amount" style="color: #dc3545;">- {{ number_format($salaire->deduction_absences, 0, ',', ' ') }}</td>
                </tr>
                <tr>
                    <td><strong>Autres déductions</strong></td>
                    <td>Diverses déductions</td>
                    <td class="amount" style="color: #dc3545;">- {{ number_format($salaire->deduction_autres, 0, ',', ' ') }}</td>
                </tr>
                <tr class="total-row">
                    <td colspan="2"><strong>Total des déductions</strong></td>
                    <td class="amount" style="color: #dc3545;"><strong>- {{ number_format($salaire->deduction_absences + $salaire->deduction_autres, 0, ',', ' ') }}</strong></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Résumé final -->
    <div class="calculation-section">
        <div class="calculation-box">
            <h4>Récapitulatif</h4>
            <div class="calculation-item">
                <span>Salaire de base:</span>
                <span>{{ number_format($salaire->salaire_base, 0, ',', ' ') }} GNF</span>
            </div>
            <div class="calculation-item">
                <span>Salaire horaire:</span>
                <span>{{ number_format($salaire->nombre_heures * $salaire->taux_horaire, 0, ',', ' ') }} GNF</span>
            </div>
            <div class="calculation-item">
                <span>Total primes:</span>
                <span>{{ number_format($salaire->prime_anciennete + $salaire->prime_performance + $salaire->prime_heures_supplementaires, 0, ',', ' ') }} GNF</span>
            </div>
            <div class="calculation-item">
                <span><strong>Salaire brut:</strong></span>
                <span><strong>{{ number_format($salaire->salaire_brut, 0, ',', ' ') }} GNF</strong></span>
            </div>
        </div>
        
        <div class="calculation-box">
            <h4>Déductions</h4>
            <div class="calculation-item">
                <span>Absences:</span>
                <span>- {{ number_format($salaire->deduction_absences, 0, ',', ' ') }} GNF</span>
            </div>
            <div class="calculation-item">
                <span>Autres:</span>
                <span>- {{ number_format($salaire->deduction_autres, 0, ',', ' ') }} GNF</span>
            </div>
            <div class="calculation-item">
                <span><strong>Total déductions:</strong></span>
                <span><strong>- {{ number_format($salaire->deduction_absences + $salaire->deduction_autres, 0, ',', ' ') }} GNF</strong></span>
            </div>
        </div>
        
        <div class="calculation-box" style="background-color: #e8f4f8; border: 2px solid #2c3e50;">
            <h4 style="background-color: #2c3e50; color: white;">SALAIRE NET À PAYER</h4>
            <div class="calculation-item" style="font-size: 16px; color: #2c3e50;">
                <span><strong>Montant final:</strong></span>
                <span><strong class="currency">{{ number_format($salaire->salaire_net, 0, ',', ' ') }} GNF</strong></span>
            </div>
        </div>
    </div>

    <!-- Observations -->
    @if($salaire->observations)
        <div style="margin-top: 20px; padding: 10px; background-color: #f8f9fa; border-left: 3px solid #2c3e50;">
            <h4 style="margin: 0 0 4px 0; color: #2c3e50; font-size: 8px;">Observations</h4>
            <p style="margin: 0; font-style: italic; font-size: 8px;">{{ $salaire->observations }}</p>
        </div>
    @endif

    <!-- Signatures -->
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="signature-label">Signature de l'enseignant</div>
        </div>
        
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="signature-label">Signature du comptable</div>
        </div>
        
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="signature-label">Signature du directeur</div>
        </div>
    </div>

    <!-- Pied de page -->
    <div class="footer">
        <div class="footer-content">
            <div class="footer-school-info">
                @if($etablissement)
                    <p><strong>{{ $etablissement->nom }}</strong></p>
                    <p>{{ $etablissement->adresse }}</p>
                    @if($etablissement->telephone)
                        <p>Tél: {{ $etablissement->telephone }}</p>
                    @endif
                    @if($etablissement->email)
                        <p>Email: {{ $etablissement->email }}</p>
                    @endif
                @endif
            </div>
            <div class="footer-document-info">
                <p>Document généré le {{ now()->format('d/m/Y à H:i') }}</p>
                <p>Ce document fait foi de paiement</p>
            </div>
        </div>
    </div>
</body>
</html>
