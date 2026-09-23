@extends('layouts.app')

@section('title', 'Tableau des tarifs par classe')

@php
    $gnf = fn ($montant) => ((float) $montant > 0)
        ? number_format($montant, 0, ',', ' ') . ' GNF'
        : '—';
    $nombreTranches = $tarifs->max('nombre_tranches') ?: 9;
@endphp

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-table me-2"></i>
        Tableau des tarifs
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('tarifs.index') }}" class="btn btn-sm btn-primary">
            <i class="fas fa-cog me-1"></i>
            Gérer les tarifs
        </a>
    </div>
</div>

<form method="GET" action="{{ route('tarifs.tableau') }}" class="mb-3">
    <div class="row g-2">
        <div class="col-12 col-sm-6 col-md-4">
            <select name="annee_scolaire" class="form-select" title="Année scolaire">
                <option value="">Toutes les années</option>
                @foreach($anneesScolaires as $annee)
                    <option value="{{ $annee }}" {{ (string) $anneeScolaire === (string) $annee ? 'selected' : '' }}>
                        {{ $annee }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="d-flex gap-1">
                <button type="submit" class="btn btn-primary flex-fill">
                    <i class="fas fa-search me-1"></i>
                    Filtrer
                </button>
                <a href="{{ route('tarifs.tableau') }}" class="btn btn-outline-secondary" title="Réinitialiser">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </div>
    </div>
</form>

@if($tarifs->count() > 0)
<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small">Classes</div>
                <div class="fs-4 fw-bold">{{ $tarifs->count() }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small">Tarifs actifs</div>
                <div class="fs-4 fw-bold">{{ $tarifs->where('actif', true)->count() }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small">Total mensuel</div>
                <div class="fs-5 fw-bold">{{ number_format($tarifs->sum('total_mensuel'), 0, ',', ' ') }} GNF</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small">Total annuel</div>
                <div class="fs-5 fw-bold">{{ number_format($tarifs->sum('total_annuel'), 0, ',', ' ') }} GNF</div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Liste des tarifs{{ $anneeScolaire ? ' — ' . $anneeScolaire : '' }}</h5>
        <span class="badge bg-primary">{{ $tarifs->count() }} classes</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th rowspan="2" class="text-center align-middle">Classe</th>
                        <th colspan="3" class="text-center">Frais uniques</th>
                        <th colspan="3" class="text-center">Frais mensuels</th>
                        <th rowspan="2" class="text-end align-middle">Total mensuel</th>
                        <th rowspan="2" class="text-end align-middle">Total annuel</th>
                        <th rowspan="2" class="text-center align-middle">Statut</th>
                    </tr>
                    <tr>
                        <th class="text-end">Inscription</th>
                        <th class="text-end">Uniforme</th>
                        <th class="text-end">Livres</th>
                        <th class="text-end">Scolarité</th>
                        <th class="text-end">Cantine</th>
                        <th class="text-end">Transport</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tarifs as $tarif)
                    <tr class="table-row-clickable" data-href="{{ route('tarifs.show', $tarif) }}" role="button" tabindex="0">
                        <td>
                            <strong>{{ $tarif->classe->nom ?? '—' }}</strong>
                            <div class="small text-muted">{{ $tarif->annee_scolaire }}</div>
                        </td>
                        <td class="text-end">{{ $gnf($tarif->frais_inscription) }}</td>
                        <td class="text-end">{{ $gnf($tarif->frais_uniforme) }}</td>
                        <td class="text-end">{{ $gnf($tarif->frais_livres) }}</td>
                        <td class="text-end">{{ $gnf($tarif->frais_scolarite_mensuel) }}</td>
                        <td class="text-end">{{ $gnf($tarif->frais_cantine_mensuel) }}</td>
                        <td class="text-end">{{ $gnf($tarif->frais_transport_mensuel) }}</td>
                        <td class="text-end fw-bold">{{ $gnf($tarif->total_mensuel) }}</td>
                        <td class="text-end fw-bold">{{ $gnf($tarif->total_annuel) }}</td>
                        <td class="text-center">
                            @if($tarif->actif)
                                <span class="badge bg-success">Actif</span>
                            @else
                                <span class="badge bg-secondary">Inactif</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <th colspan="7" class="text-end">Totaux</th>
                        <th class="text-end">{{ number_format($tarifs->sum('total_mensuel'), 0, ',', ' ') }} GNF</th>
                        <th class="text-end">{{ number_format($tarifs->sum('total_annuel'), 0, ',', ' ') }} GNF</th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header bg-white">
        <h5 class="mb-0">Légende</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6>Frais uniques</h6>
                <ul class="mb-md-0">
                    <li>Inscription — payable une fois</li>
                    <li>Uniforme — uniforme scolaire</li>
                    <li>Livres — manuels scolaires</li>
                </ul>
            </div>
            <div class="col-md-6">
                <h6>Frais mensuels</h6>
                <ul class="mb-0">
                    <li>Scolarité, cantine, transport — payables par mois</li>
                    <li>Le total annuel est calculé sur {{ $nombreTranches }} mois</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@else
<div class="card">
    <div class="card-body text-center py-5">
        <i class="fas fa-table fa-3x text-muted mb-3"></i>
        <h5 class="text-muted">Aucun tarif trouvé</h5>
        <p class="text-muted">Créez des tarifs pour les classes.</p>
        <a href="{{ route('tarifs.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>
            Créer un tarif
        </a>
    </div>
</div>
@endif
@endsection
