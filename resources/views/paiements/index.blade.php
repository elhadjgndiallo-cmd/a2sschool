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
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-filter me-2"></i>Filtres de recherche
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <form method="GET" action="{{ route('paiements.index') }}" class="row g-3">
                                        <div class="col-md-3">
                                            <label for="classe_id" class="form-label">Classe</label>
                                            <select class="form-select" id="classe_id" name="classe_id">
                                                <option value="">Toutes les classes</option>
                                                @foreach(\App\Models\Classe::orderBy('nom')->get() as $classe)
                                                    <option value="{{ $classe->id }}" 
                                                        {{ request('classe_id') == $classe->id ? 'selected' : '' }}>
                                                        {{ $classe->nom }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="matricule" class="form-label">Matricule</label>
                                            <input type="text" class="form-control" id="matricule" name="matricule" 
                                                   value="{{ request('matricule') }}" 
                                                   placeholder="Rechercher par matricule">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="nom" class="form-label">Nom de l'élève</label>
                                            <input type="text" class="form-control" id="nom" name="nom" 
                                                   value="{{ request('nom') }}" 
                                                   placeholder="Rechercher par nom">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="statut" class="form-label">Statut</label>
                                            <select class="form-select" id="statut" name="statut">
                                                <option value="">Tous les statuts</option>
                                                <option value="paye" {{ request('statut') == 'paye' ? 'selected' : '' }}>Payé</option>
                                                <option value="en_attente" {{ request('statut') == 'en_attente' ? 'selected' : '' }}>En attente</option>
                                                <option value="en_retard" {{ request('statut') == 'en_retard' ? 'selected' : '' }}>En retard</option>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-search me-1"></i>Filtrer
                                            </button>
                                            <a href="{{ route('paiements.index') }}" class="btn btn-secondary">
                                                <i class="fas fa-times me-1"></i>Effacer
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

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
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($fraisScolarite as $frais)
                                    <tr>
                                        <td>
                                            <strong>{{ $frais->eleve->utilisateur->nom ?? 'N/A' }} {{ $frais->eleve->utilisateur->prenom ?? 'N/A' }}</strong>
                                        </td>
                                        <td>{{ $frais->eleve->numero_etudiant ?? 'N/A' }}</td>
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
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('paiements.show', $frais) }}" 
                                                   class="btn btn-sm btn-info" title="Voir détails">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($frais->montant_restant > 0)
                                                    @if($frais->paiement_par_tranches)
                                                        @php
                                                            $prochaineTranche = $frais->tranchesPaiement()
                                                                ->where('statut', 'en_attente')
                                                                ->orderBy('numero_tranche')
                                                                ->first();
                                                        @endphp
                                                        @if($prochaineTranche)
                                                            <div class="btn-group" role="group">
                                                                <a href="{{ route('paiements.payer-tranche', $prochaineTranche) }}" 
                                                                   class="btn btn-sm btn-warning" title="Payer mois">
                                                                    <i class="fas fa-credit-card"></i>
                                                                </a>
                                                                <a href="{{ route('paiements.payer-direct', $frais) }}" 
                                                                   class="btn btn-sm btn-success" title="Payer tout">
                                                                    <i class="fas fa-money-bill-wave"></i>
                                                                </a>
                                                            </div>
                                                        @else
                                                            <button class="btn btn-sm btn-secondary" disabled title="Toutes les tranches sont payées">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        @endif
                                                    @else
                                                        <a href="{{ route('paiements.payer-direct', $frais) }}" 
                                                           class="btn btn-sm btn-success" title="Payer">
                                                            <i class="fas fa-money-bill-wave"></i>
                                                        </a>
                                                    @endif
                                                @endif
                                                @if($frais->paiements()->count() > 0)
                                                    <button type="button" 
                                                            class="btn btn-sm btn-danger" 
                                                            title="Annuler le dernier paiement"
                                                            onclick="confirmAnnulerPaiement({{ $frais->id }}, '{{ $frais->eleve->utilisateur->nom }} {{ $frais->eleve->utilisateur->prenom }}')">
                                                        <i class="fas fa-undo"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center text-muted">
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

<!-- Modal de confirmation pour annuler le dernier paiement -->
<div class="modal fade" id="annulerPaiementModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Annuler le dernier paiement
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <h6><i class="fas fa-exclamation-triangle me-2"></i>ATTENTION : Action irréversible</h6>
                    <p class="mb-0">Êtes-vous sûr de vouloir annuler le dernier paiement de <strong id="eleve-nom"></strong> ?</p>
                </div>
                <p class="text-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Cette action supprimera définitivement le dernier paiement et recalculera le montant restant.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Annuler
                </button>
                <form id="annuler-paiement-form" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-undo me-1"></i>Annuler le paiement
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function confirmAnnulerPaiement(fraisId, eleveNom) {
    // Mettre à jour le contenu du modal
    document.getElementById('eleve-nom').textContent = eleveNom;
    
    // Mettre à jour l'action du formulaire
    document.getElementById('annuler-paiement-form').action = `/paiements/${fraisId}/annuler-dernier-paiement`;
    
    // Afficher le modal
    new bootstrap.Modal(document.getElementById('annulerPaiementModal')).show();
}
</script>
@endpush

@endsection
