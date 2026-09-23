<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulletin de Salaire - {{ $salaire->enseignant->utilisateur->nom }} {{ $salaire->enseignant->utilisateur->prenom }}</title>
    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 14px;
            line-height: 1.4;
            color: #111;
            background: #e9ecef;
        }

        .toolbar {
            background: #fff;
            border-bottom: 1px solid #dee2e6;
            padding: 12px 16px;
            position: sticky;
            top: 0;
            z-index: 20;
            display: flex;
            gap: 8px;
            justify-content: center;
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

        .sheet {
            width: 210mm;
            min-height: 297mm;
            margin: 16px auto 32px;
            background: #fff;
            padding: 12mm 14mm 16mm;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.12);
        }

        .school-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 8px;
        }

        .logo-slot {
            width: 80px;
            flex-shrink: 0;
            text-align: center;
        }

        .logo-slot img {
            max-width: 70px;
            max-height: 70px;
            object-fit: contain;
        }

        .school-center {
            flex: 1;
            text-align: center;
        }

        .school-name {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .school-slogan {
            margin: 4px 0 0;
            font-size: 13px;
            font-style: italic;
            color: #555;
        }

        .header-line {
            border: 0;
            border-top: 2px solid #222;
            margin: 8px 0 12px;
        }

        .doc-title {
            text-align: center;
            margin: 0 0 4px;
            font-size: 18px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .doc-meta {
            text-align: center;
            margin: 0 0 16px;
            font-size: 13px;
            color: #555;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 10px;
            margin-bottom: 16px;
        }

        .info-box {
            border: 1px solid #222;
            padding: 10px 12px;
        }

        .info-box h3 {
            margin: 0 0 8px;
            font-size: 13px;
            text-transform: uppercase;
            border-bottom: 1px solid #ccc;
            padding-bottom: 4px;
        }

        .info-box p {
            margin: 4px 0;
            font-size: 14px;
        }

        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .status-calculé { background: #ffc107; color: #000; }
        .status-validé { background: #17a2b8; color: #fff; }
        .status-payé { background: #198754; color: #fff; }
        .status-annulé { background: #dc3545; color: #fff; }

        table.bulletin {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            font-size: 14px;
        }

        .bulletin th,
        .bulletin td {
            border: 1px solid #222;
            padding: 10px 8px;
            vertical-align: middle;
        }

        .bulletin th {
            background: #f0f0f0;
            text-align: center;
            font-weight: 700;
        }

        .bulletin td.num,
        .bulletin th.num {
            text-align: right;
        }

        .bulletin td.center,
        .bulletin th.center {
            text-align: center;
        }

        .bulletin .retenue {
            color: #b02a37;
        }

        .bulletin tr.total td {
            background: #f5f5f5;
            font-weight: 700;
        }

        .bulletin tr.net td {
            background: #222;
            color: #fff;
            font-weight: 700;
            font-size: 15px;
        }

        .bulletin tr.net .retenue {
            color: #fff;
        }

        .net-banner {
            border: 2px solid #222;
            padding: 12px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
            background: #f8f8f8;
        }

        .net-banner .label {
            font-weight: 700;
            text-transform: uppercase;
            font-size: 14px;
        }

        .net-banner .montant {
            font-size: 20px;
            font-weight: 700;
        }

        .observations {
            border: 1px solid #222;
            padding: 10px 12px;
            margin-bottom: 22px;
        }

        .observations h3 {
            margin: 0 0 6px;
            font-size: 13px;
            text-transform: uppercase;
        }

        .observations p {
            margin: 0;
            font-style: italic;
        }

        .signatures {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            margin: 28px 0 24px;
        }

        .signature-box {
            flex: 1;
            text-align: center;
        }

        .signature-line {
            border-bottom: 1px solid #222;
            height: 48px;
            margin-bottom: 6px;
        }

        .signature-label {
            font-size: 13px;
        }

        .footer {
            border-top: 1px solid #222;
            padding-top: 8px;
            text-align: center;
            font-size: 13px;
            color: #333;
        }

        .footer p { margin: 3px 0; }

        @media print {
            @page {
                size: A4 portrait;
                margin: 10mm 12mm 12mm;
            }

            html, body {
                background: #fff !important;
                margin: 0;
                padding: 0;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .toolbar { display: none !important; }

            .sheet {
                width: auto;
                min-height: 0;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }

            .bulletin th,
            .bulletin tr.total td,
            .bulletin tr.net td,
            .status-badge,
            .net-banner {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button type="button" class="btn btn-success" onclick="window.print()">Imprimer</button>
        <a href="{{ route('salaires.show', $salaire) }}" class="btn btn-secondary">Retour</a>
    </div>

    <div class="sheet">
        <div class="school-header">
            <div class="logo-slot">
                @if($etablissement && $etablissement->logo)
                    <img src="{{ asset('storage/' . $etablissement->logo) }}" alt="Logo">
                @endif
            </div>
            <div class="school-center">
                <p class="school-name">{{ $etablissement->nom ?? 'Établissement scolaire' }}</p>
                @if($etablissement && $etablissement->slogan)
                    <p class="school-slogan">{{ $etablissement->slogan }}</p>
                @endif
            </div>
            <div class="logo-slot">
                @if($etablissement && $etablissement->logo)
                    <img src="{{ asset('storage/' . $etablissement->logo) }}" alt="Logo">
                @endif
            </div>
        </div>
        <hr class="header-line">

        <h1 class="doc-title">Bulletin de salaire</h1>
        <p class="doc-meta">
            Période du {{ $salaire->periode_debut->format('d/m/Y') }}
            au {{ $salaire->periode_fin->format('d/m/Y') }}
        </p>

        <div class="info-grid">
            <div class="info-box">
                <h3>Enseignant</h3>
                <p><strong>{{ $salaire->enseignant->utilisateur->nom }} {{ $salaire->enseignant->utilisateur->prenom }}</strong></p>
                @if($salaire->enseignant->specialite)
                    <p>{{ $salaire->enseignant->specialite }}</p>
                @endif
                <p>{{ $salaire->enseignant->utilisateur->telephone ?? '—' }}</p>
            </div>
            <div class="info-box">
                <h3>Période</h3>
                <p>Du {{ $salaire->periode_debut->format('d/m/Y') }}</p>
                <p>Au {{ $salaire->periode_fin->format('d/m/Y') }}</p>
                <p>{{ $salaire->periode_debut->diffInDays($salaire->periode_fin) + 1 }} jours</p>
            </div>
            <div class="info-box">
                <h3>Statut</h3>
                <p><span class="status-badge status-{{ $salaire->statut }}">{{ ucfirst($salaire->statut) }}</span></p>
                <p>Calcul : {{ $salaire->date_calcul ? $salaire->date_calcul->format('d/m/Y') : '—' }}</p>
                @if($salaire->date_paiement)
                    <p>Paiement : {{ $salaire->date_paiement->format('d/m/Y') }}</p>
                @elseif($salaire->date_validation)
                    <p>Validation : {{ $salaire->date_validation->format('d/m/Y') }}</p>
                @endif
            </div>
        </div>

        <table class="bulletin">
            <thead>
                <tr>
                    <th>Libellé</th>
                    <th class="center">Base</th>
                    <th class="center">Taux</th>
                    <th class="num">Gains</th>
                    <th class="num">Retenues</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Salaire de base</td>
                    <td class="center">1 mois</td>
                    <td class="center">{{ number_format($salaire->salaire_base, 0, ',', ' ') }} GNF</td>
                    <td class="num">{{ number_format($salaire->salaire_base, 0, ',', ' ') }}</td>
                    <td class="num">—</td>
                </tr>
                <tr>
                    <td>Heures travaillées</td>
                    <td class="center">{{ $salaire->nombre_heures }} h</td>
                    <td class="center">{{ number_format($salaire->taux_horaire, 0, ',', ' ') }} GNF/h</td>
                    <td class="num">{{ number_format($salaire->nombre_heures * $salaire->taux_horaire, 0, ',', ' ') }}</td>
                    <td class="num">—</td>
                </tr>
                <tr>
                    <td>Prime d'ancienneté</td>
                    <td class="center">—</td>
                    <td class="center">—</td>
                    <td class="num">{{ number_format($salaire->prime_anciennete, 0, ',', ' ') }}</td>
                    <td class="num">—</td>
                </tr>
                <tr>
                    <td>Prime de performance</td>
                    <td class="center">—</td>
                    <td class="center">—</td>
                    <td class="num">{{ number_format($salaire->prime_performance, 0, ',', ' ') }}</td>
                    <td class="num">—</td>
                </tr>
                <tr>
                    <td>Heures supplémentaires</td>
                    <td class="center">—</td>
                    <td class="center">—</td>
                    <td class="num">{{ number_format($salaire->prime_heures_supplementaires, 0, ',', ' ') }}</td>
                    <td class="num">—</td>
                </tr>
                <tr>
                    <td>Déduction absences</td>
                    <td class="center">—</td>
                    <td class="center">—</td>
                    <td class="num">—</td>
                    <td class="num retenue">{{ number_format($salaire->deduction_absences, 0, ',', ' ') }}</td>
                </tr>
                <tr>
                    <td>Autres déductions</td>
                    <td class="center">—</td>
                    <td class="center">—</td>
                    <td class="num">—</td>
                    <td class="num retenue">{{ number_format($salaire->deduction_autres, 0, ',', ' ') }}</td>
                </tr>
                @if((float) $salaire->deduction_avances > 0)
                <tr>
                    <td>Avances sur salaire</td>
                    <td class="center">—</td>
                    <td class="center">—</td>
                    <td class="num">—</td>
                    <td class="num retenue">{{ number_format($salaire->deduction_avances, 0, ',', ' ') }}</td>
                </tr>
                @endif
                <tr class="total">
                    <td colspan="3">Total gains</td>
                    <td class="num">{{ number_format($salaire->salaire_brut, 0, ',', ' ') }}</td>
                    <td class="num">—</td>
                </tr>
                <tr class="total">
                    <td colspan="3">Total retenues</td>
                    <td class="num">—</td>
                    <td class="num retenue">{{ number_format($salaire->deduction_absences + $salaire->deduction_autres + (float) $salaire->deduction_avances, 0, ',', ' ') }}</td>
                </tr>
                <tr class="net">
                    <td colspan="3">Net à payer</td>
                    <td class="num">{{ number_format($salaire->salaire_net, 0, ',', ' ') }} GNF</td>
                    <td class="num">—</td>
                </tr>
            </tbody>
        </table>

        <div class="net-banner">
            <span class="label">Net à payer</span>
            <span class="montant">{{ number_format($salaire->salaire_net, 0, ',', ' ') }} GNF</span>
        </div>

        @if($salaire->observations)
        <div class="observations">
            <h3>Observations</h3>
            <p>{{ $salaire->observations }}</p>
        </div>
        @endif

        <div class="signatures">
            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="signature-label">L'enseignant</div>
            </div>
            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="signature-label">Le comptable</div>
            </div>
            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="signature-label">Le directeur</div>
            </div>
        </div>

        <div class="footer">
            @if($etablissement)
            <p>
                @if($etablissement->adresse)
                    {{ $etablissement->adresse }}
                @endif
                @if($etablissement->adresse && $etablissement->telephone)
                    &nbsp;|&nbsp;
                @endif
                @if($etablissement->telephone)
                    Tél. {{ $etablissement->telephone }}
                @endif
            </p>
            @endif
            <p>Document généré le {{ now()->format('d/m/Y à H:i') }} — Ce document fait foi de paiement</p>
        </div>
    </div>
</body>
</html>
