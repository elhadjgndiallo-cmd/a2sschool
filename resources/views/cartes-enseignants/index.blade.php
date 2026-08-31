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
            <form method="GET" action="{{ route('cartes-enseignants.index') }}" class="mb-3">
                <div class="row g-2">
                    <div class="col-12 col-sm-6 col-md-2">
                        <select name="statut" id="statut" class="form-select" title="Statut">
                            <option value="">Tous les statuts</option>
                            <option value="active" {{ request('statut') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="expiree" {{ request('statut') == 'expiree' ? 'selected' : '' }}>Expirée</option>
                            <option value="suspendue" {{ request('statut') == 'suspendue' ? 'selected' : '' }}>Suspendue</option>
                            <option value="annulee" {{ request('statut') == 'annulee' ? 'selected' : '' }}>Annulée</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-md-2">
                        <select name="type_carte" id="type_carte" class="form-select" title="Type de carte">
                            <option value="">Tous les types</option>
                            <option value="standard" {{ request('type_carte') == 'standard' ? 'selected' : '' }}>Standard</option>
                            <option value="temporaire" {{ request('type_carte') == 'temporaire' ? 'selected' : '' }}>Temporaire</option>
                            <option value="remplacement" {{ request('type_carte') == 'remplacement' ? 'selected' : '' }}>Remplacement</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <select name="enseignant_id" id="enseignant_id" class="form-select" title="Enseignant">
                            <option value="">Tous les enseignants</option>
                            @foreach($enseignants as $enseignant)
                                <option value="{{ $enseignant->id }}" {{ request('enseignant_id') == $enseignant->id ? 'selected' : '' }}>
                                    {{ $enseignant->utilisateur->nom }} {{ $enseignant->utilisateur->prenom }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <input type="text" name="numero_carte" id="numero_carte" class="form-control"
                               value="{{ request('numero_carte') }}" placeholder="Rechercher par numéro">
                    </div>
                    <div class="col-12 col-sm-6 col-md-2">
                        <div class="d-flex gap-1">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="fas fa-search"></i>
                                <span class="d-none d-sm-inline">Filtrer</span>
                            </button>
                            <a href="{{ route('cartes-enseignants.index') }}" class="btn btn-outline-secondary" title="Effacer">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>

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
                                        <th class="hide-mobile">Numéro</th>
                                        <th>Enseignant</th>
                                        <th class="hide-mobile">Type</th>
                                        <th class="hide-mobile">Date d'émission</th>
                                        <th class="hide-mobile">Date d'expiration</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cartes as $carte)
                                        <tr class="table-row-clickable" data-href="{{ route('cartes-enseignants.show', $carte) }}" role="button" tabindex="0">
                                            <td class="hide-mobile">
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
                                            <td class="hide-mobile">
                                                <span class="badge bg-info">{{ $carte->type_carte_libelle }}</span>
                                            </td>
                                            <td class="hide-mobile">{{ $carte->date_emission->format('d/m/Y') }}</td>
                                            <td class="hide-mobile">
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



