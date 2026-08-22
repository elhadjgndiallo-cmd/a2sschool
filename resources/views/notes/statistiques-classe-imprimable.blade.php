<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Classement des Élèves - {{ $classe->nom }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Orientation portrait pour l'impression */
        @page {
            size: A4 portrait;
            margin: 10mm;
        }
        @media print {
            * {
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            .no-print { display: none !important; }
            .page-break { page-break-before: always; }
            body { font-size: 12px; }
            .table { font-size: 11px; }
            .table th, .table td { padding: 4px 6px; }
        }
        
        .print-header {
            border-bottom: 3px solid #007bff;
            margin-bottom: 20px;
            padding-bottom: 15px;
        }
        
        .school-info {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .class-title {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .stats-summary {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .table-print {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .table-print th {
            background: #007bff;
            color: white;
            font-weight: bold;
            text-align: center;
            padding: 8px;
            border: 1px solid #0056b3;
        }
        
        .table-print td {
            border: 1px solid #dee2e6;
            padding: 6px;
            text-align: center;
        }
        
        .table-print tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .rank-badge {
            font-weight: bold;
            padding: 4px 8px;
            border-radius: 4px;
            display: inline-block;
            min-width: 30px;
        }
        
        .rank-1 { background: #ffd700; color: #000; }
        .rank-2 { background: #c0c0c0; color: #000; }
        .rank-3 { background: #cd7f32; color: #fff; }
        .rank-other { background: #e9ecef; color: #495057; }
        
        .grade-badge {
            font-weight: bold;
            padding: 4px 8px;
            border-radius: 4px;
            display: inline-block;
        }
        
        .grade-excellent { background: #28a745; color: white; }
        .grade-very-good { background: #007bff; color: white; }
        .grade-good { background: #17a2b8; color: white; }
        .grade-fair { background: #ffc107; color: #000; }
        .grade-poor { background: #dc3545; color: white; }
        
        /* Classes dynamiques pour les couleurs d'appréciation */
        .grade-success { background: #28a745; color: white; }
        .grade-primary { background: #007bff; color: white; }
        .grade-info { background: #17a2b8; color: white; }
        .grade-warning { background: #ffc107; color: #000; }
        .grade-secondary { background: #6c757d; color: white; }
        .grade-danger { background: #dc3545; color: white; }
        
        .appreciation {
            font-weight: 500;
        }
        
        .appreciation.excellent { color: #28a745; }
        .appreciation.very-good { color: #007bff; }
        .appreciation.good { color: #17a2b8; }
        .appreciation.fair { color: #ffc107; }
        .appreciation.poor { color: #dc3545; }
        
        .footer-info {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #dee2e6;
            font-size: 10px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <!-- En-tête de l'établissement -->
        <div class="school-info">
            <h2 class="mb-1">{{ \App\Models\Etablissement::principal()->nom ?? 'ÉTABLISSEMENT SCOLAIRE' }}</h2>
            <h4 class="mb-1">{{ \App\Models\Etablissement::principal()->slogan ?? 'GESTION SCOLAIRE' }}</h4>
            <p class="mb-0 text-muted">Système de Gestion Scolaire</p>
        </div>

        <!-- Titre de la classe -->
        <div class="class-title text-center">
            <h3 class="mb-2">
                <i class="fas fa-chart-bar me-2"></i>
                CLASSEMENT DES ÉLÈVES
            </h3>
            <h4 class="mb-0">{{ $classe->nom }} - {{ \App\Helpers\PeriodeHelper::libelle($periode, $classe ?? null) }}</h4>
        </div>

        <!-- Statistiques générales -->
        <div class="stats-summary">
            <div class="row text-center">
                <div class="col-md-3">
                    <strong>Nombre d'élèves :</strong> {{ $statistiques->count() }}
                </div>
                <div class="col-md-3">
                    <strong>Moyenne de classe :</strong> 
                    {{ $statistiques->count() > 0 ? number_format($statistiques->pluck('moyenne')->avg(), 2) : '0.00' }}/20
                </div>
                <div class="col-md-3">
                    <strong>Meilleure moyenne :</strong> 
                    {{ $statistiques->count() > 0 ? number_format($statistiques->pluck('moyenne')->max(), 2) : '0.00' }}/20
                </div>
                <div class="col-md-3">
                    <strong>Date d'édition :</strong> {{ now()->format('d/m/Y à H:i') }}
                </div>
            </div>
        </div>

        @if($statistiques->count() > 0)
        <!-- Tableau de classement -->
        <table class="table-print">
            <thead>
                <tr>
                    <th width="12%">Matricule</th>
                    <th width="20%">Nom</th>
                    <th width="20%">Prénom</th>
                    <th width="12%">Moyenne</th>
                    <th width="8%">Rang</th>
                    <th width="28%">Appréciation</th>
                </tr>
            </thead>
            <tbody>
                @foreach($statistiques as $stat)
                <tr>
                    <td><strong>{{ $stat['eleve']->numero_etudiant }}</strong></td>
                    <td><strong>{{ $stat['eleve']->utilisateur->nom }}</strong></td>
                    <td><strong>{{ $stat['eleve']->utilisateur->prenom }}</strong></td>
                    <td>
                        @php
                            $appreciation = $classe->getAppreciation($stat['moyenne']);
                        @endphp
                        <span class="grade-badge grade-{{ $appreciation['color'] }}">
                            {{ number_format($stat['moyenne'], 2) }}/{{ $classe->note_max }}
                        </span>
                    </td>
                    <td>
                        <span class="rank-badge 
                            @if($stat['rang'] == 1) rank-1
                            @elseif($stat['rang'] == 2) rank-2
                            @elseif($stat['rang'] == 3) rank-3
                            @else rank-other
                            @endif">
                            {{ $stat['rang'] }}{{ $stat['rang'] == 1 ? 'er' : 'ème' }}
                        </span>
                    </td>
                    <td>
                        @php
                            $appreciation = $classe->getAppreciation($stat['moyenne']);
                        @endphp
                        <span class="appreciation {{ $appreciation['color'] }}">
                            @if($appreciation['label'] == 'Excellent')
                                ⭐ {{ $appreciation['label'] }}
                            @elseif($appreciation['label'] == 'Très bien')
                                👍 {{ $appreciation['label'] }}
                            @elseif($appreciation['label'] == 'Bien')
                                ✅ {{ $appreciation['label'] }}
                            @elseif($appreciation['label'] == 'Assez bien')
                                ⚠️ {{ $appreciation['label'] }}
                            @elseif($appreciation['label'] == 'Passable')
                                ➖ {{ $appreciation['label'] }}
                            @else
                                ❌ {{ $appreciation['label'] }}
                            @endif
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @else
        <div class="text-center text-muted py-5">
            <h5>Aucune note trouvée</h5>
            <p>Il n'y a pas encore de notes saisies pour cette période.</p>
        </div>
        @endif

        <!-- Pied de page -->
        <div class="footer-info text-center">
            <p class="mb-1">Document généré le {{ now()->format('d/m/Y à H:i') }}</p>
            <p class="mb-0">{{ \App\Models\Etablissement::principal()->nom ?? 'Système de Gestion Scolaire' }} - {{ $classe->nom }}</p>
            @if(\App\Models\Etablissement::principal()->adresse)
                <p class="mb-0 text-muted">{{ \App\Models\Etablissement::principal()->adresse }}</p>
            @endif
        </div>
    </div>

    <!-- Bouton d'impression (masqué à l'impression) -->
    <div class="no-print position-fixed" style="top: 20px; right: 20px; z-index: 1000;">
        <button onclick="window.print()" class="btn btn-primary btn-lg">
            <i class="fas fa-print me-2"></i>Imprimer
        </button>
        <a href="{{ route('notes.statistiques.classe', $classe->id) }}" class="btn btn-secondary btn-lg ms-2">
            <i class="fas fa-arrow-left me-2"></i>Retour
        </a>
    </div>

    <script>
        // Auto-impression au chargement de la page
        window.onload = function() {
            // Optionnel : décommenter pour auto-impression
            // window.print();
        }
    </script>
</body>
</html>
