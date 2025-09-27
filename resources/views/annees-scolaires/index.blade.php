@extends('layouts.app')

@section('title', 'Années Scolaires')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-calendar-alt text-primary me-2"></i>
                    Années Scolaires
                </h1>
                <a href="{{ route('annees-scolaires.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Nouvelle Année Scolaire
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($annees->count() > 0)
                <!-- Année scolaire active -->
                @php $anneeActive = $annees->firstWhere('active', true); @endphp
                @if($anneeActive)
                <div class="card shadow mb-4 border-success">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-star me-2"></i>
                            Année Scolaire Active
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h4 class="text-success mb-3">{{ $anneeActive->nom }}</h4>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-2">
                                            <i class="fas fa-calendar-check me-2 text-success"></i>
                                            <strong>Date de début :</strong> 
                                            {{ $anneeActive->date_debut->format('d/m/Y') }}
                                        </p>
                                        <p class="mb-2">
                                            <i class="fas fa-calendar-times me-2 text-danger"></i>
                                            <strong>Date de fin :</strong> 
                                            {{ $anneeActive->date_fin->format('d/m/Y') }}
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-2">
                                            <i class="fas fa-info-circle me-2 text-info"></i>
                                            <strong>Statut :</strong> 
                                            @php
                                                $statut = $anneeActive->statut;
                                                $badgeClass = match($statut) {
                                                    'en_cours' => 'bg-primary',
                                                    'à_venir' => 'bg-info',
                                                    'terminee' => 'bg-secondary',
                                                    default => 'bg-light text-dark'
                                                };
                                                $statutText = match($statut) {
                                                    'en_cours' => 'En cours',
                                                    'à_venir' => 'À venir',
                                                    'terminee' => 'Terminée',
                                                    default => 'Indéterminé'
                                                };
                                            @endphp
                                            <span class="badge {{ $badgeClass }} fs-6">{{ $statutText }}</span>
                                        </p>
                                        @if($anneeActive->description)
                                        <p class="mb-0">
                                            <i class="fas fa-quote-left me-2 text-muted"></i>
                                            <strong>Description :</strong><br>
                                            <em>{{ $anneeActive->description }}</em>
                                        </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="d-grid gap-2">
                                    <a href="{{ route('annees-scolaires.edit', $anneeActive) }}" class="btn btn-outline-primary">
                                        <i class="fas fa-edit me-1"></i>Modifier cette année
                                    </a>
                                    <span class="badge bg-success fs-6 py-2">
                                        <i class="fas fa-check-circle me-1"></i>ANNÉE ACTIVE
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Liste de toutes les années scolaires -->
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <div class="row align-items-center">
                            <div class="col">
                                <h5 class="mb-0">
                                    <i class="fas fa-list me-2"></i>
                                    Toutes les Années Scolaires
                                </h5>
                            </div>
                            <div class="col-auto">
                                <span class="badge bg-light text-dark">
                                    {{ $annees->count() }} année{{ $annees->count() > 1 ? 's' : '' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th width="5%" class="text-center">#</th>
                                        <th width="25%">Année Scolaire</th>
                                        <th width="15%">Date de Début</th>
                                        <th width="15%">Date de Fin</th>
                                        <th width="12%">Statut</th>
                                        <th width="12%">État</th>
                                        <th width="16%" class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($annees as $index => $annee)
                                    <tr class="{{ $annee->active ? 'table-success' : '' }}">
                                        <td class="text-center">
                                            <strong>{{ $index + 1 }}</strong>
                                        </td>
                                        <td>
                                            <div>
                                                <strong class="text-dark">{{ $annee->nom }}</strong>
                                                @if($annee->active)
                                                    <span class="badge bg-success ms-2">ACTIVE</span>
                                                @endif
                                            </div>
                                            @if($annee->description)
                                                <small class="text-muted">{{ Str::limit($annee->description, 50) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <i class="fas fa-calendar-check me-1 text-success"></i>
                                            <strong>{{ $annee->date_debut->format('d/m/Y') }}</strong>
                                        </td>
                                        <td>
                                            <i class="fas fa-calendar-times me-1 text-danger"></i>
                                            <strong>{{ $annee->date_fin->format('d/m/Y') }}</strong>
                                        </td>
                                        <td>
                                            @php
                                                $statut = $annee->statut;
                                                $badgeClass = match($statut) {
                                                    'en_cours' => 'bg-primary',
                                                    'à_venir' => 'bg-info',
                                                    'terminee' => 'bg-secondary',
                                                    default => 'bg-light text-dark'
                                                };
                                                $statutText = match($statut) {
                                                    'en_cours' => 'En cours',
                                                    'à_venir' => 'À venir',
                                                    'terminee' => 'Terminée',
                                                    default => 'Indéterminé'
                                                };
                                            @endphp
                                            <span class="badge {{ $badgeClass }}">{{ $statutText }}</span>
                                        </td>
                                        <td>
                                            @if($annee->active)
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check-circle me-1"></i>Active
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">
                                                    <i class="fas fa-pause-circle me-1"></i>Inactive
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                @if(!$annee->active)
                                                    <form action="{{ route('annees-scolaires.activer', $annee) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-success" 
                                                                onclick="return confirm('Activer cette année scolaire ?\n\nCela désactivera automatiquement l\'année actuellement active.')"
                                                                title="Activer cette année">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <button class="btn btn-success" disabled title="Année actuellement active">
                                                        <i class="fas fa-star"></i>
                                                    </button>
                                                @endif
                                                
                                                <a href="{{ route('annees-scolaires.edit', $annee) }}" 
                                                   class="btn btn-outline-primary"
                                                   title="Modifier cette année">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                
                                                @if(!$annee->active)
                                                    <form action="{{ route('annees-scolaires.destroy', $annee) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger" 
                                                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette année scolaire ?\n\nCette action est irréversible.')"
                                                                title="Supprimer cette année">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <button class="btn btn-outline-secondary" disabled title="Impossible de supprimer l'année active">
                                                        <i class="fas fa-lock"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    @if($annees->hasPages())
                        <div class="card-footer bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted">
                                        Affichage de {{ $annees->firstItem() ?? 0 }} à {{ $annees->lastItem() ?? 0 }} sur {{ $annees->total() }} année{{ $annees->total() > 1 ? 's' : '' }} scolaire{{ $annees->total() > 1 ? 's' : '' }}
                                    </small>
                                </div>
                                <div>
                                    {{ $annees->appends(request()->query())->links('vendor.pagination.custom') }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @else
                <!-- Aucune année scolaire -->
                <div class="card shadow">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-calendar-times fa-4x text-muted mb-4"></i>
                        <h4 class="text-muted mb-3">Aucune année scolaire</h4>
                        <p class="text-muted mb-4">
                            Vous n'avez pas encore créé d'année scolaire.<br>
                            Commencez par créer votre première année scolaire pour organiser votre établissement.
                        </p>
                        <a href="{{ route('annees-scolaires.create') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-plus me-2"></i>Créer la Première Année Scolaire
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
