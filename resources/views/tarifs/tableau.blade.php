@extends('layouts.app')

@section('title', 'Tableau des Tarifs par Classe')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-table mr-2"></i>
                        Tableau des Tarifs par Classe
                    </h3>
                    <div>
                        <form method="GET" action="{{ route('tarifs.tableau') }}" class="d-inline">
                            <select name="annee_scolaire" class="form-control d-inline-block" style="width: auto;" onchange="this.form.submit()">
                                @foreach($anneesScolaires as $annee)
                                    <option value="{{ $annee }}" {{ $anneeScolaire == $annee ? 'selected' : '' }}>
                                        {{ $annee }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                        <a href="{{ route('tarifs.index') }}" class="btn btn-primary ml-2">
                            <i class="fas fa-cog mr-1"></i>
                            Gérer les Tarifs
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($tarifs->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="thead-dark">
                                    <tr>
                                        <th rowspan="2" class="text-center align-middle">Classe</th>
                                        <th colspan="3" class="text-center">Frais Uniques</th>
                                        <th colspan="3" class="text-center">Frais Mensuels</th>
                                        <th rowspan="2" class="text-center align-middle">Total Mensuel</th>
                                        <th rowspan="2" class="text-center align-middle">Total Annuel</th>
                                        <th rowspan="2" class="text-center align-middle">Statut</th>
                                    </tr>
                                    <tr>
                                        <th class="text-center">Inscription</th>
                                        <th class="text-center">Uniforme</th>
                                        <th class="text-center">Livres</th>
                                        <th class="text-center">Scolarité</th>
                                        <th class="text-center">Cantine</th>
                                        <th class="text-center">Transport</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tarifs as $tarif)
                                        <tr>
                                            <td class="text-center">
                                                <strong>{{ $tarif->classe->nom }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $tarif->annee_scolaire }}</small>
                                            </td>
                                            <td class="text-right">
                                                @if($tarif->frais_inscription > 0)
                                                    <span class="badge bg-primary text-white">{{ number_format($tarif->frais_inscription, 0, ',', ' ') }} GNF</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                @if($tarif->frais_uniforme > 0)
                                                    <span class="badge bg-dark text-white">{{ number_format($tarif->frais_uniforme, 0, ',', ' ') }} GNF</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                @if($tarif->frais_livres > 0)
                                                    <span class="badge bg-warning text-dark">{{ number_format($tarif->frais_livres, 0, ',', ' ') }} GNF</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                @if($tarif->frais_scolarite_mensuel > 0)
                                                    <span class="badge bg-info text-white">{{ number_format($tarif->frais_scolarite_mensuel, 0, ',', ' ') }} GNF</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                @if($tarif->frais_cantine_mensuel > 0)
                                                    <span class="badge bg-success text-white">{{ number_format($tarif->frais_cantine_mensuel, 0, ',', ' ') }} GNF</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                @if($tarif->frais_transport_mensuel > 0)
                                                    <span class="badge bg-secondary text-white">{{ number_format($tarif->frais_transport_mensuel, 0, ',', ' ') }} GNF</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                <strong class="text-primary">{{ number_format($tarif->total_mensuel, 0, ',', ' ') }} GNF</strong>
                                            </td>
                                            <td class="text-right">
                                                <strong class="text-success">{{ number_format($tarif->total_annuel, 0, ',', ' ') }} GNF</strong>
                                            </td>
                                            <td class="text-center">
                                                @if($tarif->actif)
                                                    <span class="badge bg-success text-white">Actif</span>
                                                @else
                                                    <span class="badge bg-danger text-white">Inactif</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-dark">
                                    <tr>
                                        <th colspan="7" class="text-right">TOTAUX</th>
                                        <th class="text-right">
                                            <strong>{{ number_format($tarifs->sum('total_mensuel'), 0, ',', ' ') }} GNF</strong>
                                        </th>
                                        <th class="text-right">
                                            <strong>{{ number_format($tarifs->sum('total_annuel'), 0, ',', ' ') }} GNF</strong>
                                        </th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Statistiques -->
                        <div class="row mt-4">
                            <div class="col-md-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body text-center">
                                        <h4>{{ $tarifs->count() }}</h4>
                                        <p class="mb-0">Classes</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <h4>{{ $tarifs->where('actif', true)->count() }}</h4>
                                        <p class="mb-0">Tarifs Actifs</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body text-center">
                                        <h4>{{ number_format($tarifs->sum('total_mensuel') / 1000, 0) }}K</h4>
                                        <p class="mb-0">Total Mensuel (GNF)</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-warning text-white">
                                    <div class="card-body text-center">
                                        <h4>{{ number_format($tarifs->sum('total_annuel') / 1000, 0) }}K</h4>
                                        <p class="mb-0">Total Annuel (GNF)</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Légende -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-info-circle mr-2"></i>Légende</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6>Frais Uniques :</h6>
                                        <ul class="list-unstyled">
                                            <li><span class="badge bg-primary text-white">Inscription</span> - Frais d'inscription (payable une fois)</li>
                                            <li><span class="badge bg-dark text-white">Uniforme</span> - Frais d'uniforme scolaire</li>
                                            <li><span class="badge bg-warning text-dark">Livres</span> - Frais de manuels scolaires</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Frais Mensuels :</h6>
                                        <ul class="list-unstyled">
                                            <li><span class="badge bg-info text-white">Scolarité</span> - Frais de scolarité mensuels</li>
                                            <li><span class="badge bg-success text-white">Cantine</span> - Frais de restauration</li>
                                            <li><span class="badge bg-secondary text-white">Transport</span> - Frais de transport scolaire</li>
                                        </ul>
                                    </div>
                                </div>
                                <hr>
                                <div class="alert alert-info">
                                    <i class="fas fa-lightbulb mr-2"></i>
                                    <strong>Note :</strong> Les frais mensuels sont payables par tranches ({{ $tarif->nombre_tranches }} mois). 
                                    Le total annuel inclut tous les frais pour l'année scolaire complète.
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-table fa-3x mb-3"></i>
                            <h5>Aucun tarif trouvé</h5>
                            <p>Commencez par créer des tarifs pour les classes.</p>
                            <a href="{{ route('tarifs.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus mr-1"></i>
                                Créer un Tarif
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
