@extends('layouts.app')

@section('title', 'Liste des Parents')

@section('content')
<style>
    /* Amélioration de l'affichage des numéros de téléphone sur mobile/tablette */
    .phone-link {
        display: inline-flex;
        align-items: center;
        padding: 4px 8px;
        border-radius: 4px;
        transition: all 0.3s ease;
    }
    
    .phone-link:hover {
        background-color: #f0f0f0;
        transform: scale(1.05);
    }
    
    .phone-number {
        font-weight: 600;
        letter-spacing: 0.5px;
    }
    
    /* Sur mobile et tablette */
    @media (max-width: 768px) {
        .phone-number {
            font-size: 16px !important;
            font-weight: 700 !important;
            color: #0d6efd !important;
            letter-spacing: 1px;
        }
        
        .phone-link {
            padding: 4px 8px;
            background-color: #e7f3ff;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
        }
        
        .phone-link i {
            font-size: 18px;
            color: #0d6efd;
            margin-right: 8px;
        }
        
        .phone-link:active {
            background-color: #b3d9ff;
            transform: scale(0.98);
        }
    }
    
    /* Sur très petits écrans */
    @media (max-width: 576px) {
        .phone-number {
            font-size: 18px !important;
            font-weight: 700 !important;
        }
        
        .phone-link {
            padding: 6px 10px;
            width: auto;
            justify-content: flex-start;
        }
    }
</style>
<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-users me-2"></i>
            Liste des Parents
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            @if(auth()->user()->hasPermission('eleves.create'))
            <a href="{{ route('parents.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>
                Nouveau Parent
            </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-primary">{{ $stats['total'] }}</h5>
                    <p class="card-text text-muted mb-0">Total Parents</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-success">{{ $stats['actifs'] }}</h5>
                    <p class="card-text text-muted mb-0">Parents Actifs</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-danger">{{ $stats['inactifs'] }}</h5>
                    <p class="card-text text-muted mb-0">Parents Inactifs</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <form method="GET" action="{{ route('parents.index') }}" class="mb-3">
        <div class="row g-2">
            <div class="col-12 col-sm-6 col-md-3">
                <input type="text"
                       class="form-control"
                       id="search"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Nom, prénom, téléphone ou email...">
            </div>
            <div class="col-12 col-sm-6 col-md-2">
                <input type="text"
                       class="form-control"
                       id="profession"
                       name="profession"
                       value="{{ request('profession') }}"
                       placeholder="Profession...">
            </div>
            <div class="col-12 col-sm-6 col-md-2">
                <select class="form-select" id="lien_parente" name="lien_parente" title="Lien de parenté">
                    <option value="">Tous les liens</option>
                    <option value="pere" {{ request('lien_parente') == 'pere' ? 'selected' : '' }}>Père</option>
                    <option value="mere" {{ request('lien_parente') == 'mere' ? 'selected' : '' }}>Mère</option>
                    <option value="tuteur" {{ request('lien_parente') == 'tuteur' ? 'selected' : '' }}>Tuteur</option>
                    <option value="autre" {{ request('lien_parente') == 'autre' ? 'selected' : '' }}>Autre</option>
                </select>
            </div>
            <div class="col-12 col-sm-6 col-md-2">
                <select class="form-select" id="actif" name="actif" title="Statut">
                    <option value="">Tous les statuts</option>
                    <option value="1" {{ request('actif') == '1' ? 'selected' : '' }}>Actifs</option>
                    <option value="0" {{ request('actif') == '0' ? 'selected' : '' }}>Inactifs</option>
                </select>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="d-flex gap-1">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="fas fa-search"></i>
                        <span class="d-none d-sm-inline">Filtrer</span>
                    </button>
                    <a href="{{ route('parents.index') }}" class="btn btn-outline-secondary" title="Réinitialiser">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </div>
        </div>
    </form>

    <!-- Liste des parents -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>
                Parents ({{ $parents->total() }})
            </h5>
        </div>
        <div class="card-body p-0">
            @if($parents->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="col-sticky">Nom</th>
                            <th scope="col">Prénom</th>
                            <th scope="col">Téléphone</th>
                            <th scope="col" class="hide-mobile">Email</th>
                            <th scope="col" class="hide-mobile">Profession</th>
                            <th scope="col" class="hide-sm">Lien</th>
                            <th scope="col" class="text-center hide-sm">Enfants</th>
                            <th scope="col" class="text-center">Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($parents as $parent)
                        <tr class="table-row-clickable" data-href="{{ route('parents.show', $parent->id) }}" role="button" tabindex="0">
                            <td class="col-sticky"><strong>{{ $parent->utilisateur->nom }}</strong></td>
                            <td>{{ $parent->utilisateur->prenom }}</td>
                            <td>
                                @if($parent->utilisateur->telephone)
                                    <a href="tel:{{ $parent->utilisateur->telephone }}" class="phone-link text-decoration-none" onclick="event.stopPropagation()">
                                        <i class="fas fa-phone me-1"></i>
                                        <span class="phone-number">{{ $parent->utilisateur->telephone }}</span>
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="hide-mobile">
                                @if($parent->utilisateur->email)
                                    <a href="mailto:{{ $parent->utilisateur->email }}" class="text-decoration-none" onclick="event.stopPropagation()">
                                        <i class="fas fa-envelope me-1"></i>{{ $parent->utilisateur->email }}
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="hide-mobile">{{ $parent->profession ?? '-' }}</td>
                            <td class="hide-sm">
                                @if($parent->lien_parente == 'pere')
                                    <span class="badge bg-primary">Père</span>
                                @elseif($parent->lien_parente == 'mere')
                                    <span class="badge bg-pink">Mère</span>
                                @elseif($parent->lien_parente == 'tuteur')
                                    <span class="badge bg-info">Tuteur</span>
                                @else
                                    <span class="badge bg-secondary">Autre</span>
                                @endif
                            </td>
                            <td class="text-center hide-sm">
                            </td>
                            <td class="text-center">
                                @if($parent->actif)
                                    <span class="badge bg-success">Actif</span>
                                @else
                                    <span class="badge bg-danger">Inactif</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination des parents -->
            @if($parents->hasPages())
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">
                                Affichage de {{ $parents->firstItem() ?? 0 }} à {{ $parents->lastItem() ?? 0 }} sur {{ $parents->total() }} parent{{ $parents->total() > 1 ? 's' : '' }}
                            </small>
                        </div>
                        <div>
                            {{ $parents->appends(request()->query())->links('vendor.pagination.custom') }}
                        </div>
                    </div>
                </div>
            @endif
            @else
            <div class="text-center py-5">
                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Aucun parent trouvé</h5>
                <p class="text-muted">Aucun parent ne correspond aux critères de recherche.</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

