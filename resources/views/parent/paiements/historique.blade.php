@extends('layouts.app')

@section('title', 'Historique des Paiements')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center pt-3 pb-2 mb-3 border-bottom gap-2">
    <div>
        <h1 class="h2 mb-0">
            <i class="fas fa-history text-primary me-2"></i>
            Historique des Paiements
        </h1>
        <p class="text-muted mb-0">Consultez l'historique de tous les paiements effectués</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('parent.paiements.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                <span class="d-none d-sm-inline">Retour</span>
            </a>
        </div>
    </div>
</div>

<!-- Filtres -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('parent.paiements.historique') }}">
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label for="eleve_id" class="form-label">Enfant</label>
                            <select name="eleve_id" id="eleve_id" class="form-select">
                                <option value="">Tous les enfants</option>
                                @foreach($enfantsList as $enfant)
                                    <option value="{{ $enfant->id }}" {{ request('eleve_id') == $enfant->id ? 'selected' : '' }}>
                                        {{ $enfant->utilisateur->nom }} {{ $enfant->utilisateur->prenom }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-3">
                            <label for="date_debut" class="form-label">Date début</label>
                            <input type="date" name="date_debut" id="date_debut" class="form-control" value="{{ request('date_debut') }}">
                        </div>
                        <div class="col-12 col-md-3">
                            <label for="date_fin" class="form-label">Date fin</label>
                            <input type="date" name="date_fin" id="date_fin" class="form-control" value="{{ request('date_fin') }}">
                        </div>
                        <div class="col-12 col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-1"></i>
                                    <span class="d-none d-sm-inline">Filtrer</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Liste des paiements -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-list me-2"></i>
                    Historique des Paiements ({{ $paiements->total() }} paiement(s))
                </h6>
            </div>
            <div class="card-body">
                @if($paiements->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Enfant</th>
                                    <th>Classe</th>
                                    <th>Type de frais</th>
                                    <th>Montant</th>
                                    <th>Méthode</th>
                                    <th>Encaisse par</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($paiements as $paiement)
                                    <tr>
                                        <td>
                                            <span class="badge bg-light text-dark">
                                                {{ $paiement->date_paiement->format('d/m/Y') }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2">
                                                    <i class="fas fa-user text-primary"></i>
                                                </div>
                                                <div>
                                                    <strong>{{ $paiement->fraisScolarite->eleve->utilisateur->nom }}</strong><br>
                                                    <small class="text-muted">{{ $paiement->fraisScolarite->eleve->utilisateur->prenom }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                {{ $paiement->fraisScolarite->eleve->classe->nom ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td>
                                            <strong>{{ $paiement->fraisScolarite->libelle }}</strong>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-success">
                                                {{ number_format($paiement->montant_paye, 0, ',', ' ') }} GNF
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                {{ ucfirst($paiement->methode_paiement ?? 'Non spécifié') }}
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $paiement->encaissePar->nom ?? 'N/A' }}
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('parent.paiements.show', $paiement->fraisScolarite) }}" 
                                                   class="btn btn-outline-primary" title="Voir détails">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $paiements->links('vendor.pagination.custom') }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Aucun paiement trouvé</h5>
                        <p class="text-muted">Aucun paiement ne correspond aux critères de recherche.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

