@extends('layouts.app')

@section('title', 'Sélectionner une Classe pour les Statistiques')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h2 class="mb-4"><i class="fas fa-chart-bar me-2"></i>Sélectionner une Classe pour les Statistiques</h2>
            <p class="text-muted">Veuillez choisir une classe pour afficher les statistiques de notes et le classement des élèves.</p>

            @if($classes->isEmpty())
                <div class="alert alert-warning" role="alert">
                    Aucune classe disponible pour afficher les statistiques. Veuillez créer des classes d'abord.
                </div>
            @else
                <div class="row">
                    @foreach($classes as $classe)
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 shadow-sm border-primary">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title text-primary">
                                        <i class="fas fa-chalkboard-teacher me-2"></i>{{ $classe->nom }}
                                    </h5>
                                    <p class="card-text text-muted">Niveau: {{ $classe->niveau }}</p>
                                    <p class="card-text">Nombre d'élèves: <strong>{{ $classe->eleves->count() }}</strong></p>
                                    <div class="mt-auto">
                                        <a href="{{ route('notes.statistiques.classe', $classe->id) }}" class="btn btn-primary btn-sm w-100">
                                            <i class="fas fa-chart-line me-2"></i>Voir les Statistiques
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Section Tableau Statistique Trimestriel -->
    <div class="row mt-5">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-table me-2"></i>
                        Tableau Statistique Trimestriel
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('notes.statistiques') }}">
                        <div class="row mb-3">
                            <div class="col-md-5">
                                <label for="classe_id" class="form-label">Classe</label>
                                <select name="classe_id" id="classe_id" class="form-select">
                                    <option value="">-- Sélectionner une classe --</option>
                                    @foreach($classes as $c)
                                    <option value="{{ $c->id }}" {{ $classeId == $c->id ? 'selected' : '' }}>
                                        {{ $c->nom }} ({{ $c->niveau }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label for="periode_id" class="form-label">Trimestre</label>
                                <select name="periode_id" id="periode_id" class="form-select">
                                    <option value="">-- Sélectionner un trimestre --</option>
                                    @foreach($periodes as $p)
                                    <option value="{{ $p->id }}" {{ $periodeId == $p->id ? 'selected' : '' }}>
                                        {{ $p->nom }} ({{ $p->date_debut->format('d/m/Y') }} - {{ $p->date_fin->format('d/m/Y') }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-1"></i>
                                    Filtrer
                                </button>
                            </div>
                        </div>
                    </form>

                    @if($stats && $classe && $periode)
                    <div class="mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">
                                Tableau statistique - {{ $classe->nom }} - {{ $periode->nom }} ({{ $periode->date_debut->format('d/m/Y') }} - {{ $periode->date_fin->format('d/m/Y') }})
                            </h6>
                            <a href="{{ route('notes.statistiques.imprimer') }}?classe_id={{ $classe->id }}&periode_id={{ $periode->id }}" 
                               class="btn btn-sm btn-primary" target="_blank">
                                <i class="fas fa-print me-1"></i>
                                Imprimer
                            </a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped mb-0 text-center align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th rowspan="2" class="text-start align-middle" style="min-width: 150px;">Statistiques</th>
                                        <th colspan="2">Effectifs</th>
                                        <th colspan="2">Composés</th>
                                        <th colspan="2">Non composés</th>
                                        <th colspan="4">Moyennant</th>
                                        <th colspan="4">Non moyennant</th>
                                    </tr>
                                    <tr>
                                        <th>Total</th>
                                        <th>Filles</th>
                                        <th>Total</th>
                                        <th>Filles</th>
                                        <th>Total</th>
                                        <th>Filles</th>
                                        <th>Total</th>
                                        <th>Filles</th>
                                        <th>% Total</th>
                                        <th>% Filles</th>
                                        <th>Total</th>
                                        <th>Filles</th>
                                        <th>% Total</th>
                                        <th>% Filles</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="text-start fw-bold">Classe</td>
                                        <td class="fw-bold">{{ $stats['effectifs']['total'] }}</td>
                                        <td class="fw-bold">{{ $stats['effectifs']['filles'] }}</td>
                                        <td>{{ $stats['composes']['total'] }}</td>
                                        <td>{{ $stats['composes']['filles'] }}</td>
                                        <td>{{ $stats['non_composes']['total'] }}</td>
                                        <td>{{ $stats['non_composes']['filles'] }}</td>
                                        <td class="text-success fw-bold">{{ $stats['moyennant']['total'] }}</td>
                                        <td class="text-success fw-bold">{{ $stats['moyennant']['filles'] }}</td>
                                        <td class="text-success">{{ $stats['moyennant']['pct_total'] }}%</td>
                                        <td class="text-success">{{ $stats['moyennant']['pct_filles'] }}%</td>
                                        <td class="text-danger fw-bold">{{ $stats['non_moyennant']['total'] }}</td>
                                        <td class="text-danger fw-bold">{{ $stats['non_moyennant']['filles'] }}</td>
                                        <td class="text-danger">{{ $stats['non_moyennant']['pct_total'] }}%</td>
                                        <td class="text-danger">{{ $stats['non_moyennant']['pct_filles'] }}%</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @elseif($classeId || $periodeId)
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle me-2"></i>
                        Aucune donnée disponible pour cette classe et ce trimestre.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
