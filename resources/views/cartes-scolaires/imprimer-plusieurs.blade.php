<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Impression de Cartes Scolaires - Version {{ date('YmdHis') }}</title>
    <!-- Version: {{ time() }} -->
    <style>
        @page {
            size: A4;
            margin: 0;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background: white;
        }
        
        .page {
            width: 210mm;
            height: 297mm;
            page-break-after: always;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            grid-template-rows: repeat(5, 1fr);
            gap: 2.5mm;
            padding: 3mm;
            justify-items: center;
            align-items: start;
        }
        
        .carte-container {
            width: 86mm;
            height: 54mm;
            border: 2px solid #d4af37;
            margin: 0;
            position: relative;
            background: white;
            page-break-inside: avoid;
            overflow: hidden;
            box-sizing: border-box;
        }
        
        .carte-header {
            background: white;
            color: black;
            padding: 2mm;
            text-align: center;
            position: relative;
            height: 12mm;
            border-bottom: 1px solid #d4af37;
            box-sizing: border-box;
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
            background: #d4af37;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4mm;
            color: #1e3c72;
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
            color: black;
        }
        
        .motto {
            font-size: 2mm;
            margin: 0;
            color: #666;
        }
        
        .school-name {
            font-size: 3.5mm;
            font-weight: bold;
            margin: 0;
            color: black;
        }
        
        .card-title {
            font-size: 3mm;
            font-weight: bold;
            margin: 0;
            color: #d4af37;
        }
        
        .logo-right {
            width: 8mm;
            height: 8mm;
            background: #d4af37;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3mm;
            color: #1e3c72;
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
            border-right: 1px solid #d4af37;
        }
        
        .carte-right {
            width: 55mm;
            padding-left: 2mm;
            padding-right: 2mm;
            position: relative;
            margin-right: 0;
        }
        
        .eleve-photo {
            width: 20mm;
            height: 25mm;
            border: 1px solid #d4af37;
            margin: 0 auto 2mm;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5mm;
            color: #666;
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
            color: #d4af37;
            text-align: center;
            margin-bottom: 2mm;
        }
        
        .eleve-info {
            font-size: 2.5mm;
            line-height: 1.4;
        }
        
        .eleve-details {
            font-size: 2.3mm;
            color: #333;
        }
        
        .eleve-details strong {
            font-size: 2.3mm;
        }
        
        .eleve-details div {
            margin-bottom: 0.5mm;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5mm;
        }
        
        .info-row-left {
            flex: 1;
        }
        
        .info-row-right {
            text-align: right;
            white-space: nowrap;
            padding-right: 2mm;
            margin-right: 0;
        }
        
        .info-label {
            font-weight: bold;
        }
        
        .qr-code {
            position: absolute;
            top: 2mm;
            right: 2mm;
            width: 12mm;
            height: 12mm;
            border: 1px solid #d4af37;
            overflow: hidden;
            margin: 0;
            padding: 0;
        }
        
        .classe-row {
            text-align: right;
            margin-top: 0.5mm;
            padding-right: 0;
        }
        
        .qr-code img {
            width: 100%;
            height: 100%;
        }
        
        .carte-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            font-size: 1.8mm;
            color: #666;
            text-align: center;
            border-top: 1px solid #d4af37;
            padding: 1mm 2mm;
            display: flex;
            justify-content: center;
            align-items: center;
            box-sizing: border-box;
        }
        
        .footer-left {
            color: #d4af37;
            font-weight: bold;
            font-size: 2.5mm;
            position: absolute;
            left: 2mm;
        }
        
        .footer-right {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.5mm 1mm;
            border-radius: 1mm;
            font-size: 1.8mm;
            font-weight: bold;
            color: white;
        }
        
        .status-active { background: #28a745; }
        .status-expiree { background: #dc3545; }
        .status-suspendue { background: #ffc107; color: #000; }
        .status-annulee { background: #6c757d; }
        
        .decorative-border {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border: 1mm solid transparent;
            border-image: linear-gradient(45deg, #d4af37, #1e3c72, #d4af37) 1;
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
            .page {
                page-break-after: always;
            }
            .page:last-child {
                page-break-after: auto;
            }
        }
        
        @media screen {
            .no-print {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                background: #007bff;
                color: white;
                padding: 15px;
                text-align: center;
                z-index: 1000;
                box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            }
            
            .no-print button {
                background: white;
                color: #007bff;
                border: none;
                padding: 10px 20px;
                margin: 0 10px;
                border-radius: 5px;
                cursor: pointer;
                font-weight: bold;
            }
            
            .no-print button:hover {
                background: #f0f0f0;
            }
            
            body {
                padding-top: 70px;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; padding: 15px;">
        <h3 style="margin: 0 0 10px 0;">Impression de {{ $cartes->count() }} carte(s) - Format 10 par page A4</h3>
        <button onclick="window.print()">
            <i class="fas fa-print"></i> Imprimer
        </button>
        <button onclick="window.close()">
            Fermer
        </button>
    </div>

    @foreach($cartesParPage as $pageCartes)
        <div class="page">
            @foreach($pageCartes as $carte)
                <div class="carte-container">
                    <div class="decorative-border"></div>
                    
                    <div class="carte-header">
                        <div class="header-content">
                            <div class="logo-left">
                                @php
                                    $school = \App\Helpers\SchoolHelper::getSchoolInfo();
                                    $logoUrl = $school && isset($school->logo) ? asset('storage/' . $school->logo) : null;
                                @endphp
                                @if($logoUrl && file_exists(public_path('storage/' . $school->logo)))
                                    <img src="{{ $logoUrl }}" alt="Logo">
                                @else
                                    🇬🇳
                                @endif
                            </div>
                            <div class="header-text">
                                <div class="country-name">RÉPUBLIQUE DE GUINÉE</div>
                                <div class="motto">Travail - Justice - Solidarité</div>
                                <div class="school-name">{{ $school->nom ?? 'A2 SCHOOL' }}</div>
                                <div class="card-title">CARTE D'IDENTITÉ SCOLAIRE</div>
                            </div>
                            <div class="logo-right">
                                @if($logoUrl && file_exists(public_path('storage/' . $school->logo)))
                                    <img src="{{ $logoUrl }}" alt="Logo">
                                @else
                                    📚
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="carte-body">
                        <div class="carte-left">
                            <div class="eleve-photo">
                                @if($carte->eleve->utilisateur->photo_profil)
                                    @php
                                        $imagePath = 'storage/' . $carte->eleve->utilisateur->photo_profil;
                                    @endphp
                                    @if(file_exists(public_path($imagePath)))
                                        <img src="{{ asset($imagePath) }}" alt="Photo">
                                    @else
                                        <div style="text-align: center;">
                                            <div style="font-size: 8mm; margin-bottom: 1mm;">👤</div>
                                            <div>PHOTO</div>
                                        </div>
                                    @endif
                                @else
                                    <div style="text-align: center;">
                                        <div style="font-size: 8mm; margin-bottom: 1mm;">👤</div>
                                        <div>PHOTO</div>
                                    </div>
                                @endif
                            </div>
                            
                        </div>
                        
                        <div class="carte-right">
                            <div class="eleve-info">
                                <div class="eleve-details">
                                    <div><strong>Année scolaire :</strong> {{ $carte->date_emission ? $carte->date_emission->format('Y') : now()->year }}-{{ $carte->date_expiration ? $carte->date_expiration->format('Y') : now()->year + 1 }}</div>
                                    <div><strong>Nom :</strong> {{ strtoupper($carte->eleve->utilisateur->nom ?? '') }}</div>
                                    <div><strong>Prénom :</strong> {{ strtoupper($carte->eleve->utilisateur->prenom ?? '') }}</div>
                                    <div><strong>Né(e) le :</strong> {{ $carte->eleve->utilisateur->date_naissance ? $carte->eleve->utilisateur->date_naissance->format('d-m-Y') : 'Non définie' }} A {{ strtoupper($carte->eleve->utilisateur->lieu_naissance ?? 'CONAKRY') }}</div>
                                    <div class="info-row">
                                        <span class="info-row-left"><strong>Sexe :</strong> {{ $carte->eleve->utilisateur->sexe == 'M' ? 'M' : 'F' }}</span>
                                        <span class="info-row-right"><strong>Classe :</strong> {{ $carte->eleve->classe->nom ?? 'Non assigné' }}</span>
                                    </div>
                                    @if($carte->eleve->numero_etudiant)
                                    <div><strong>Matricule :</strong> {{ $carte->eleve->numero_etudiant }}</div>
                                    @endif
                                    <div><strong>Contact :</strong> {{ $carte->eleve->utilisateur->telephone ?? '**********' }}</div>
                                </div>
                            </div>
                            
                            <div class="qr-code">
                                {!! $carte->qr_code !!}
                            </div>
                        </div>
                    </div>
                    
                    <div class="carte-footer">
                        <span class="footer-left">MAT. : {{ $carte->numero_carte }}</span>
                        <span class="footer-right">
                            <span class="status-badge status-{{ $carte->statut }}">
                                {{ $carte->statut_libelle }}
                            </span>
                            | Émise le {{ $carte->date_emission ? $carte->date_emission->format('d/m/Y') : 'Non définie' }}
                        </span>
                    </div>
                </div>
            @endforeach
            
            {{-- Remplir les emplacements vides si moins de 10 cartes --}}
            @for($i = $pageCartes->count(); $i < 10; $i++)
                <div class="carte-container" style="visibility: hidden;">
                    <!-- Carte vide pour maintenir la grille -->
                </div>
            @endfor
        </div>
    @endforeach

    <script>
        // Forcer le rechargement sans cache au chargement
        if (window.performance && window.performance.navigation && window.performance.navigation.type === 1) {
            // La page a été rechargée - forcer un hard reload
            setTimeout(function() {
                window.location.reload(true);
            }, 100);
        }
        
        // Vérifier et forcer le rechargement si nécessaire
        if (window.history.replaceState) {
            var url = window.location.href;
            if (!url.includes('t=') || url.includes('t=0')) {
                var separator = url.includes('?') ? '&' : '?';
                window.location.replace(url + separator + 't=' + Date.now());
            }
        }
        
        // Auto-impression au chargement
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
        
        // Empêcher le cache du navigateur
        window.addEventListener('beforeunload', function() {
            if (window.history.replaceState) {
                window.history.replaceState(null, null, window.location.href.split('&t=')[0] + '&t=' + new Date().getTime());
            }
        });
    </script>
</body>
</html>
