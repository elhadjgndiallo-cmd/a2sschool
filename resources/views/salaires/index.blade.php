@extends('layouts.app')

@section('title', 'Gestion des Salaires des Enseignants')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="card-title mb-0">
                            <i class="fas fa-coins mr-2"></i>
                            Gestion des Salaires des Enseignants
                        </h3>
                        @if($anneeScolaire ?? null)
                            <small class="text-muted">
                                <i class="fas fa-calendar-alt me-1"></i>
                                Année scolaire active : <strong>{{ $anneeScolaire->nom }}</strong>
                            </small>
                        @endif
                    </div>
                    <div>
                        <a href="{{ route('salaires.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus mr-1"></i>
                            Nouveau bulletin
                        </a>
                        <a href="{{ route('salaires.bons.create') }}" class="btn btn-warning ml-2">
                            <i class="fas fa-hand-holding-usd mr-1"></i>
                            Bon de salaire (avance)
                        </a>
                        <a href="{{ route('salaires.bons.index') }}" class="btn btn-outline-warning ml-2">
                            <i class="fas fa-receipt mr-1"></i>
                            Avances
                        </a>
                        <a href="{{ route('salaires.rapports') }}" class="btn btn-info ml-2">
                            <i class="fas fa-chart-bar mr-1"></i>
                            Rapports
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filtres -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form method="GET" action="{{ route('salaires.index') }}">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="enseignant_id">Enseignant</label>
                                            <select name="enseignant_id" id="enseignant_id" class="form-control">
                                                <option value="">Tous les enseignants</option>
                                                @foreach($enseignants as $enseignant)
                                                    <option value="{{ $enseignant->id }}" 
                                                            {{ request('enseignant_id') == $enseignant->id ? 'selected' : '' }}>
                                                        {{ $enseignant->utilisateur->nom }} {{ $enseignant->utilisateur->prenom }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="statut">Statut</label>
                                            <select name="statut" id="statut" class="form-control">
                                                <option value="">Tous les statuts</option>
                                                <option value="calculé" {{ request('statut') == 'calculé' ? 'selected' : '' }}>Calculé</option>
                                                <option value="validé" {{ request('statut') == 'validé' ? 'selected' : '' }}>Validé</option>
                                                <option value="payé" {{ request('statut') == 'payé' ? 'selected' : '' }}>Payé</option>
                                                <option value="annulé" {{ request('statut') == 'annulé' ? 'selected' : '' }}>Annulé</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="periode_debut">Période Début</label>
                                            <input type="date" name="periode_debut" id="periode_debut" class="form-control" 
                                                   value="{{ request('periode_debut') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="periode_fin">Période Fin</label>
                                            <input type="date" name="periode_fin" id="periode_fin" class="form-control" 
                                                   value="{{ request('periode_fin') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <div>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-search mr-1"></i>
                                                    Filtrer
                                                </button>
                                                <a href="{{ route('salaires.index') }}" class="btn btn-secondary ml-2">
                                                    <i class="fas fa-times mr-1"></i>
                                                    Effacer
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Liste des salaires -->
                    @if($salaires->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Enseignant</th>
                                        <th>Période</th>
                                        <th>Heures</th>
                                        <th>Taux Horaire</th>
                                        <th>Salaire Brut</th>
                                        <th>Salaire Net</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($salaires as $salaire)
                                        <tr class="table-row-clickable" data-href="{{ route('salaires.show', $salaire) }}" role="button" tabindex="0">
                                            <td>
                                                <strong>{{ $salaire->enseignant->utilisateur->nom }} {{ $salaire->enseignant->utilisateur->prenom }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $salaire->enseignant->utilisateur->email }}</small>
                                            </td>
                                            <td>
                                                {{ $salaire->periode_debut->format('d/m/Y') }} - {{ $salaire->periode_fin->format('d/m/Y') }}
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-info">{{ $salaire->nombre_heures }}h</span>
                                            </td>
                                            <td class="text-right">
                                                <strong>{{ number_format($salaire->taux_horaire, 0, ',', ' ') }} GNF/h</strong>
                                            </td>
                                            <td class="text-right">
                                                <strong class="text-primary">
                                                    {{ number_format($salaire->salaire_brut, 0, ',', ' ') }} GNF
                                                </strong>
                                            </td>
                                            <td class="text-right">
                                                <strong class="text-success">
                                                    {{ number_format($salaire->salaire_net, 0, ',', ' ') }} GNF
                                                </strong>
                                            </td>
                                            <td>
                                                @switch($salaire->statut)
                                                    @case('calculé')
                                                        <span class="badge badge-warning">Calculé</span>
                                                        @break
                                                    @case('validé')
                                                        <span class="badge badge-info">Validé</span>
                                                        @break
                                                    @case('payé')
                                                        <span class="badge badge-success">Payé</span>
                                                        @break
                                                    @case('annulé')
                                                        <span class="badge badge-danger">Annulé</span>
                                                        @break
                                                    @default
                                                        <span class="badge badge-secondary">{{ $salaire->statut }}</span>
                                                @endswitch
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $salaires->appends(request()->query())->links('vendor.pagination.custom') }}
                        </div>
                    @else
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-coins fa-3x mb-3"></i>
                            <h5>Aucun salaire trouvé</h5>
                            <p>Commencez par créer un bulletin de salaire ou un bon d'avance.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

