@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Mes Notifications</h4>
                <div class="page-title-right">
                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('notifications.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> Nouvelle notification
                        </a>
                    @endif
                    <form method="POST" action="{{ route('notifications.marquer-toutes-lues') }}" class="d-inline">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="btn btn-success" onclick="return confirm('Êtes-vous sûr de vouloir marquer toutes les notifications comme lues ?')">
                            <i class="fas fa-check-double me-1"></i> Tout marquer comme lu
                        </button>
                    </form>
                    <!-- Bouton supprimer les lues masqué -->
                    <!--
                    <form method="POST" action="{{ route('notifications.supprimer-toutes-lues') }}" class="d-inline" id="supprimer-form">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-warning" onclick="return confirm('Êtes-vous sûr de vouloir supprimer toutes les notifications lues ? Cette action est irréversible.')">
                            <i class="fas fa-trash me-1"></i> Supprimer les lues
                        </button>
                    </form>
                    -->
                </div>
            </div>
        </div>
    </div>

    <!-- Messages de succès/erreur -->
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

    <!-- Statistiques -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card border-primary">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-bell text-primary fa-2x me-3"></i>
                        <div>
                            <h6 class="card-title text-primary mb-1">Total</h6>
                            <h3 class="mt-2 mb-0 text-primary">{{ $stats['total'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-warning">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-bell-slash text-warning fa-2x me-3"></i>
                        <div>
                            <h6 class="card-title text-warning mb-1">Non lues</h6>
                            <h3 class="mt-2 mb-0 text-warning">{{ $stats['non_lues'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-success">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle text-success fa-2x me-3"></i>
                        <div>
                            <h6 class="card-title text-success mb-1">Lues</h6>
                            <h3 class="mt-2 mb-0 text-success">{{ $stats['lues'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-info">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-chart-pie text-info fa-2x me-3"></i>
                        <div>
                            <h6 class="card-title text-info mb-1">Types</h6>
                            <h3 class="mt-2 mb-0 text-info">{{ $parType->count() }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Filtres</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('notifications.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="lue" class="form-label">Statut</label>
                            <select class="form-select" id="lue" name="lue">
                                <option value="">Toutes</option>
                                <option value="0" {{ request('lue') === '0' ? 'selected' : '' }}>Non lues</option>
                                <option value="1" {{ request('lue') === '1' ? 'selected' : '' }}>Lues</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="type" class="form-label">Type</label>
                            <select class="form-select" id="type" name="type">
                                <option value="">Tous les types</option>
                                <option value="info" {{ request('type') === 'info' ? 'selected' : '' }}>Information</option>
                                <option value="success" {{ request('type') === 'success' ? 'selected' : '' }}>Succès</option>
                                <option value="warning" {{ request('type') === 'warning' ? 'selected' : '' }}>Avertissement</option>
                                <option value="danger" {{ request('type') === 'danger' ? 'selected' : '' }}>Erreur</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="per_page" class="form-label">Par page</label>
                            <select class="form-select" id="per_page" name="per_page">
                                <option value="10" {{ request('per_page') == '10' ? 'selected' : '' }}>10</option>
                                <option value="20" {{ request('per_page') == '20' ? 'selected' : '' }}>20</option>
                                <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50</option>
                                <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-filter me-1"></i> Filtrer
                            </button>
                            <a href="{{ route('notifications.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i> Effacer
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des notifications -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Liste des notifications</h5>
                </div>
                <div class="card-body p-0">
                    @if($notifications->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($notifications as $notification)
                                <div class="list-group-item {{ !$notification->lue ? 'bg-light' : '' }}">
                                    <div class="d-flex w-100 justify-content-between align-items-start">
                                        <div class="d-flex align-items-start">
                                            <div class="me-3">
                                                @if($notification->icone)
                                                    <i class="{{ $notification->icone }} fa-2x text-{{ $notification->type }}"></i>
                                                @else
                                                    <i class="fas fa-bell fa-2x text-{{ $notification->type }}"></i>
                                                @endif
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1 {{ !$notification->lue ? 'fw-bold' : '' }}">
                                                    {{ $notification->titre }}
                                                    @if(!$notification->lue)
                                                        <span class="badge bg-warning ms-2">Nouveau</span>
                                                    @endif
                                                </h6>
                                                <p class="mb-1">{{ $notification->message }}</p>
                                                <small class="text-muted">
                                                    <i class="fas fa-clock me-1"></i>
                                                    {{ $notification->created_at->diffForHumans() }}
                                                    ({{ $notification->created_at->format('d/m/Y H:i') }})
                                                </small>
                                            </div>
                                        </div>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                @if(!$notification->lue)
                                                    <li>
                                                        <form method="POST" action="{{ route('notifications.marquer-lue', $notification->id) }}" class="d-inline">
                                                            @csrf
                                                            @method('PUT')
                                                            <button type="submit" class="dropdown-item">
                                                                <i class="fas fa-check me-2"></i>Marquer comme lu
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endif
                                                @if($notification->lien)
                                                    <li>
                                                        <a class="dropdown-item" href="{{ $notification->lien }}">
                                                            <i class="fas fa-external-link-alt me-2"></i>Ouvrir le lien
                                                        </a>
                                                    </li>
                                                @endif
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger" 
                                                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette notification ?')">
                                                            <i class="fas fa-trash me-2"></i>Supprimer
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <!-- Pagination -->
                        <div class="card-footer">
                            {{ $notifications->links('vendor.pagination.custom') }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-bell-slash fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">Aucune notification</h5>
                            <p class="text-muted">Vous n'avez aucune notification pour le moment.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Auto-refresh du compteur toutes les 30 secondes
setInterval(function() {
    fetch('{{ route("notifications.compteur-non-lues") }}')
        .then(response => response.json())
        .then(data => {
            // Mettre à jour le compteur dans la navbar si il existe
            const counter = document.querySelector('.notification-counter');
            if (counter) {
                counter.textContent = data.count;
                counter.style.display = data.count > 0 ? 'inline' : 'none';
            }
        })
        .catch(error => console.log('Erreur lors de la mise à jour du compteur:', error));
}, 30000);
</script>
@endpush



