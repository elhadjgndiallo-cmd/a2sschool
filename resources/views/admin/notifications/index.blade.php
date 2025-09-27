@extends('layouts.app')

@section('title', 'Messages des Parents')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center pt-3 pb-2 mb-3 border-bottom gap-2">
    <div>
        <h1 class="h2 mb-0">
            <i class="fas fa-envelope text-primary me-2"></i>
            Messages des Parents
        </h1>
        <p class="text-muted mb-0">Gérez les communications avec les parents</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('admin.notifications.statistiques') }}" class="btn btn-outline-info">
                <i class="fas fa-chart-bar me-1"></i>
                <span class="d-none d-sm-inline">Statistiques</span>
            </a>
        </div>
    </div>
</div>

<!-- Filtres -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.notifications.index') }}">
                    <div class="row g-3">
                        <div class="col-12 col-md-2">
                            <label for="type" class="form-label">Type</label>
                            <select name="type" id="type" class="form-select">
                                <option value="">Tous les types</option>
                                <option value="question" {{ request('type') == 'question' ? 'selected' : '' }}>Question</option>
                                <option value="demande" {{ request('type') == 'demande' ? 'selected' : '' }}>Demande</option>
                                <option value="information" {{ request('type') == 'information' ? 'selected' : '' }}>Information</option>
                                <option value="plainte" {{ request('type') == 'plainte' ? 'selected' : '' }}>Plainte</option>
                                <option value="autre" {{ request('type') == 'autre' ? 'selected' : '' }}>Autre</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-2">
                            <label for="statut" class="form-label">Statut</label>
                            <select name="statut" id="statut" class="form-select">
                                <option value="">Tous les statuts</option>
                                <option value="envoyee" {{ request('statut') == 'envoyee' ? 'selected' : '' }}>Envoyé</option>
                                <option value="lue" {{ request('statut') == 'lue' ? 'selected' : '' }}>Lu</option>
                                <option value="repondue" {{ request('statut') == 'repondue' ? 'selected' : '' }}>Répondu</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-2">
                            <label for="priorite" class="form-label">Priorité</label>
                            <select name="priorite" id="priorite" class="form-select">
                                <option value="">Toutes les priorités</option>
                                <option value="faible" {{ request('priorite') == 'faible' ? 'selected' : '' }}>Faible</option>
                                <option value="moyenne" {{ request('priorite') == 'moyenne' ? 'selected' : '' }}>Moyenne</option>
                                <option value="haute" {{ request('priorite') == 'haute' ? 'selected' : '' }}>Haute</option>
                                <option value="urgente" {{ request('priorite') == 'urgente' ? 'selected' : '' }}>Urgente</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-2">
                            <label for="expediteur_type" class="form-label">Expéditeur</label>
                            <select name="expediteur_type" id="expediteur_type" class="form-select">
                                <option value="">Tous</option>
                                <option value="parent" {{ request('expediteur_type') == 'parent' ? 'selected' : '' }}>Parents</option>
                            </select>
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

<!-- Liste des messages -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-list me-2"></i>
                    Messages ({{ $messages->total() }} message(s))
                </h6>
            </div>
            <div class="card-body">
                @if($messages->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Expéditeur</th>
                                    <th>Type</th>
                                    <th>Titre</th>
                                    <th>Priorité</th>
                                    <th>Statut</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($messages as $message)
                                    <tr class="{{ !$message->lue && $message->destinataire_id == auth()->id() ? 'table-warning' : '' }}">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-user-circle fa-2x text-primary me-2"></i>
                                                <div>
                                                    <strong>{{ $message->expediteur->nom ?? 'N/A' }} {{ $message->expediteur->prenom ?? '' }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ ucfirst($message->expediteur_type) }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $message->type == 'question' ? 'info' : ($message->type == 'demande' ? 'primary' : ($message->type == 'plainte' ? 'danger' : 'secondary')) }}">
                                                {{ ucfirst($message->type) }}
                                            </span>
                                        </td>
                                        <td>
                                            <strong>{{ $message->titre }}</strong>
                                            @if(!$message->lue && $message->destinataire_id == auth()->id())
                                                <span class="badge bg-danger ms-1">Nouveau</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $message->priorite == 'urgente' ? 'danger' : ($message->priorite == 'haute' ? 'warning' : ($message->priorite == 'moyenne' ? 'info' : 'secondary')) }}">
                                                {{ ucfirst($message->priorite) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $message->statut == 'repondue' ? 'success' : ($message->statut == 'lue' ? 'info' : 'warning') }}">
                                                {{ ucfirst($message->statut) }}
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $message->created_at->format('d/m/Y H:i') }}
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('admin.notifications.show', $message) }}" 
                                                   class="btn btn-outline-primary" title="Voir">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <form action="{{ route('admin.notifications.destroy', $message) }}" 
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce message ?')">
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
                    <div class="d-flex justify-content-center mt-4">
                        {{ $messages->links('vendor.pagination.custom') }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-envelope fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Aucun message trouvé</h5>
                        <p class="text-muted">Aucun message ne correspond aux critères de recherche.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

