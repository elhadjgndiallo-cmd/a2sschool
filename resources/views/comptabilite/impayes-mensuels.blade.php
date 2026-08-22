@extends('layouts.app')

@section('title', 'Impayés mensuels - Comptabilité')

@section('content')
@php
    $classeSelectionnee = $classeId ? $classes->firstWhere('id', $classeId) : null;
    $docInfo = \App\Helpers\SchoolHelper::getDocumentInfo();
@endphp
<div class="container-fluid impayes-mensuels-page">
    {{-- En-tête visible uniquement à l'impression --}}
    <div class="print-only print-document-header mb-3">
        <div class="print-header-row">
            <div class="print-header-logo">
                @if($schoolHeader['logo'] ?? null)
                    <img src="{{ $schoolHeader['logo'] }}" alt="Logo" class="print-logo-img">
                @endif
            </div>
            <div class="print-header-center text-center">
                <h2 class="print-school-name mb-1">{{ $schoolHeader['title'] ?? 'École' }}</h2>
                @if(!empty($schoolHeader['subtitle']))
                    <p class="print-school-slogan mb-1">{{ $schoolHeader['subtitle'] }}</p>
                @endif
                @if(!empty($schoolHeader['year']))
                    <p class="print-school-year mb-0">Année scolaire : {{ $schoolHeader['year'] }}</p>
                @endif
            </div>
            <div class="print-header-right text-end">
                @if(!empty($schoolHeader['address']))
                    <p class="mb-1">{{ $schoolHeader['address'] }}</p>
                @endif
                @if(!empty($docInfo['school_phone']))
                    <p class="mb-0">Tél : {{ $docInfo['school_phone'] }}</p>
                @endif
            </div>
        </div>
        <hr class="print-header-separator">
        <h3 class="text-center mb-0 fw-bold print-report-title">
            Liste des impayés mensuels
            @if($classeSelectionnee)
                — {{ $classeSelectionnee->nom }}
            @endif
        </h3>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card no-print">
                <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
                    <h3 class="card-title mb-0 screen-only-title">
                        <i class="fas fa-user-times text-danger me-2"></i>
                        <span class="d-none d-sm-inline">Impayés mensuels par classe</span>
                        <span class="d-sm-none">Impayés mensuels</span>
                    </h3>
                    <div class="d-flex gap-2">
                        <a href="{{ route('comptabilite.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>
                            <span class="d-none d-sm-inline">Retour</span>
                        </a>
                        @if($aRecherche)
                            <button type="button" class="btn btn-success" onclick="window.print()">
                                <i class="fas fa-print me-1"></i>
                                <span class="d-none d-sm-inline">Imprimer</span>
                                <span class="d-sm-none">Print</span>
                            </button>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('comptabilite.impayes-mensuels') }}" id="form-impayes" class="mb-0">
                        <div class="row g-2">
                            <div class="col-12 col-sm-6 col-md-3">
                                <select class="form-control" id="annee_scolaire_id" name="annee_scolaire_id" onchange="this.form.submit()" title="Année scolaire">
                                    @foreach($anneesScolaires as $annee)
                                        <option value="{{ $annee->id }}" {{ (int) $anneeScolaire->id === (int) $annee->id ? 'selected' : '' }}>
                                            {{ $annee->nom }}{{ $annee->active ? ' (active)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 col-md-3">
                                <select class="form-control @error('classe_id') is-invalid @enderror" id="classe_id" name="classe_id" required title="Classe">
                                    <option value="">Sélectionner une classe…</option>
                                    @foreach($classes as $classe)
                                        <option value="{{ $classe->id }}" {{ (int) ($classeId ?? 0) === (int) $classe->id ? 'selected' : '' }}>
                                            {{ $classe->nom }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('classe_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 col-sm-6 col-md-3">
                                <select class="form-control" id="type_frais" name="type_frais" title="Type de frais">
                                    @foreach($typesFrais as $value => $label)
                                        <option value="{{ $value }}" {{ ($typeFrais ?? 'scolarite') === $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 col-md-3">
                                <div class="d-flex gap-1">
                                    <button type="submit" name="filtrer" value="1" class="btn btn-primary flex-fill">
                                        <i class="fas fa-search"></i>
                                        <span class="d-none d-sm-inline">Filtrer</span>
                                    </button>
                                    <a href="{{ route('comptabilite.impayes-mensuels') }}" class="btn btn-outline-secondary" title="Réinitialiser">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="mt-2">
                            <button type="button" class="btn btn-sm btn-outline-info" onclick="toggleMoisFilters()">
                                <i class="fas fa-calendar-alt"></i> Mois à vérifier
                                @if(!empty($moisSelectionnes))
                                    <span class="badge bg-info text-dark">{{ count($moisSelectionnes) }}</span>
                                @endif
                            </button>
                        </div>

                        <div class="mt-2" id="moisFilters" style="{{ !empty($moisSelectionnes) || $errors->has('mois') ? '' : 'display: none;' }}">
                            <div class="d-flex flex-wrap gap-1 mb-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-tout">Tout</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-rien">Rien</button>
                                <button type="button" class="btn btn-sm btn-outline-info" data-mois-count="1">1 mois</button>
                                <button type="button" class="btn btn-sm btn-outline-info" data-mois-count="2">2 mois</button>
                                <button type="button" class="btn btn-sm btn-outline-info" data-mois-count="3">3 mois</button>
                            </div>
                            <div class="row g-1">
                                @foreach($moisOptions as $mois)
                                    <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                        <div class="form-check">
                                            <input class="form-check-input mois-checkbox"
                                                   type="checkbox"
                                                   name="mois[]"
                                                   value="{{ $mois['value'] }}"
                                                   id="mois_{{ $mois['value'] }}"
                                                   data-mois-index="{{ $loop->index }}"
                                                   {{ in_array($mois['value'], $moisSelectionnes ?? [], true) ? 'checked' : '' }}>
                                            <label class="form-check-label small" for="mois_{{ $mois['value'] }}">
                                                {{ $mois['label'] }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @error('mois')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                    </form>

                    @if($aRecherche)
                        <div class="mt-3 mb-0 d-flex flex-wrap gap-3 align-items-center">
                            <small class="text-muted">
                                <i class="fas fa-users me-1"></i>
                                <strong class="text-danger">{{ $resultats->count() }}</strong> élève(s) concerné(s)
                                @if($classeSelectionnee)
                                    — {{ $classeSelectionnee->nom }}
                                @endif
                            </small>
                            @if($resultats->count() > 0)
                                <small class="text-muted">
                                    <i class="fas fa-coins text-danger me-1"></i>
                                    Total dû :
                                    <strong class="text-danger">{{ number_format($resultats->sum('total_du'), 0, ',', ' ') }} GNF</strong>
                                </small>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($aRecherche)
        <div class="row print-results mt-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-body p-0">
                        @if($resultats->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered impayes-table mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-center col-num">N°</th>
                                            <th>Matricule</th>
                                            <th>Prénom</th>
                                            <th>Nom</th>
                                            <th>Mois impayés</th>
                                            <th class="text-center col-nb-mois">NB Mois</th>
                                            <th class="text-end col-montant">Montant</th>
                                            <th class="no-print text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($resultats as $index => $ligne)
                                            @php
                                                $eleve = $ligne['eleve'];
                                                $utilisateur = $eleve->utilisateur;
                                                $moisImpayesEleve = collect($ligne['mois_impayes'])->map(function ($moisImpaye) {
                                                    return \Carbon\Carbon::parse($moisImpaye['mois'])->format('Y-m');
                                                })->values()->all();
                                            @endphp
                                            <tr>
                                                <td class="text-center">{{ $index + 1 }}</td>
                                                <td>{{ $eleve->numero_etudiant ?? '—' }}</td>
                                                <td>{{ $utilisateur->prenom ?? '—' }}</td>
                                                <td>{{ $utilisateur->nom ?? '—' }}</td>
                                                <td class="col-mois-impayes">
                                                    {{ collect($ligne['mois_impayes'])->pluck('libelle_mois')->implode(', ') }}
                                                </td>
                                                <td class="text-center">{{ $ligne['nombre_mois'] }}</td>
                                                <td class="text-end fw-semibold">
                                                    {{ number_format($ligne['total_du'], 0, ',', ' ') }} GNF
                                                </td>
                                                <td class="no-print text-center">
                                                    @if(auth()->user()->hasPermission('paiements.create'))
                                                        <a href="{{ route('recus-rappel.depuis-impayes', [
                                                                'eleve_id' => $eleve->id,
                                                                'type_frais' => $typeFrais,
                                                                'mois' => $moisImpayesEleve,
                                                            ]) }}"
                                                           class="btn btn-sm btn-outline-warning"
                                                           title="Reçu de rappel de paiement"
                                                           target="_blank">
                                                            <i class="fas fa-bell"></i>
                                                        </a>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-total-row">
                                            <th colspan="6" class="text-end">Total général</th>
                                            <th class="text-end fw-bold">
                                                {{ number_format($resultats->sum('total_du'), 0, ',', ' ') }} GNF
                                            </th>
                                            <th class="no-print"></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @else
                            <div class="p-4 text-center text-muted">
                                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                <p class="mb-0">Aucun élève impayé pour les mois sélectionnés dans cette classe.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
function toggleMoisFilters() {
    const el = document.getElementById('moisFilters');
    if (!el) return;
    const isHidden = el.style.display === 'none' || window.getComputedStyle(el).display === 'none';
    el.style.display = isHidden ? 'block' : 'none';
}

document.addEventListener('DOMContentLoaded', function () {
    const checkboxes = Array.from(document.querySelectorAll('.mois-checkbox'));
    const moisFilters = document.getElementById('moisFilters');

    // Afficher les mois si déjà sélectionnés ou en cas d'erreur
    if (moisFilters && (checkboxes.some(cb => cb.checked) || {{ $errors->has('mois') ? 'true' : 'false' }})) {
        moisFilters.style.display = 'block';
    }

    document.getElementById('btn-tout')?.addEventListener('click', function () {
        checkboxes.forEach(cb => cb.checked = true);
    });

    document.getElementById('btn-rien')?.addEventListener('click', function () {
        checkboxes.forEach(cb => cb.checked = false);
    });

    document.querySelectorAll('[data-mois-count]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const count = parseInt(this.dataset.moisCount, 10);
            checkboxes.forEach(cb => cb.checked = false);

            const today = new Date();
            const currentYm = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0');

            const eligibles = checkboxes
                .filter(cb => cb.value <= currentYm)
                .sort((a, b) => a.value.localeCompare(b.value));

            eligibles.slice(-count).forEach(cb => cb.checked = true);
        });
    });
});
</script>
@endpush

@push('styles')
<style>
.print-only {
    display: none;
}

.impayes-table {
    border-collapse: collapse;
    width: 100%;
}

.impayes-table th,
.impayes-table td {
    border: 1px solid #333 !important;
    vertical-align: middle;
    padding: 0.5rem 0.6rem;
}

.impayes-table thead th {
    background-color: #f0f0f0;
    font-weight: 700;
    text-align: center;
}

.impayes-table thead th.col-montant,
.impayes-table tbody td.text-end {
    text-align: right;
}

.impayes-table thead th:first-child,
.impayes-table tbody td.col-num,
.impayes-table tbody td.text-center {
    text-align: center;
}

.impayes-table thead th:nth-child(2),
.impayes-table thead th:nth-child(3),
.impayes-table thead th:nth-child(4),
.impayes-table thead th:nth-child(5) {
    text-align: left;
}

.impayes-table .col-num {
    width: 40px;
}

.impayes-table .col-nb-mois {
    width: 70px;
}

.impayes-table .col-montant {
    width: 160px;
    min-width: 160px;
    white-space: nowrap;
}

.impayes-table .col-mois-impayes {
    white-space: normal;
    min-width: 120px;
}

.impayes-table .table-total-row th {
    background-color: #f8f8f8;
    font-weight: 700;
}

@media print {
    @page {
        size: A4 portrait;
        margin: 12mm 10mm;
    }

    body {
        background: #fff !important;
        color: #000 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .top-navbar,
    .sidebar,
    .sidebar-overlay,
    footer,
    .no-print,
    .btn,
    form,
    .screen-only-title {
        display: none !important;
    }

    .print-only {
        display: block !important;
    }

    .main-content {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
    }

    .impayes-mensuels-page.container-fluid {
        padding: 0 !important;
        max-width: 100% !important;
    }

    .print-results .card {
        border: none !important;
        box-shadow: none !important;
    }

    .print-results .card-body {
        padding: 0 !important;
    }

    .print-header-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .print-header-logo {
        flex: 0 0 90px;
        text-align: left;
    }

    .print-logo-img {
        max-height: 70px;
        max-width: 90px;
        object-fit: contain;
    }

    .print-header-center {
        flex: 1 1 auto;
        padding: 0 8px;
    }

    .print-school-name {
        font-size: 16px;
        font-weight: bold;
        margin: 0;
        color: #000 !important;
    }

    .print-school-slogan {
        font-size: 10px;
        font-style: italic;
        margin: 0;
        color: #333 !important;
    }

    .print-school-year {
        font-size: 10px;
        font-weight: 600;
        color: #000 !important;
    }

    .print-header-right {
        flex: 0 0 30%;
        font-size: 10px;
        line-height: 1.35;
        color: #000 !important;
    }

    .print-header-right p {
        margin: 0 0 0.2rem 0;
    }

    .print-header-separator {
        border-top: 2px solid #333;
        margin: 0.75rem 0;
    }

    .print-report-title {
        font-size: 13px;
        color: #000 !important;
    }

    .impayes-table {
        font-size: 10px;
    }

    .impayes-table th,
    .impayes-table td {
        padding: 0.35rem 0.4rem !important;
        border: 1px solid #000 !important;
    }

    .impayes-table thead th {
        background: #e9e9e9 !important;
        color: #000 !important;
    }

    .table {
        font-size: 10px;
        width: 100% !important;
    }

    .table th,
    .table td {
        padding: 0.35rem 0.4rem !important;
        vertical-align: middle !important;
        border-color: #000 !important;
    }

    .table thead th {
        background: #e9e9e9 !important;
        color: #000 !important;
    }

    .col-mois-impayes {
        white-space: normal;
    }

    .text-danger {
        color: #000 !important;
    }

    .table-responsive {
        overflow: visible !important;
    }

    tr {
        break-inside: avoid;
    }

    .print-document-header {
        break-inside: avoid;
    }
}
</style>
@endpush
@endsection
