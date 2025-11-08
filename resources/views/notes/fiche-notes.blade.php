<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fiche de Notes - {{ $matiere->nom }} - {{ $classe->nom }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Format A4 paysage */
        @page {
            size: A4 landscape;
            margin: 8mm;
        }
        
        @media print {
            * {
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            .no-print { 
                display: none !important; 
            }
            
            body { 
                margin: 0; 
                padding: 0;
                background: white !important;
            }
            
            .page-break { 
                page-break-before: always; 
            }
            
            html, body {
                width: 297mm;
                height: 210mm;
            }
            
            .container-print {
                width: 100%;
                max-width: 100%;
                margin: 0;
                padding: 0;
            }
        }
        
        body {
            font-family: 'Arial', sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 10px;
            background: #f5f5f5;
        }
        
        .container-print {
            background: white;
            padding: 15px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 3px solid #1e3a8a;
        }
        
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .header-logo-left,
        .header-logo-right {
            flex-shrink: 0;
            width: 80px;
        }
        
        .header-content-center {
            flex: 1;
            text-align: center;
            margin: 0 20px;
        }
        
        .header h1 {
            font-size: 18px;
            font-weight: bold;
            margin: 4px 0;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #1e3a8a;
        }
        
        .header h2 {
            font-size: 14px;
            font-weight: bold;
            margin: 3px 0;
            color: #1e3a8a;
        }
        
        .header h3 {
            font-size: 11px;
            margin: 2px 0;
            font-weight: normal;
            color: #333;
        }
        
        .header-title {
            font-size: 16px;
            font-weight: bold;
            margin-top: 10px;
            padding: 8px;
            background: #1e3a8a;
            color: white;
            border-radius: 4px;
        }
        
        .header-logo-section {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 8px 0;
            gap: 15px;
        }
        
        .school-logo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 3px solid #1e3a8a;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            flex-shrink: 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .school-logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }
        
        .school-logo-text {
            text-align: center;
            font-size: 9px;
            font-weight: bold;
            padding: 8px;
            color: #1e3a8a;
        }
        
        .school-phone {
            font-size: 10px;
            margin-top: 5px;
            font-weight: bold;
            color: #1e3a8a;
        }
        
        .teacher-info {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
            padding: 8px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
        }
        
        .teacher-photo {
            width: 70px;
            height: 70px;
            border-radius: 4px;
            border: 2px solid #000;
            overflow: hidden;
            margin-right: 12px;
            flex-shrink: 0;
        }
        
        .teacher-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .teacher-photo-placeholder {
            width: 100%;
            height: 100%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 9px;
            color: #6c757d;
        }
        
        .teacher-details {
            flex: 1;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 3px 15px;
        }
        
        .teacher-details p {
            margin: 2px 0;
            font-size: 10px;
        }
        
        .table-container {
            width: 100%;
            margin-top: 8px;
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            table-layout: fixed;
        }
        
        table thead th {
            background: #1e3a8a;
            color: white;
            padding: 8px 5px;
            text-align: center;
            border: 1px solid #000;
            font-weight: bold;
            font-size: 11px;
        }
        
        table tbody td {
            padding: 6px 5px;
            border: 1px solid #000;
            text-align: center;
            font-size: 11px;
            word-wrap: break-word;
        }
        
        table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .matricule {
            font-weight: bold;
            width: 70px;
        }
        
        .nom {
            text-align: left;
            padding-left: 4px;
            width: 100px;
        }
        
        .prenoms {
            text-align: left;
            padding-left: 4px;
            width: 130px;
        }
        
        .sexe {
            width: 45px;
        }
        
        .note-col {
            width: 60px;
        }
        
        .no-print {
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        
        .btn-print {
            margin-right: 10px;
        }
        
        @media print {
            .container-print {
                box-shadow: none;
                padding: 0;
            }
            
            .header {
                margin-bottom: 10px;
                padding-bottom: 6px;
            }
            
            .teacher-info {
                margin-bottom: 10px;
                padding: 6px;
            }
            
            .table-container {
                margin-top: 6px;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2><i class="fas fa-file-alt me-2"></i>Fiche de Notes - {{ $matiere->nom }}</h2>
            <div>
                <button onclick="window.print()" class="btn btn-primary btn-print">
                    <i class="fas fa-print me-2"></i>Imprimer
                </button>
                <a href="{{ route('notes.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour
                </a>
            </div>
        </div>
    </div>

    <div class="container-print">
        <div class="header">
            <div class="header-top">
                <div class="header-logo-left">
                    <div class="school-logo">
                        @if($etablissement && $etablissement->logo)
                            <img src="{{ asset('storage/' . $etablissement->logo) }}" alt="Logo">
                        @else
                            <div class="school-logo-text">
                                <div style="font-size: 10px; line-height: 1.2;">COMPLEXE</div>
                                <div style="font-size: 10px; line-height: 1.2;">SCOLAIRE</div>
                                <div style="font-size: 12px; margin-top: 3px; font-weight: bold;">CS</div>
                            </div>
                        @endif
                    </div>
                </div>
                
                <div class="header-content-center">
                    <h1>REPUBLIQUE DE GUINEE</h1>
                    <h2>Travail - Justice - Solidarité</h2>
                    @if($etablissement)
                        <h3 style="font-weight: bold; color: #1e3a8a; margin-top: 5px;">{{ strtoupper($etablissement->nom) }}</h3>
                    @else
                        <h3 style="font-weight: bold; color: #1e3a8a; margin-top: 5px;">COMPLEXE SCOLAIRE</h3>
                    @endif
                    @if($etablissement && $etablissement->telephone)
                        <div class="school-phone" style="margin-top: 5px;">{{ $etablissement->telephone }}</div>
                    @endif
                </div>
                
                <div class="header-logo-right">
                    <div class="school-logo">
                        @if($etablissement && $etablissement->logo)
                            <img src="{{ asset('storage/' . $etablissement->logo) }}" alt="Logo">
                        @else
                            <div class="school-logo-text">
                                <div style="font-size: 10px; line-height: 1.2;">COMPLEXE</div>
                                <div style="font-size: 10px; line-height: 1.2;">SCOLAIRE</div>
                                <div style="font-size: 12px; margin-top: 3px; font-weight: bold;">CS</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="header-title">
                FICHE DE NOTES DU {{ $semestre == 'sem1' ? 'PREMIER' : 'DEUXIÈME' }} SEMESTRE
            </div>
        </div>

        <div class="teacher-info">
            <div class="teacher-photo">
                @if($enseignant->utilisateur && $enseignant->utilisateur->photo_profil)
                    <img src="{{ asset('storage/' . $enseignant->utilisateur->photo_profil) }}" alt="Photo enseignant">
                @else
                    <div class="teacher-photo-placeholder">
                        Photo
                    </div>
                @endif
            </div>
            <div class="teacher-details">
                <p><strong>Professeur :</strong> {{ strtoupper($enseignant->utilisateur->nom ?? '') }} {{ ucfirst($enseignant->utilisateur->prenom ?? '') }}</p>
                <p><strong>Matière :</strong> {{ strtoupper($matiere->nom) }}</p>
                <p><strong>Tel :</strong> {{ $enseignant->utilisateur->telephone ?? ($etablissement->telephone ?? '') }}</p>
                <p><strong>Année scolaire :</strong> {{ $anneeScolaireActive->nom ?? '' }}</p>
                <p><strong>Classe :</strong> {{ strtoupper($classe->nom) }}</p>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Matricule</th>
                        <th>Nom</th>
                        <th>Prénoms</th>
                        <th>Sexe</th>
                        @if($semestre == 'sem1')
                            <th>Octobre</th>
                            <th>Novembre</th>
                            <th>Décembre</th>
                        @else
                            <th>Janvier</th>
                            <th>Février</th>
                            <th>Mars</th>
                        @endif
                        <th>Moyenne Cours</th>
                        <th>Note Composition</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($elevesAvecNotes as $data)
                        <tr>
                            <td class="matricule">{{ $data['eleve']->numero_etudiant ?? '-' }}</td>
                            <td class="nom">{{ strtoupper($data['eleve']->utilisateur->nom ?? '') }}</td>
                            <td class="prenoms">{{ ucfirst($data['eleve']->utilisateur->prenom ?? '') }}</td>
                            <td class="sexe">{{ strtoupper($data['eleve']->utilisateur->sexe ?? 'M') }}</td>
                            @if($semestre == 'sem1')
                                <td class="note-col"></td>
                                <td class="note-col"></td>
                                <td class="note-col"></td>
                            @else
                                <td class="note-col"></td>
                                <td class="note-col"></td>
                                <td class="note-col"></td>
                            @endif
                            <td class="note-col"></td>
                            <td class="note-col"></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 20px;">
                                Aucun élève trouvé
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

