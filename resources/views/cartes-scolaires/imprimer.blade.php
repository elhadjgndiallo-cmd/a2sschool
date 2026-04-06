<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carte Scolaire - {{ $cartes_scolaire->eleve->utilisateur->nom }} {{ $cartes_scolaire->eleve->utilisateur->prenom }}</title>
    @php
        $cardBorder = $couleurs['document']['document_card_border'] ?? '#d4af37';
        $cardAccent = $couleurs['document']['document_card_accent'] ?? '#1e3c72';
        $cardTitle = $couleurs['document']['document_card_title'] ?? '#d4af37';
        $cardBodyText = $couleurs['document']['document_card_text'] ?? '#333';
        $cardMutedText = $couleurs['document']['document_card_muted'] ?? '#666';
        $cardBg = $couleurs['document']['document_card_bg'] ?? '#ffffff';
        $statusActive = $couleurs['resultat']['resultat_success_bg'] ?? '#28a745';
        $statusDanger = $couleurs['resultat']['resultat_danger_bg'] ?? '#dc3545';
        $statusWarning = $couleurs['resultat']['resultat_warning_bg'] ?? '#ffc107';
        $statusSecondary = $couleurs['resultat']['resultat_secondary_bg'] ?? '#6c757d';
    @endphp
    <style>
        :root {
            --card-border: {{ $cardBorder }};
            --card-accent: {{ $cardAccent }};
            --card-title: {{ $cardTitle }};
            --card-text: {{ $cardBodyText }};
            --card-muted: {{ $cardMutedText }};
            --card-bg: {{ $cardBg }};
            --status-active: {{ $statusActive }};
            --status-expiree: {{ $statusDanger }};
            --status-suspendue: {{ $statusWarning }};
            --status-annulee: {{ $statusSecondary }};
        }

        @page {
            size: A4;
            margin: 0.5cm;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background: white;
        }
        
        .carte-container {
            width: 86mm;
            height: 54mm;
            border: 2px solid var(--card-border);
            margin: 0 auto;
            position: relative;
            background: var(--card-bg);
            page-break-inside: avoid;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .carte-header {
            background: var(--card-bg);
            color: var(--card-text);
            padding: 2mm;
            text-align: center;
            position: relative;
            height: 12mm;
            border-bottom: 1px solid var(--card-border);
        }
        
        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 100%;
        }
        
        .logo-left {
            width: 8mm;
            height: 8mm;
            background: var(--card-border);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4mm;
            color: var(--card-accent);
            font-weight: bold;
            overflow: hidden;
        }
        
        .logo-left img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }
        
        .header-text {
            flex: 1;
            text-align: center;
        }
        
        .country-name {
            font-size: 2.5mm;
            font-weight: bold;
            margin: 0;
            color: var(--card-text);
        }
        
        .motto {
            font-size: 2mm;
            margin: 0;
            color: var(--card-muted);
        }
        
        .school-name {
            font-size: 3.5mm;
            font-weight: bold;
            margin: 0;
            color: var(--card-text);
        }
        
        .card-title {
            font-size: 3mm;
            font-weight: bold;
            margin: 0;
            color: var(--card-title);
        }
        
        .logo-right {
            width: 8mm;
            height: 8mm;
            background: var(--card-border);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3mm;
            color: var(--card-accent);
            font-weight: bold;
            overflow: hidden;
        }
        
        .logo-right img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }
        
        .carte-body {
            padding: 2mm;
            display: flex;
            height: calc(100% - 16mm);
            position: relative;
        }
        
        .carte-left {
            width: 25mm;
            padding-right: 2mm;
            border-right: 1px solid var(--card-border);
        }
        
        .carte-right {
            width: 55mm;
            padding-left: 2mm;
            position: relative;
        }
        
        .eleve-photo {
            width: 20mm;
            height: 25mm;
            border: 1px solid var(--card-border);
            margin: 0 auto 2mm;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5mm;
            color: var(--card-muted);
            overflow: hidden;
        }
        
        .eleve-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .matricule {
            font-size: 2.5mm;
            font-weight: bold;
            color: var(--card-title);
            text-align: center;
            margin-bottom: 2mm;
        }
        
        .eleve-info {
            font-size: 2.2mm;
            line-height: 1.3;
        }
        
        .eleve-details {
            font-size: 2mm;
            color: var(--card-text);
        }
        
        .eleve-details div {
            margin-bottom: 0.5mm;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5mm;
        }
        
        .info-label {
            font-weight: bold;
        }
        
        .qr-code {
            position: absolute;
            top: 0;
            right: 0;
            width: 12mm;
            height: 12mm;
            border: 1px solid var(--card-border);
            overflow: hidden;
        }
        
        .qr-code img {
            width: 100%;
            height: 100%;
        }
        
        .carte-footer {
            position: absolute;
            bottom: 1mm;
            left: 2mm;
            right: 2mm;
            font-size: 1.8mm;
            color: var(--card-muted);
            text-align: center;
            border-top: 1px solid var(--card-border);
            padding-top: 1mm;
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.5mm 1mm;
            border-radius: 1mm;
            font-size: 1.8mm;
            font-weight: bold;
            color: white;
        }
        
        .status-active { background: var(--status-active); }
        .status-expiree { background: var(--status-expiree); }
        .status-suspendue { background: var(--status-suspendue); color: #000; }
        .status-annulee { background: var(--status-annulee); }
        
        .decorative-border {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border: 1mm solid transparent;
            border-image: linear-gradient(45deg, var(--card-border), var(--card-accent), var(--card-border)) 1;
            pointer-events: none;
        }
        
        @media print {
            * {
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            body { margin: 0; }
            .no-print { display: none; }
            .carte-container {
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin: 20px;">
        <h2>Carte Scolaire - {{ $cartes_scolaire->eleve->utilisateur->nom }} {{ $cartes_scolaire->eleve->utilisateur->prenom }}</h2>
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">
            <i class="fas fa-print"></i> Imprimer
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; font-size: 16px; background: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer; margin-left: 10px;">
            Fermer
        </button>
    </div>

    <div class="carte-container">
        <div class="decorative-border"></div>
        
        <div class="carte-header">
            <div class="header-content">
                <div class="logo-left">
                    @php
                        $school = \App\Helpers\SchoolHelper::getSchoolInfo();
                        $logoUrl = $school && $school->logo ? asset('storage/' . $school->logo) : null;
                    @endphp
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="Logo de l'école">
                    @else
                        🇬🇳
                    @endif
                </div>
                <div class="header-text">
                    <div class="country-name">RÉPUBLIQUE DE GUINÉE</div>
                    <div class="motto">Travail - Justice - Solidarité</div>
                    <div class="school-name">{{ $school->nom ?? 'ÉCOLE GANALIS' }}</div>
                    <div class="card-title">CARTE D'IDENTITÉ SCOLAIRE</div>
                </div>
                <div class="logo-right">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="Logo de l'école">
                    @else
                        📚
                    @endif
                </div>
            </div>
        </div>
        
        <div class="carte-body">
            <div class="carte-left">
                <div class="eleve-photo">
                    @if($cartes_scolaire->eleve->utilisateur->photo_profil)
                        @php
                            $imageName = basename($cartes_scolaire->eleve->utilisateur->photo_profil);
                            $imagePath = 'storage/' . $cartes_scolaire->eleve->utilisateur->photo_profil;
                        @endphp
                        <img src="{{ asset($imagePath) }}" alt="Photo">
                    @else
                        <div style="text-align: center;">
                            <div style="font-size: 8mm; margin-bottom: 1mm;">👤</div>
                            <div>PHOTO</div>
                        </div>
                    @endif
                </div>
                
                <div class="matricule">
                    MAT. : {{ $cartes_scolaire->numero_carte }}
                </div>
            </div>
            
            <div class="carte-right">
                <div class="eleve-info">
                    <div class="eleve-details">
                        <div><strong>Année scolaire :</strong> {{ now()->year }}-{{ now()->year + 1 }}</div>
                        <div><strong>Nom :</strong> {{ strtoupper($cartes_scolaire->eleve->utilisateur->nom) }}</div>
                        <div><strong>Prénom :</strong> {{ strtoupper($cartes_scolaire->eleve->utilisateur->prenom) }}</div>
                        <div><strong>Né(e) le :</strong> {{ $cartes_scolaire->eleve->utilisateur->date_naissance ? $cartes_scolaire->eleve->utilisateur->date_naissance->format('d-m-Y') : 'Non définie' }} A {{ $cartes_scolaire->eleve->utilisateur->lieu_naissance ?? 'CONAKRY' }}</div>
                        <div class="info-row">
                            <span><strong>Sexe :</strong> {{ $cartes_scolaire->eleve->utilisateur->sexe == 'M' ? 'M' : 'F' }}</span>
                            <span><strong>Classe :</strong> {{ $cartes_scolaire->eleve->classe->nom ?? 'Non assigné' }}</span>
                        </div>
                        @if($cartes_scolaire->eleve->numero_etudiant)
                        <div><strong>Matricule :</strong> {{ $cartes_scolaire->eleve->numero_etudiant }}</div>
                        @endif
                        <div><strong>Contact :</strong> {{ $cartes_scolaire->eleve->utilisateur->telephone ?? '**********' }}</div>
                    </div>
                </div>
                
                <div class="qr-code">
                    {!! $cartes_scolaire->qr_code !!}
                </div>
            </div>
        </div>
        
        <div class="carte-footer">
            <span class="status-badge status-{{ $cartes_scolaire->statut }}">
                {{ $cartes_scolaire->statut_libelle }}
            </span>
            | Émise le {{ $cartes_scolaire->date_emission ? $cartes_scolaire->date_emission->format('d/m/Y') : 'Non définie' }}
        </div>
    </div>

    <script>
        // Auto-print when page loads
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 1000);
        };
    </script>
</body>
</html>
