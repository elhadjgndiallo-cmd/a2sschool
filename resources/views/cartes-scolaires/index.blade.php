@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-id-card text-primary me-2"></i>
                        Gestion des Cartes Scolaires
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('cartes-scolaires.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Nouvelle Carte
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filtres -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="fas fa-filter me-2"></i>Filtres
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="{{ route('cartes-scolaires.index') }}">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="statut" class="form-label">Statut</label>
                                            <select class="form-select" id="statut" name="statut">
                                                <option value="">Tous les statuts</option>
                                                <option value="active" {{ request('statut') == 'active' ? 'selected' : '' }}>Active</option>
                                                <option value="expiree" {{ request('statut') == 'expiree' ? 'selected' : '' }}>Expirée</option>
                                                <option value="suspendue" {{ request('statut') == 'suspendue' ? 'selected' : '' }}>Suspendue</option>
                                                <option value="annulee" {{ request('statut') == 'annulee' ? 'selected' : '' }}>Annulée</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="type_carte" class="form-label">Type de carte</label>
                                            <select class="form-select" id="type_carte" name="type_carte">
                                                <option value="">Tous les types</option>
                                                <option value="standard" {{ request('type_carte') == 'standard' ? 'selected' : '' }}>Standard</option>
                                                <option value="temporaire" {{ request('type_carte') == 'temporaire' ? 'selected' : '' }}>Temporaire</option>
                                                <option value="remplacement" {{ request('type_carte') == 'remplacement' ? 'selected' : '' }}>Remplacement</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="eleve_id" class="form-label">Élève</label>
                                            <select class="form-select" id="eleve_id" name="eleve_id">
                                                <option value="">Tous les élèves</option>
                                                @foreach($eleves as $eleve)
                                                    <option value="{{ $eleve->id }}" {{ request('eleve_id') == $eleve->id ? 'selected' : '' }}>
                                                        {{ $eleve->utilisateur->nom }} {{ $eleve->utilisateur->prenom }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="numero_carte" class="form-label">Numéro de carte</label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="numero_carte" 
                                                   name="numero_carte" 
                                                   value="{{ request('numero_carte') }}"
                                                   placeholder="Rechercher par numéro">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary me-2">
                                            <i class="fas fa-search me-2"></i>Filtrer
                                        </button>
                                        <a href="{{ route('cartes-scolaires.index') }}" class="btn btn-secondary">
                                            <i class="fas fa-times me-2"></i>Réinitialiser
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Tableau des cartes -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Numéro</th>
                                    <th>Élève</th>
                                    <th>Classe</th>
                                    <th>Type</th>
                                    <th>Date d'émission</th>
                                    <th>Date d'expiration</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($cartes as $carte)
                                    <tr>
                                        <td>
                                            <span class="badge bg-info">{{ $carte->numero_carte }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($carte->eleve->utilisateur->photo_profil)
                                                    @php
                                                        $imageName = basename($carte->eleve->utilisateur->photo_profil);
                                                        $imagePath = 'images/profile_images/' . $imageName;
                                                    @endphp
                                                    <img src="{{ asset($imagePath) }}" 
                                                         class="rounded-circle me-2" 
                                                         width="30" height="30" 
                                                         alt="Photo">
                                                @else
                                                    <div class="bg-secondary rounded-circle me-2 d-flex align-items-center justify-content-center" 
                                                         style="width: 30px; height: 30px;">
                                                        <i class="fas fa-user text-white"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <strong>{{ $carte->eleve->utilisateur->nom }} {{ $carte->eleve->utilisateur->prenom }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $carte->eleve->numero_etudiant }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                {{ $carte->eleve->classe->nom ?? 'Non assigné' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">{{ $carte->type_carte_libelle }}</span>
                                        </td>
                                        <td>{{ $carte->date_emission->format('d/m/Y') }}</td>
                                        <td>
                                            <span class="{{ $carte->date_expiration < now() ? 'text-danger' : 'text-success' }}">
                                                {{ $carte->date_expiration->format('d/m/Y') }}
                                            </span>
                                        </td>
                                        <td>
                                            @php
                                                $badgeClass = match($carte->statut) {
                                                    'active' => 'bg-success',
                                                    'expiree' => 'bg-danger',
                                                    'suspendue' => 'bg-warning',
                                                    'annulee' => 'bg-secondary',
                                                    default => 'bg-secondary'
                                                };
                                            @endphp
                                            <span class="badge {{ $badgeClass }}">{{ $carte->statut_libelle }}</span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('cartes-scolaires.show', $carte) }}" 
                                                   class="btn btn-sm btn-outline-primary" 
                                                   title="Voir">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('cartes-scolaires.edit', $carte) }}" 
                                                   class="btn btn-sm btn-outline-warning" 
                                                   title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="{{ route('cartes-scolaires.imprimer', $carte) }}" 
                                                   class="btn btn-sm btn-outline-info" 
                                                   title="Imprimer" 
                                                   target="_blank">
                                                    <i class="fas fa-print"></i>
                                                </a>
                                                @if($carte->statut === 'active')
                                                    <a href="{{ route('cartes-scolaires.renouveler', $carte) }}" 
                                                       class="btn btn-sm btn-outline-success" 
                                                       title="Renouveler">
                                                        <i class="fas fa-sync"></i>
                                                    </a>
                                                @endif
                                                <form action="{{ route('cartes-scolaires.destroy', $carte) }}" 
                                                      method="POST" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette carte ?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="btn btn-sm btn-outline-danger" 
                                                            title="Supprimer">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-id-card fa-3x mb-3"></i>
                                                <p>Aucune carte scolaire trouvée.</p>
                                                <a href="{{ route('cartes-scolaires.create') }}" class="btn btn-primary">
                                                    <i class="fas fa-plus me-2"></i>Créer la première carte
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($cartes->hasPages())
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div>
                                <small class="text-muted">
                                    Affichage de {{ $cartes->firstItem() ?? 0 }} à {{ $cartes->lastItem() ?? 0 }} sur {{ $cartes->total() }} carte{{ $cartes->total() > 1 ? 's' : '' }} scolaire{{ $cartes->total() > 1 ? 's' : '' }}
                                </small>
                            </div>
                            <div>
                                {{ $cartes->appends(request()->query())->links('vendor.pagination.custom') }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection


