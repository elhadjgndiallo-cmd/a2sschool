<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des élèves</title>
    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: #111;
            background: #e9ecef;
        }

        .no-print { }

        .toolbar {
            background: #fff;
            border-bottom: 1px solid #dee2e6;
            padding: 14px 16px;
            position: sticky;
            top: 0;
            z-index: 20;
        }

        .toolbar-actions {
            display: flex;
            gap: 8px;
            justify-content: center;
            margin-bottom: 12px;
        }

        .btn {
            display: inline-block;
            padding: 8px 16px;
            background: #0d6efd;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }

        .btn:hover { background: #0b5ed7; }
        .btn-success { background: #198754; }
        .btn-success:hover { background: #157347; }
        .btn-secondary { background: #6c757d; }
        .btn-secondary:hover { background: #5c636a; }

        .filters {
            display: grid;
            grid-template-columns: repeat(4, minmax(140px, 1fr));
            gap: 10px;
            max-width: 960px;
            margin: 0 auto;
            align-items: end;
        }

        .filters label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 4px;
            color: #495057;
        }

        .filters select,
        .filters button {
            width: 100%;
            padding: 8px 10px;
            font-size: 14px;
            border: 1px solid #ced4da;
            border-radius: 4px;
        }

        .sheet {
            width: 210mm;
            min-height: 297mm;
            margin: 16px auto 32px;
            background: #fff;
            padding: 12mm 12mm 18mm;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.12);
        }

        .header-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 8px;
        }

        .header-logo {
            flex: 0 0 70px;
            text-align: left;
        }

        .header-logo img {
            max-width: 68px;
            max-height: 68px;
            object-fit: contain;
        }

        .header-center {
            flex: 1;
            text-align: center;
        }

        .school-name {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .school-slogan,
        .school-year {
            margin: 2px 0 0;
            font-size: 11px;
            color: #555;
        }

        .header-right {
            flex: 0 0 28%;
            text-align: right;
            font-size: 11px;
            line-height: 1.35;
            color: #333;
        }

        .header-right p { margin: 0 0 3px; }

        .header-separator {
            border: 0;
            border-top: 2px solid #222;
            margin: 8px 0 10px;
        }

        .doc-title {
            text-align: center;
            margin: 0 0 4px;
            font-size: 16px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .doc-meta {
            text-align: center;
            margin: 0 0 12px;
            font-size: 11px;
            color: #555;
        }

        .summary {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 12px;
            font-size: 12px;
        }

        .summary span {
            border: 1px solid #ccc;
            padding: 3px 8px;
            background: #f8f8f8;
        }

        .class-section {
            margin-bottom: 16px;
            page-break-inside: auto;
        }

        .class-banner {
            background: #f0f0f0;
            border: 1px solid #222;
            border-bottom: none;
            padding: 5px 8px;
            font-size: 13px;
            font-weight: 700;
            display: flex;
            justify-content: space-between;
            gap: 12px;
        }

        .class-banner .effectif {
            font-weight: 600;
            font-size: 12px;
        }

        table.students-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 12px;
        }

        .students-table thead {
            display: table-header-group;
        }

        .students-table th,
        .students-table td {
            border: 1px solid #222;
            padding: 5px 7px;
            vertical-align: middle;
        }

        .students-table th {
            background: #f5f5f5;
            text-align: center;
            font-size: 11px;
            font-weight: 700;
        }

        .students-table td.col-num,
        .students-table td.col-mat {
            text-align: center;
        }

        .students-table td.col-nom {
            font-weight: 700;
            text-transform: uppercase;
        }

        .empty {
            text-align: center;
            padding: 24px;
            font-style: italic;
            color: #666;
        }

        .footer {
            margin-top: 14px;
            padding-top: 6px;
            border-top: 1px solid #999;
            font-size: 10px;
            color: #555;
            display: flex;
            justify-content: space-between;
            gap: 12px;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 10mm 10mm 12mm;
            }

            html, body {
                background: #fff !important;
                margin: 0;
                padding: 0;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .no-print { display: none !important; }

            .sheet {
                width: auto;
                min-height: 0;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }

            .class-section {
                page-break-inside: auto;
            }

            .students-table tr {
                page-break-inside: avoid;
            }

            .class-banner,
            .students-table th,
            .summary span {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }

        @media (max-width: 900px) {
            .filters { grid-template-columns: 1fr 1fr; }
            .sheet { width: auto; margin: 12px; padding: 16px; }
        }
    </style>
</head>
<body>
@php
    $classeSelectionnee = request('classe_id') ? $classes->firstWhere('id', (int) request('classe_id')) : null;
    $elevesParClasse = $eleves
        ->sortBy(function ($eleve) {
            return mb_strtolower(trim(($eleve->utilisateur->nom ?? '') . ' ' . ($eleve->utilisateur->prenom ?? '')));
        })
        ->groupBy(function ($eleve) {
            return $eleve->classe->nom ?? 'Sans classe';
        })
        ->sortKeys();
@endphp

<div class="toolbar no-print">
    <div class="toolbar-actions">
        <button type="button" class="btn btn-success" onclick="window.print()">Imprimer</button>
        <a href="{{ route('eleves.index', request()->query()) }}" class="btn btn-secondary">Retour à la liste</a>
    </div>
    <form method="GET" action="{{ route('eleves.print') }}" class="filters">
        <div>
            <label for="classe_id">Classe</label>
            <select name="classe_id" id="classe_id">
                <option value="">Toutes les classes</option>
                @foreach($classes as $classe)
                    <option value="{{ $classe->id }}" {{ (string) request('classe_id') === (string) $classe->id ? 'selected' : '' }}>
                        {{ $classe->nom }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="statut">Statut</label>
            <select name="statut" id="statut">
                <option value="">Tous les statuts</option>
                <option value="actif" {{ request('statut') === 'actif' ? 'selected' : '' }}>Actifs</option>
                <option value="inactif" {{ request('statut') === 'inactif' ? 'selected' : '' }}>Inactifs</option>
            </select>
        </div>
        <div>
            <label for="annee_scolaire_id">Année scolaire</label>
            <select name="annee_scolaire_id" id="annee_scolaire_id">
                <option value="">Année active</option>
                @foreach($anneesScolarires as $annee)
                    <option value="{{ $annee->id }}" {{ (string) request('annee_scolaire_id') === (string) $annee->id ? 'selected' : '' }}>
                        {{ $annee->nom }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <button type="submit" class="btn">Filtrer</button>
        </div>
    </form>
</div>

<div class="sheet">
    <div class="header-row">
        <div class="header-logo">
            @if(!empty($schoolInfo['logo_url']))
                <img src="{{ $schoolInfo['logo_url'] }}" alt="Logo">
            @endif
        </div>
        <div class="header-center">
            <h1 class="school-name">{{ $schoolInfo['school_name'] ?? 'École' }}</h1>
            @if(!empty($schoolInfo['school_slogan']))
                <p class="school-slogan">{{ $schoolInfo['school_slogan'] }}</p>
            @endif
            @if(!empty($schoolInfo['year_name']))
                <p class="school-year">Année scolaire : {{ $schoolInfo['year_name'] }}</p>
            @endif
        </div>
        <div class="header-right">
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
    <hr class="header-separator">

    <h2 class="doc-title">
        Liste des élèves
        @if($classeSelectionnee)
            — {{ $classeSelectionnee->nom }}
        @endif
    </h2>
    <p class="doc-meta">
        Généré le {{ now()->format('d/m/Y à H:i') }}
        @if(request('statut') === 'actif') — Élèves actifs
        @elseif(request('statut') === 'inactif') — Élèves inactifs
        @endif
    </p>

    <div class="summary">
        <span>Total : <strong>{{ $eleves->count() }}</strong></span>
        <span>Actifs : <strong>{{ $eleves->where('actif', true)->count() }}</strong></span>
        <span>Inactifs : <strong>{{ $eleves->where('actif', false)->count() }}</strong></span>
        <span>Classes : <strong>{{ $elevesParClasse->count() }}</strong></span>
    </div>

    @forelse($elevesParClasse as $nomClasse => $elevesClasse)
        <div class="class-section">
            @unless($classeSelectionnee)
                <div class="class-banner">
                    <span>Classe : {{ $nomClasse }}</span>
                    <span class="effectif">Effectif : {{ $elevesClasse->count() }} élève(s)</span>
                </div>
            @endunless

            <table class="students-table">
                <thead>
                    <tr>
                        <th style="width: 10%;">N°</th>
                        <th style="width: 22%;">Matricule</th>
                        <th style="width: 38%;">Prénoms</th>
                        <th style="width: 30%;">Nom</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($elevesClasse as $index => $eleve)
                        <tr>
                            <td class="col-num">{{ $index + 1 }}</td>
                            <td class="col-mat">{{ $eleve->numero_etudiant ?? '—' }}</td>
                            <td>{{ $eleve->utilisateur->prenom ?? '—' }}</td>
                            <td class="col-nom">{{ $eleve->utilisateur->nom ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @empty
        <p class="empty">Aucun élève trouvé pour ces filtres.</p>
    @endforelse

    <div class="footer">
        <div>
            @if(!empty($schoolInfo['school_address']))
                {{ $schoolInfo['school_address'] }}
            @endif
            @if(!empty($schoolInfo['school_phone']))
                — Tél. {{ $schoolInfo['school_phone'] }}
            @endif
        </div>
        <div>{{ $schoolInfo['school_name'] ?? 'École' }}</div>
    </div>
</div>
</body>
</html>
