@extends('layouts.app')

@section('title', 'Gestion des Cartes Enseignants')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-id-card me-2"></i>Gestion des Cartes Enseignants</h2>
                <a href="{{ route('cartes-enseignants.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Nouvelle Carte
                </a>
            </div>

            <!-- Messages de session -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Filtres -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filtres</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('cartes-enseignants.index') }}">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="statut" class="form-label">Statut</label>
                                <select name="statut" id="statut" class="form-select">
                                    <option value="">Tous les statuts</option>
                                    <option value="active" {{ request('statut') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="expiree" {{ request('statut') == 'expiree' ? 'selected' : '' }}>Expirée</option>
                                    <option value="suspendue" {{ request('statut') == 'suspendue' ? 'selected' : '' }}>Suspendue</option>
                                    <option value="annulee" {{ request('statut') == 'annulee' ? 'selected' : '' }}>Annulée</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="type_carte" class="form-label">Type de carte</label>
                                <select name="type_carte" id="type_carte" class="form-select">
                                    <option value="">Tous les types</option>
                                    <option value="standard" {{ request('type_carte') == 'standard' ? 'selected' : '' }}>Standard</option>
                                    <option value="temporaire" {{ request('type_carte') == 'temporaire' ? 'selected' : '' }}>Temporaire</option>
                                    <option value="remplacement" {{ request('type_carte') == 'remplacement' ? 'selected' : '' }}>Remplacement</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="enseignant_id" class="form-label">Enseignant</label>
                                <select name="enseignant_id" id="enseignant_id" class="form-select">
                                    <option value="">Tous les enseignants</option>
                                    @foreach($enseignants as $enseignant)
                                        <option value="{{ $enseignant->id }}" {{ request('enseignant_id') == $enseignant->id ? 'selected' : '' }}>
                                            {{ $enseignant->utilisateur->nom }} {{ $enseignant->utilisateur->prenom }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="numero_carte" class="form-label">Numéro de carte</label>
                                <input type="text" name="numero_carte" id="numero_carte" class="form-control" 
                                       value="{{ request('numero_carte') }}" placeholder="Rechercher par numéro">
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-search me-2"></i>Filtrer
                                </button>
                                <a href="{{ route('cartes-enseignants.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>Effacer
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tableau des cartes -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Liste des Cartes Enseignants ({{ $cartes->total() }} cartes)</h5>
                </div>
                <div class="card-body">
                    @if($cartes->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Numéro</th>
                                        <th>Enseignant</th>
                                        <th>Type</th>
                                        <th>Date d'émission</th>
                                        <th>Date d'expiration</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cartes as $carte)
                                        <tr>
                                            <td>
                                                <strong>{{ $carte->numero_carte }}</strong>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($carte->enseignant->utilisateur->photo_profil && Storage::disk('public')->exists($carte->enseignant->utilisateur->photo_profil))
                                                        <img src="{{ asset('storage/' . $carte->enseignant->utilisateur->photo_profil) }}" 
                                                             alt="Photo enseignant" 
                                                             class="rounded-circle me-2" 
                                                             style="width: 40px; height: 40px; object-fit: cover;">
                                                    @else
                                                        <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                                            {{ substr($carte->enseignant->utilisateur->nom, 0, 1) }}
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <div class="fw-bold">{{ $carte->enseignant->utilisateur->nom }} {{ $carte->enseignant->utilisateur->prenom }}</div>
                                                        <small class="text-muted">{{ $carte->enseignant->numero_employe }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $carte->type_carte_libelle }}</span>
                                            </td>
                                            <td>{{ $carte->date_emission->format('d/m/Y') }}</td>
                                            <td>
                                                <span class="{{ $carte->date_expiration < now() ? 'text-danger' : '' }}">
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
                                                    <a href="{{ route('cartes-enseignants.show', $carte) }}" 
                                                       class="btn btn-sm btn-outline-primary" title="Voir">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('cartes-enseignants.edit', $carte) }}" 
                                                       class="btn btn-sm btn-outline-warning" title="Modifier">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="{{ route('cartes-enseignants.imprimer', $carte) }}" 
                                                       class="btn btn-sm btn-outline-success" title="Imprimer" target="_blank">
                                                        <i class="fas fa-print"></i>
                                                    </a>
                                                    @if($carte->statut === 'active')
                                                        <a href="{{ route('cartes-enseignants.renouveler', $carte) }}" 
                                                           class="btn btn-sm btn-outline-info" title="Renouveler">
                                                            <i class="fas fa-sync"></i>
                                                        </a>
                                                    @endif
                                                    <form action="{{ route('cartes-enseignants.destroy', $carte) }}" 
                                                          method="POST" class="d-inline" 
                                                          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette carte ?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
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
                        <div class="d-flex justify-content-center mt-4">
                            {{ $cartes->appends(request()->query())->links('vendor.pagination.custom') }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-id-card fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Aucune carte enseignant trouvée</h5>
                            <p class="text-muted">Commencez par créer une nouvelle carte enseignant.</p>
                            <a href="{{ route('cartes-enseignants.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Créer une carte
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection



