@extends('layouts.app')

@section('title', 'Gestion des Entrées')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Gestion des Entrées</h4>
                <p class="text-muted">Suivi des entrées d'argent de l'établissement</p>
            </div>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-success text-white h-100">
                <div class="card-body d-flex flex-column justify-content-center text-center">
                    <h2 class="card-title mb-0 font-weight-bold">{{ number_format($totalEntreesManuelles, 0, ',', ' ') }} GNF</h2>
                    <p class="card-text">Entrées Manuelles</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-primary text-white h-100">
                <div class="card-body d-flex flex-column justify-content-center text-center">
                    <h2 class="card-title mb-0 font-weight-bold">{{ number_format($totalPaiementsFrais, 0, ',', ' ') }} GNF</h2>
                    <p class="card-text">Frais de Scolarité</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white h-100">
                <div class="card-body d-flex flex-column justify-content-center text-center">
                    <h2 class="card-title mb-0 font-weight-bold">{{ number_format($totalGeneral, 0, ',', ' ') }} GNF</h2>
                    <p class="card-text">Total Entrées</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Filtres</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('entrees.index') }}">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="source" class="form-label">Source</label>
                                <select name="source" id="source" class="form-select">
                                    <option value="">Toutes les sources</option>
                                    @foreach($sources as $source)
                                        <option value="{{ $source }}" {{ request('source') == $source ? 'selected' : '' }}>
                                            {{ $source }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="date_debut" class="form-label">Date début</label>
                                <input type="date" name="date_debut" id="date_debut" class="form-control" value="{{ request('date_debut') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="date_fin" class="form-label">Date fin</label>
                                <input type="date" name="date_fin" id="date_fin" class="form-control" value="{{ request('date_fin') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-1"></i>Filtrer
                                    </button>
                                    <a href="{{ route('entrees.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times me-1"></i>Effacer
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h5>Entrées Manuelles</h5>
                <a href="{{ route('entrees.create') }}" class="btn btn-success">
                    <i class="fas fa-plus me-1"></i>Nouvelle Entrée
                </a>
            </div>
        </div>
    </div>

    <!-- Tableau des entrées manuelles -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if($entrees->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Libellé</th>
                                        <th>Source</th>
                                        <th>Montant</th>
                                        <th>Mode de Paiement</th>
                                        <th>Enregistré par</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($entrees as $entree)
                                        <tr>
                                            <td>{{ $entree->date_entree->format('d/m/Y') }}</td>
                                            <td>{{ $entree->libelle }}</td>
                                            <td>
                                                <span class="badge bg-info">{{ $entree->source }}</span>
                                            </td>
                                            <td class="text-success fw-bold">{{ $entree->montant_formate }}</td>
                                            <td>
                                                <span class="badge bg-secondary">{{ ucfirst($entree->mode_paiement) }}</span>
                                            </td>
                                            <td>{{ $entree->enregistrePar->nom ?? 'N/A' }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('entrees.show', $entree) }}" class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('entrees.edit', $entree) }}" class="btn btn-sm btn-warning">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('entrees.destroy', $entree) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette entrée ?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination des entrées manuelles -->
                        @if($entrees->hasPages())
                            <div class="mt-3">
                                {{ $entrees->appends(request()->query())->links('vendor.pagination.custom') }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Aucune entrée manuelle trouvée.</p>
                            <a href="{{ route('entrees.create') }}" class="btn btn-success">
                                <i class="fas fa-plus me-1"></i>Créer la première entrée
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Paiements de frais de scolarité -->
    <div class="row mt-5">
        <div class="col-12">
            <h5>Paiements de Frais de Scolarité</h5>
            <div class="card">
                <div class="card-body">
                    @if($paiementsFrais->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Élève</th>
                                        <th>Libellé</th>
                                        <th>Montant</th>
                                        <th>Mode de Paiement</th>
                                        <th>Encaissé par</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($paiementsFrais as $paiement)
                                        <tr>
                                            <td>{{ $paiement->date_paiement->format('d/m/Y') }}</td>
                                            <td>{{ $paiement->fraisScolarite->eleve->utilisateur->nom ?? 'N/A' }} {{ $paiement->fraisScolarite->eleve->utilisateur->prenom ?? 'N/A' }}</td>
                                            <td>{{ $paiement->fraisScolarite->libelle }}</td>
                                            <td class="text-success fw-bold">{{ number_format($paiement->montant_paye, 0, ',', ' ') }} GNF</td>
                                            <td>
                                                <span class="badge bg-secondary">{{ ucfirst($paiement->mode_paiement) }}</span>
                                            </td>
                                            <td>{{ $paiement->encaissePar->nom ?? 'N/A' }}</td>
                                            <td>
                                                <a href="{{ route('paiements.show', $paiement->fraisScolarite) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!-- Pagination des paiements de frais de scolarité -->
                        @if($paiementsFrais->hasPages())
                            <div class="mt-3">
                                {{ $paiementsFrais->appends(request()->query())->links('vendor.pagination.custom') }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-graduation-cap fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Aucun paiement de frais de scolarité trouvé.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
