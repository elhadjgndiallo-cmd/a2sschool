<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Élèves</title>
    <style>
        /* Styles pour l'impression */
        @media print {
            @page {
                size: A4 portrait;
                margin: 0.8cm;
            }
            
            body {
                margin: 0;
                padding: 0 0 40px 0;
                font-family: Arial, sans-serif;
                font-size: 14px;
                line-height: 1.4;
                color: #000;
                background: white;
            }
            
            .no-print {
                display: none !important;
            }
            
            .page-break {
                page-break-before: always;
            }
            
            .header {
                margin-bottom: 8px;
                border-bottom: 1px solid #000;
                padding-bottom: 5px;
            }
            
            .header-content {
                text-align: center;
            }
            
            .school-info {
                display: flex;
                align-items: center;
                justify-content: center;
                margin-bottom: 8px;
                gap: 10px;
            }
            
            .school-logo {
                flex-shrink: 0;
            }
            
            .logo-image {
                max-width: 45px;
                max-height: 45px;
                object-fit: contain;
            }
            
            .school-details {
                text-align: left;
            }
            
            .school-name {
                margin: 0;
                font-size: 22px;
                font-weight: bold;
                color: #000;
                line-height: 1.1;
            }
            
            .school-slogan {
                margin: 2px 0 0 0;
                font-size: 13px;
                color: #666;
                font-style: italic;
            }
            
            .document-title {
                margin-bottom: 5px;
            }
            
            .document-title h2 {
                margin: 0;
                font-size: 20px;
                font-weight: bold;
                color: #000;
            }
            
            .class-title {
                margin: 2px 0 0 0;
                font-size: 18px;
                font-weight: bold;
                color: #007bff;
            }
            
            .document-info {
                margin-top: 3px;
            }
            
            .generation-info {
                margin: 0;
                font-size: 12px;
                color: #666;
            }
            
            .info-section {
                margin-bottom: 8px;
                font-size: 14px;
            }
            
            .info-section .label {
                font-weight: bold;
                display: inline-block;
                width: 120px;
            }
            
            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 5px;
                font-size: 13px;
            }
            
            table th {
                background-color: #f5f5f5;
                border: 1px solid #000;
                padding: 4px 6px;
                text-align: center;
                font-weight: bold;
                font-size: 12px;
            }
            
            table td {
                border: 1px solid #000;
                padding: 3px 6px;
                text-align: left;
                vertical-align: top;
                font-size: 12px;
            }
            
            .student-number {
                font-weight: bold;
                text-align: center;
            }
            
            .student-name {
                font-weight: bold;
            }
            
            .class-info {
                text-align: center;
            }
            
            .status-badge {
                text-align: center;
                font-weight: bold;
            }
            
            .fees-info {
                text-align: center;
                font-size: 11px;
            }
            
            .footer {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                font-size: 10px;
                color: #666;
                padding: 5px;
                background-color: white;
                border-top: 1px solid #ccc;
            }
            
            .footer-content {
                display: flex;
                justify-content: space-between;
                align-items: center;
                flex-wrap: wrap;
                gap: 5px;
            }
            
            .footer-school-info {
                flex: 1;
                min-width: 150px;
            }
            
            .footer-document-info {
                flex: 1;
                text-align: right;
                min-width: 150px;
            }
            
            .school-address,
            .school-phone {
                margin: 0;
                font-size: 9px;
                line-height: 1.1;
            }
            
            .footer-document-info p {
                margin: 0;
                font-size: 9px;
                color: #666;
                font-style: italic;
            }
            
            .summary {
                margin-bottom: 8px;
                padding: 6px;
                background-color: #f8f9fa;
                border: 1px solid #dee2e6;
                border-radius: 3px;
                font-size: 13px;
            }
            
            .summary-content {
                display: flex;
                align-items: center;
                flex-wrap: wrap;
                gap: 10px;
            }
            
            .summary-icon {
                font-size: 14px;
                margin-right: 5px;
            }
            
            .summary-item {
                padding: 3px 6px;
                background-color: #e9ecef;
                border-radius: 2px;
                font-size: 13px;
            }
            
            .class-section {
                margin-bottom: 8px;
            }
            
            .class-header {
                background-color: #f8f9fa;
                border: 1px solid #dee2e6;
                border-radius: 3px;
                margin-bottom: 8px;
                overflow: hidden;
            }
            
            .class-title {
                background-color: #e9ecef;
                padding: 5px 8px;
                border-bottom: 1px solid #dee2e6;
                display: flex;
                align-items: center;
            }
            
            .class-icon {
                font-size: 12px;
                margin-right: 5px;
            }
            
            .class-name {
                font-size: 15px;
                font-weight: bold;
                color: #495057;
            }
            
            .class-stats {
                padding: 6px 10px;
                background-color: #ffffff;
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                font-size: 13px;
            }
            
            .stat-item {
                padding: 3px 6px;
                background-color: #f8f9fa;
                border-radius: 2px;
                color: #495057;
            }
            
            .students-table {
                margin-bottom: 8px;
            }
            
            /* Forcer le contenu à commencer immédiatement après l'en-tête */
            .class-section:first-of-type {
                page-break-before: avoid;
            }
            
            /* Éviter les sauts de page dans les tableaux */
            .students-table {
                page-break-inside: auto;
            }
            
            .students-table tbody tr {
                page-break-inside: avoid;
            }
            
        }
        
        /* Styles pour l'aperçu à l'écran */
        @media screen {
            body {
                font-family: Arial, sans-serif;
                margin: 20px;
                background-color: #f5f5f5;
            }
            
            .print-container {
                background: white;
                padding: 20px;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
                max-width: 1000px;
                margin: 0 auto;
            }
            
            .print-actions {
                text-align: center;
                margin-bottom: 20px;
            }
            
            .btn {
                display: inline-block;
                padding: 10px 20px;
                margin: 0 5px;
                background-color: #007bff;
                color: white;
                text-decoration: none;
                border-radius: 4px;
                border: none;
                cursor: pointer;
            }
            
            .btn:hover {
                background-color: #0056b3;
            }
            
            .btn-success {
                background-color: #28a745;
            }
            
            .btn-success:hover {
                background-color: #1e7e34;
            }
            
            .filters-section {
                background-color: #f8f9fa;
                padding: 20px;
                margin-bottom: 20px;
                border: 1px solid #dee2e6;
                border-radius: 5px;
            }
            
            .filters-section h4 {
                margin-bottom: 15px;
                color: #495057;
            }
            
            .form-label {
                font-weight: bold;
                color: #495057;
                margin-bottom: 5px;
            }
            
            .form-control {
                width: 100%;
                padding: 8px 12px;
                border: 1px solid #ced4da;
                border-radius: 4px;
                font-size: 14px;
            }
            
            .form-control:focus {
                border-color: #007bff;
                box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
            }
        }
    </style>
