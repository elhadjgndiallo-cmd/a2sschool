<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Emploi du temps - {{ $classe->nom }}</title>
    <style>
        @page { size: A4 landscape; margin: 12mm 10mm; }
        body {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #111;
            background: #fff;
        }
        .header {
            margin-bottom: 12px;
            border-bottom: 2px solid #222;
            padding-bottom: 8px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
        }
        .header-table td {
            border: none !important;
            vertical-align: middle;
            padding: 0;
        }
        .header-logo {
            width: 80px;
            text-align: center;
        }
        .header-logo img {
            max-width: 70px;
            max-height: 70px;
        }
        .header-center {
            text-align: center;
            padding: 0 10px;
        }
        .school-name { font-size: 16px; font-weight: bold; margin: 0; }
        .school-slogan { font-size: 10px; font-style: italic; color: #555; margin: 2px 0 0 0; }
        .doc-title { font-size: 14px; font-weight: bold; margin: 8px 0 2px 0; text-transform: uppercase; }
        .meta { font-size: 10px; color: #444; margin: 0; }
        table.edt {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-top: 8px;
        }
        table.edt th,
        table.edt td {
            border: 1px solid #222;
            padding: 6px 4px;
            vertical-align: top;
            word-wrap: break-word;
        }
        table.edt th {
            background: #2c3e50;
            color: #fff;
            text-align: center;
            font-size: 11px;
            font-weight: bold;
        }
        table.edt td.heure {
            background: #f0f0f0;
            font-weight: bold;
            text-align: center;
            width: 90px;
            font-size: 10px;
        }
        .cell-cours {
            font-size: 9px;
            line-height: 1.25;
        }
        .matiere { font-weight: bold; display: block; margin-bottom: 2px; }
        .enseignant { color: #333; display: block; }
        .salle { color: #555; font-size: 8px; display: block; margin-top: 2px; }
        .vide { color: #999; text-align: center; }
        .recre {
            text-align: center;
            font-style: italic;
            color: #555;
            background: #eee;
        }
        .footer {
            margin-top: 10px;
            font-size: 9px;
            color: #666;
            text-align: right;
        }
        .empty {
            text-align: center;
            padding: 30px;
            color: #666;
            border: 1px solid #ccc;
            margin-top: 20px;
        }
    </style>
</head>
<body>
@php
    $schoolInfo = \App\Helpers\SchoolHelper::getSchoolInfo();
    $schoolName = $schoolInfo->nom ?? config('app.name', 'École');
    $schoolSlogan = $schoolInfo->slogan ?? '';
    $isPrimaire = $classe->isPrimaire();
    $jours = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
    $dureeSecondaire = (int) ($dureeDefautSecondaire ?? 120);
@endphp

<div class="header">
    <table class="header-table">
        <tr>
            <td class="header-logo">
                @if(!empty($logoDataUri))
                    <img src="{{ $logoDataUri }}" alt="Logo">
                @endif
            </td>
            <td class="header-center">
                <p class="school-name">{{ $schoolName }}</p>
                @if($schoolSlogan)
                    <p class="school-slogan">"{{ $schoolSlogan }}"</p>
                @endif
                <p class="doc-title">Emploi du temps — {{ $classe->nom }}</p>
                <p class="meta">
                    {{ $isPrimaire ? 'Primaire' : 'Secondaire' }}
                    @if(!empty($anneeScolaireActive))
                        · Année scolaire {{ $anneeScolaireActive->nom }}
                    @endif
                    · Généré le {{ now()->format('d/m/Y à H:i') }}
                </p>
            </td>
            <td class="header-logo">
                @if(!empty($logoDataUri))
                    <img src="{{ $logoDataUri }}" alt="Logo">
                @endif
            </td>
        </tr>
    </table>
</div>

@if(($emploisTemps ?? collect())->isEmpty())
    <div class="empty">Aucun créneau enregistré pour cette classe.</div>
@else
    <table class="edt">
        <thead>
            <tr>
                <th>Heure</th>
                @foreach($jours as $jour)
                    <th>{{ ucfirst($jour) }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($plages as $plage)
                @php
                    $isRecre = !empty($plage['recre']);
                @endphp
                <tr>
                    <td class="heure {{ $isRecre ? 'recre' : '' }}">
                        @if($isRecre)
                            {{ $plage['label'] ?? 'RÉCRÉATION' }}<br>
                            <span style="font-weight:normal;">{{ $plage['debut'] }} – {{ $plage['fin'] }}</span>
                        @else
                            {{ $plage['debut'] }} – {{ $plage['fin'] }}
                        @endif
                    </td>
                    @foreach($jours as $jour)
                        @if($isRecre)
                            <td class="recre">{{ $plage['label'] ?? 'RÉCRÉATION' }}</td>
                        @else
                            @php
                                $creneaux = $emploisTemps->where('jour_semaine', $jour)->filter(function ($e) use ($plage, $isPrimaire) {
                                    $d = \Carbon\Carbon::parse($e->heure_debut)->format('H:i');
                                    $f = \Carbon\Carbon::parse($e->heure_fin)->format('H:i');
                                    if ($isPrimaire) {
                                        return $d === ($plage['debut'] ?? '') && $f === ($plage['fin'] ?? '');
                                    }
                                    return $d === ($plage['debut'] ?? '');
                                });
                            @endphp
                            <td>
                                @forelse($creneaux as $creneau)
                                    <div class="cell-cours">
                                        <span class="matiere">{{ $creneau->matiere->nom ?? '—' }}</span>
                                        <span class="enseignant">
                                            {{ strtoupper($creneau->enseignant->utilisateur->nom ?? '') }}
                                            {{ $creneau->enseignant->utilisateur->prenom ?? '' }}
                                        </span>
                                        @if(!empty($creneau->salle))
                                            <span class="salle">Salle : {{ $creneau->salle }}</span>
                                        @endif
                                    </div>
                                @empty
                                    <div class="vide">—</div>
                                @endforelse
                            </td>
                        @endif
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

<div class="footer">
    Document généré automatiquement — {{ $schoolName }}
</div>
</body>
</html>
