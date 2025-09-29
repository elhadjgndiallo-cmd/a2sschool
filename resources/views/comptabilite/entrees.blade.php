@extends('layouts.app')

@section('title', 'Entrées - Comptabilité')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-arrow-up text-success me-2"></i>
                    Entrées (Revenus)
                </h2>
                <div class="btn-group">
                    <a href="{{ route('entrees.create') }}" class="btn btn-success">
                        <i class="fas fa-plus me-1"></i>Nouvelle Entrée
                    </a>
                    <button class="btn btn-outline-primary" onclick="window.print()">
                        <i class="fas fa-print me-1"></i>Imprimer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-filter me-2"></i>Filtres
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('comptabilite.entrees') }}">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="date_debut" class="form-label">Date de début</label>
                                <input type="date" class="form-control" id="date_debut" name="date_debut" 
                                       value="{{ request('date_debut') }}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="date_fin" class="form-label">Date de fin</label>
                                <input type="date" class="form-control" id="date_fin" name="date_fin" 
                                       value="{{ request('date_fin') }}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="source" class="form-label">Source</label>
                                <select class="form-select" id="source" name="source">
                                    <option value="">Toutes les sources</option>
                                    <option value="Paiements scolaires" {{ request('source') == 'Paiements scolaires' ? 'selected' : '' }}>Paiements scolaires</option>
                                    <option value="Dons" {{ request('source') == 'Dons' ? 'selected' : '' }}>Dons</option>
                                    <option value="Subventions" {{ request('source') == 'Subventions' ? 'selected' : '' }}>Subventions</option>
                                    <option value="Autres" {{ request('source') == 'Autres' ? 'selected' : '' }}>Autres</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-search me-1"></i>Filtrer
                                </button>
                                <a href="{{ route('comptabilite.entrees') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-refresh me-1"></i>Réinitialiser
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ number_format($statsEntrees['total'], 0, ',', ' ') }}</h3>
                    <small>Total (GNF)</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ $statsEntrees['nombre'] }}</h3>
                    <small>Nombre d'entrées</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ number_format($statsEntrees['moyenne'], 0, ',', ' ') }}</h3>
                    <small>Moyenne (GNF)</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des entrées -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>Liste des Entrées
                    </h5>
                </div>
                <div class="card-body">
                    @if($entrees->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Libellé</th>
                                        <th>Source</th>
                                        <th class="text-end">Montant</th>
                                        <th>Enregistré par</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($entrees as $entree)
                                        <tr>
                                            <td>
                                                <i class="fas fa-calendar text-muted me-1"></i>
                                                {{ $entree->date_entree->format('d/m/Y') }}
                                            </td>
                                            <td>
                                                <strong>{{ $entree->libelle }}</strong>
                                                @if($entree->description)
                                                    <br><small class="text-muted">{{ Str::limit($entree->description, 50) }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $entree->source == 'Paiements scolaires' ? 'primary' : ($entree->source == 'Dons' ? 'success' : 'info') }}">
                                                    {{ $entree->source }}
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <strong class="text-success">
                                                    {{ number_format($entree->montant, 0, ',', ' ') }} GNF
                                                </strong>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($entree->enregistrePar->photo_profil)
                                                        <img src="{{ asset('storage/' . $entree->enregistrePar->photo_profil) }}" 
                                                             alt="Photo" class="rounded-circle me-2" 
                                                             style="width: 30px; height: 30px; object-fit: cover;">
                                                    @else
                                                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-2" 
                                                             style="width: 30px; height: 30px;">
                                                            <i class="fas fa-user text-white" style="font-size: 12px;"></i>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <div class="fw-bold">{{ $entree->enregistrePar->nom }} {{ $entree->enregistrePar->prenom }}</div>
                                                        <small class="text-muted">{{ ucfirst($entree->enregistrePar->role) }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('entrees.show', $entree) }}" class="btn btn-outline-primary" title="Voir">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('entrees.edit', $entree) }}" class="btn btn-outline-warning" title="Modifier">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form method="POST" action="{{ route('entrees.destroy', $entree) }}" 
                                                          style="display: inline;" 
                                                          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette entrée ?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger" title="Supprimer">
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

                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                <small class="text-muted">
                                    Affichage de {{ $entrees->firstItem() }} à {{ $entrees->lastItem() }} 
                                    sur {{ $entrees->total() }} entrées
                                </small>
                            </div>
                            <div>
                                {{ $entrees->links() }}
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Aucune entrée trouvée</h5>
                            <p class="text-muted">Commencez par ajouter une nouvelle entrée.</p>
                            <a href="{{ route('entrees.create') }}" class="btn btn-success">
                                <i class="fas fa-plus me-1"></i>Ajouter une entrée
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
