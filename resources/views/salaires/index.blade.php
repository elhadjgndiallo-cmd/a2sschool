@extends('layouts.app')

@section('title', 'Gestion des Salaires des Enseignants')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-coins mr-2"></i>
                        Gestion des Salaires des Enseignants
                    </h3>
                    <div>
                        <a href="{{ route('salaires.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus mr-1"></i>
                            Nouveau Salaire
                        </a>
                        <button type="button" class="btn btn-success ml-2" data-bs-toggle="modal" data-bs-target="#calculerModal">
                            <i class="fas fa-calculator mr-1"></i>
                            Calculer Période
                        </button>
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
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($salaires as $salaire)
                                        <tr>
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
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('salaires.show', $salaire) }}" 
                                                       class="btn btn-sm btn-info" title="Voir détails">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    
                                                    @if($salaire->statut === 'calculé')
                                                        <a href="{{ route('salaires.edit', $salaire) }}" 
                                                           class="btn btn-sm btn-warning" title="Modifier">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form action="{{ route('salaires.valider', $salaire) }}" 
                                                              method="POST" style="display: inline;">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-success" 
                                                                    title="Valider" 
                                                                    onclick="return confirm('Valider ce salaire ?')">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                    
                                                    @if($salaire->statut === 'validé')
                                                        <a href="{{ route('salaires.payer.form', $salaire) }}" 
                                                           class="btn btn-sm btn-primary" title="Payer">
                                                            <i class="fas fa-money-bill-wave"></i>
                                                        </a>
                                                    @endif
                                                    
                                                    @if($salaire->statut !== 'payé')
                                                        <form action="{{ route('salaires.destroy', $salaire) }}" 
                                                              method="POST" style="display: inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger" 
                                                                    title="Supprimer" 
                                                                    onclick="return confirm('Supprimer ce salaire ?')">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
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
                            <p>Commencez par créer un nouveau salaire ou calculer les salaires pour une période.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour calculer les salaires d'une période -->
<div class="modal fade" id="calculerModal" tabindex="-1" aria-labelledby="calculerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="calculerModalLabel">
                    <i class="fas fa-calculator mr-2"></i>
                    Calculer les Salaires pour une Période
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('salaires.calculer-periode') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="periode_debut">Période Début <span class="text-danger">*</span></label>
                                <input type="date" name="periode_debut" id="periode_debut" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="periode_fin">Période Fin <span class="text-danger">*</span></label>
                                <input type="date" name="periode_fin" id="periode_fin" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="taux_horaire_defaut">Taux Horaire Défaut (GNF) <span class="text-danger">*</span></label>
                                <input type="number" name="taux_horaire_defaut" id="taux_horaire_defaut" 
                                       class="form-control" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="salaire_base_defaut">Salaire de Base Défaut (GNF) <span class="text-danger">*</span></label>
                                <input type="number" name="salaire_base_defaut" id="salaire_base_defaut" 
                                       class="form-control" step="0.01" min="0" required>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Note:</strong> Cette action créera automatiquement un salaire pour chaque enseignant 
                        avec les paramètres par défaut (80 heures par mois). Vous pourrez ensuite modifier 
                        individuellement chaque salaire selon les besoins.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-calculator mr-1"></i>
                        Calculer les Salaires
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