</head>
<body>
    <div class="print-container">
        <!-- Actions d'impression (masquées lors de l'impression) -->
        <div class="print-actions no-print">
            <button onclick="window.print()" class="btn btn-success">🖨️ Imprimer</button>
            <a href="{{ route('eleves.index') }}" class="btn">← Retour à la liste</a>
        </div>

        <!-- Filtres pour l'impression -->
        <div class="filters-section no-print">
            <h4>🔍 Filtres d'impression</h4>
            <form method="GET" action="{{ route('eleves.print') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="classe_id" class="form-label">Classe :</label>
                    <select name="classe_id" id="classe_id" class="form-control">
                        <option value="">Toutes les classes</option>
                        @foreach($classes as $classe)
                            <option value="{{ $classe->id }}" {{ request('classe_id') == $classe->id ? 'selected' : '' }}>
                                {{ $classe->nom }} {{ $classe->niveau ? '(' . $classe->niveau . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="statut" class="form-label">Statut :</label>
                    <select name="statut" id="statut" class="form-control">
                        <option value="">Tous les statuts</option>
                        <option value="actif" {{ request('statut') == 'actif' ? 'selected' : '' }}>Actifs</option>
                        <option value="inactif" {{ request('statut') == 'inactif' ? 'selected' : '' }}>Inactifs</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="annee_scolaire_id" class="form-label">Année scolaire :</label>
                    <select name="annee_scolaire_id" id="annee_scolaire_id" class="form-control">
                        <option value="">Toutes les années</option>
                        @foreach($anneesScolarires as $annee)
                            <option value="{{ $annee->id }}" {{ request('annee_scolaire_id') == $annee->id ? 'selected' : '' }}>
                                {{ $annee->nom }} ({{ $annee->date_debut->format('Y') }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div>
                        <button type="submit" class="btn btn-primary w-100">🔍 Filtrer</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- En-tête -->
        <div class="header">
            <div class="header-content">
                <!-- Logo et nom de l'école -->
                <div class="school-info">
                    @if($schoolInfo['logo_url'])
                        <div class="school-logo">
                            <img src="{{ $schoolInfo['logo_url'] }}" alt="Logo" class="logo-image">
                        </div>
                    @endif
                    <div class="school-details">
                        <h1 class="school-name">{{ $schoolInfo['school_name'] ?? config('app.name') }}</h1>
                        @if($schoolInfo['school_slogan'])
                            <p class="school-slogan">"{{ $schoolInfo['school_slogan'] }}"</p>
                        @endif
                    </div>
                </div>
                
                <!-- Titre du document -->
                <div class="document-title">
                    @if(request('classe_id'))
                        @php $classe = $classes->find(request('classe_id')); @endphp
                        @if($classe)
                            <h2 class="class-title">CLASSE: {{ $classe->nom }}</h2>
                        @else
                            <h2>LISTE DES ÉLÈVES</h2>
                        @endif
                    @else
                        <h2>LISTE DES ÉLÈVES</h2>
                    @endif
                </div>
                
                <!-- Informations de génération -->
                <div class="document-info">
                    <p class="generation-info">
                        Généré le {{ now()->format('d/m/Y à H:i') }}
                        @if(request('statut'))
                            | {{ request('statut') == 'actif' ? 'Élèves actifs' : 'Élèves inactifs' }}
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <!-- Informations de résumé -->
        <div class="summary">
            <div class="summary-content">
                <span class="summary-icon">📊</span>
                <strong>Résumé :</strong>
                <span class="summary-item">Total d'élèves : <strong>{{ $eleves->count() }}</strong></span>
                <span class="summary-item">Actifs : <strong>{{ $eleves->where('actif', true)->count() }}</strong></span>
                <span class="summary-item">Inactifs : <strong>{{ $eleves->where('actif', false)->count() }}</strong></span>
            </div>
        </div>


        <!-- Liste organisée par classe -->
        @php
            $elevesParClasse = $eleves->groupBy(function($eleve) {
                return $eleve->classe ? $eleve->classe->nom : 'Sans classe';
            });
        @endphp

        @foreach($elevesParClasse as $nomClasse => $elevesClasse)
            <div class="class-section">
                <div class="class-header">
                    <div class="class-title">
                        <span class="class-icon">📚</span>
                        <span class="class-name">CLASSE: {{ $nomClasse }}</span>
                    </div>
                    <div class="class-stats">
                        <span class="stat-item">Effectif : <strong>{{ $elevesClasse->count() }}</strong> élève(s)</span>
                        <span class="stat-item">Actifs : <strong>{{ $elevesClasse->where('actif', true)->count() }}</strong></span>
                        <span class="stat-item">Inactifs : <strong>{{ $elevesClasse->where('actif', false)->count() }}</strong></span>
                    </div>
                </div>
                
                <table class="students-table">
                    <thead>
                        <tr>
                            <th style="width: 8%;">N°</th>
                            <th style="width: 20%;">Matricule</th>
                            <th style="width: 36%;">Nom</th>
                            <th style="width: 36%;">Prénom</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($elevesClasse as $index => $eleve)
                            <tr>
                                <td class="student-number">{{ $index + 1 }}</td>
                                <td class="student-number">{{ $eleve->numero_etudiant ?? 'N/A' }}</td>
                                <td class="student-name">{{ $eleve->utilisateur->nom ?? 'N/A' }}</td>
                                <td class="student-name">{{ $eleve->utilisateur->prenom ?? 'N/A' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach

        @if($eleves->count() === 0)
            <div class="no-students">
                <p style="text-align: center; padding: 20px; font-style: italic;">
                    Aucun élève trouvé
                </p>
            </div>
        @endif

        <!-- Pied de page -->
        <div class="footer">
            <div class="footer-content">
                <div class="footer-school-info">
                    @if($schoolInfo['school_address'])
                        <p class="school-address">
                            <strong>Adresse :</strong> {{ $schoolInfo['school_address'] }}
                        </p>
                    @endif
                    @if($schoolInfo['school_phone'])
                        <p class="school-phone">
                            <strong>Téléphone :</strong> {{ $schoolInfo['school_phone'] }}
                        </p>
                    @endif
                </div>
                <div class="footer-document-info">
                    <p><em>{{ $schoolInfo['school_name'] ?? config('app.name') }} - Système de Gestion Scolaire</em></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-impression optionnelle (décommentez si souhaité)
        // window.onload = function() {
        //     setTimeout(function() {
        //         window.print();
        //     }, 1000);
        // };
    </script>
</body>
</html>
