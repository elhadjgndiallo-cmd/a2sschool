@extends('layouts.app')

@section('title', 'Gestion des Paiements')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-credit-card mr-2"></i>
                        Gestion des Paiements
                    </h3>
                    <div>
                        <a href="{{ route('paiements.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus mr-1"></i>
                            Nouveau Frais
                        </a>
                        <a href="{{ route('paiements.rapports') }}" class="btn btn-info">
                            <i class="fas fa-chart-bar mr-1"></i>
                            Rapports
                        </a>
                        <a href="{{ route('recus-rappel.create') }}" class="btn btn-danger">
                            <i class="fas fa-bell mr-1"></i>
                            Créer Reçu de Rappel
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    @endif

                    <!-- Filtres -->
                    <form method="GET" action="{{ route('paiements.index') }}" class="mb-3">
                        <div class="row g-2">
                            <div class="col-12 col-sm-6 col-md-3">
                                <select class="form-select" id="classe_id" name="classe_id" title="Classe">
                                    <option value="">Toutes les classes</option>
                                    @foreach(\App\Models\Classe::orderBy('nom')->get() as $classe)
                                        <option value="{{ $classe->id }}"
                                            {{ request('classe_id') == $classe->id ? 'selected' : '' }}>
                                            {{ $classe->nom }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 col-md-2">
                                <input type="text" class="form-control" id="matricule" name="matricule"
                                       value="{{ request('matricule') }}"
                                       placeholder="Matricule...">
                            </div>
                            <div class="col-12 col-sm-6 col-md-3">
                                <input type="text" class="form-control" id="nom" name="nom"
                                       value="{{ request('nom') }}"
                                       placeholder="Nom de l'élève...">
                            </div>
                            <div class="col-12 col-sm-6 col-md-2">
                                <select class="form-select" id="statut" name="statut" title="Statut">
                                    <option value="">Tous les statuts</option>
                                    <option value="paye" {{ request('statut') == 'paye' ? 'selected' : '' }}>Payé</option>
                                    <option value="en_attente" {{ request('statut') == 'en_attente' ? 'selected' : '' }}>En attente</option>
                                    <option value="en_retard" {{ request('statut') == 'en_retard' ? 'selected' : '' }}>En retard</option>
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 col-md-2">
                                <div class="d-flex gap-1">
                                    <button type="submit" class="btn btn-primary flex-fill">
                                        <i class="fas fa-search"></i>
                                        <span class="d-none d-sm-inline">Filtrer</span>
                                    </button>
                                    <a href="{{ route('paiements.index') }}" class="btn btn-outline-secondary" title="Réinitialiser">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Élève</th>
                                    <th>Matricule</th>
                                    <th>Classe</th>
                                    <th>Libellé</th>
                                    <th>Type</th>
                                    <th>Montant</th>
                                    <th>Échéance</th>
                                    <th>Statut</th>
                                    <th>Paiement</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($fraisScolarite as $frais)
                                    <tr class="table-row-clickable" data-href="{{ route('paiements.show', $frais) }}" role="button" tabindex="0">
                                        <td>
                                            <strong>{{ $frais->eleve->utilisateur->nom ?? 'N/A' }} {{ $frais->eleve->utilisateur->prenom ?? 'N/A' }}</strong>
                                        </td>
                                        <td>
                                            @if($frais->eleve && $frais->eleve->id)
                                                <a href="{{ route('eleves.show', $frais->eleve->id) }}" 
                                                   class="text-primary text-decoration-none" 
                                                   title="Voir le profil de l'élève"
                                                   onclick="event.stopPropagation()">
                                                    <i class="fas fa-user me-1"></i>
                                                    {{ $frais->eleve->numero_etudiant ?? 'N/A' }}
                                                </a>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>{{ $frais->eleve->classe->nom ?? 'N/A' }}</td>
                                        <td>{{ $frais->libelle }}</td>
                                        <td>
                                            <span class="badge bg-info text-white">
                                                {{ ucfirst($frais->type_frais) }}
                                            </span>
                                        </td>
                                        <td>
                                            <strong>{{ number_format($frais->montant, 0, ',', ' ') }} GNF</strong>
                                            @if($frais->paiement_par_tranches)
                                                <br>
                                                <small class="text-muted">
                                                    {{ $frais->nombre_tranches }} mois de 
                                                    {{ number_format($frais->montant_tranche, 0, ',', ' ') }} GNF
                                                </small>
                                            @endif
                                        </td>
                                        <td>{{ $frais->date_echeance->format('d/m/Y') }}</td>
                                        <td>
                                            @switch($frais->statut)
                                                @case('paye')
                                                    <span class="badge bg-success text-white">Payé</span>
                                                    @break
                                                @case('en_attente')
                                                    <span class="badge bg-warning text-dark">En attente</span>
                                                    @break
                                                @case('en_retard')
                                                    <span class="badge bg-danger text-white">En retard</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary text-white">{{ $frais->statut }}</span>
                                            @endswitch
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                @php
                                                    $pourcentage = $frais->montant > 0 ? 
                                                        (($frais->montant - $frais->montant_restant) / $frais->montant) * 100 : 0;
                                                @endphp
                                                <div class="progress-bar 
                                                    @if($pourcentage == 100) bg-success
                                                    @elseif($pourcentage > 0) bg-warning
                                                    @else bg-secondary @endif" 
                                                    role="progressbar" 
                                                    style="width: {{ $pourcentage }}%">
                                                    {{ number_format($pourcentage, 1) }}%
                                                </div>
                                            </div>
                                            <small class="text-muted">
                                                Reste: {{ number_format($frais->montant_restant, 0, ',', ' ') }} GNF
                                            </small>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">
                                            <i class="fas fa-inbox fa-2x mb-2"></i>
                                            <br>
                                            Aucun frais de scolarité trouvé
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">
                                Affichage de {{ $fraisScolarite->firstItem() ?? 0 }} à {{ $fraisScolarite->lastItem() ?? 0 }} sur {{ $fraisScolarite->total() }} frais de scolarité
                            </small>
                        </div>
                        <div>
                            {{ $fraisScolarite->appends(request()->query())->links('vendor.pagination.custom') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmation pour supprimer les frais -->
<div class="modal fade" id="supprimerFraisModal" tabindex="-1" aria-labelledby="supprimerFraisModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="supprimerFraisModalLabel">
                    <i class="fas fa-trash me-2"></i>
                    Supprimer les frais
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <h6><i class="fas fa-exclamation-triangle me-2"></i>ATTENTION : Action irréversible</h6>
                    <p class="mb-0">Êtes-vous sûr de vouloir supprimer les frais de <strong id="eleve-nom-suppression"></strong> ?</p>
                </div>
                <div class="alert alert-warning">
                    <h6><i class="fas fa-info-circle me-2"></i>Détails de la suppression</h6>
                    <p class="mb-0">Frais: <strong id="frais-libelle"></strong></p>
                    <p class="mb-0">Cette action supprimera définitivement :</p>
                    <ul class="mb-0">
                        <li>Les frais de scolarité</li>
                        <li>Tous les paiements associés</li>
                        <li>Les tranches de paiement</li>
                        <li>Les entrées comptables liées</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Annuler
                </button>
                <form id="supprimer-frais-form" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i>Supprimer
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
