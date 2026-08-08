@extends('layouts.app')

@section('title', 'Mes Messages')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center pt-3 pb-2 mb-3 border-bottom gap-2">
    <div>
        <h1 class="h2 mb-0">
            <i class="fas fa-envelope text-primary me-2"></i>
            Mes Messages
        </h1>
        <p class="text-muted mb-0">Communiquez avec l'administration de l'école</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('parent.notifications.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>
                <span class="d-none d-sm-inline">Nouveau Message</span>
            </a>
        </div>
    </div>
</div>

<!-- Filtres -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('parent.notifications.index') }}">
                    <div class="row g-3">
                        <div class="col-12 col-md-3">
                            <label for="type" class="form-label">Type de message</label>
                            <select name="type" id="type" class="form-select">
                                <option value="">Tous les types</option>
                                <option value="question" {{ request('type') == 'question' ? 'selected' : '' }}>Question</option>
                                <option value="demande" {{ request('type') == 'demande' ? 'selected' : '' }}>Demande</option>
                                <option value="information" {{ request('type') == 'information' ? 'selected' : '' }}>Information</option>
                                <option value="plainte" {{ request('type') == 'plainte' ? 'selected' : '' }}>Plainte</option>
                                <option value="autre" {{ request('type') == 'autre' ? 'selected' : '' }}>Autre</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-3">
                            <label for="statut" class="form-label">Statut</label>
                            <select name="statut" id="statut" class="form-select">
                                <option value="">Tous les statuts</option>
                                <option value="envoyee" {{ request('statut') == 'envoyee' ? 'selected' : '' }}>Envoyé</option>
                                <option value="lue" {{ request('statut') == 'lue' ? 'selected' : '' }}>Lu</option>
                                <option value="repondue" {{ request('statut') == 'repondue' ? 'selected' : '' }}>Répondu</option>
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

<!-- Liste des notifications -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-list me-2"></i>
                    Messages ({{ $notifications->total() }} message(s))
                </h6>
            </div>
            <div class="card-body">
                @if($notifications->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Titre</th>
                                    <th>Destinataire/Expéditeur</th>
                                    <th>Priorité</th>
                                    <th>Statut</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($notifications as $notification)
                                    <tr class="table-row-clickable {{ !$notification->lue && $notification->destinataire_id == auth()->id() ? 'table-warning' : '' }}" data-href="{{ route('parent.notifications.show', $notification) }}" role="button" tabindex="0">
                                        <td>
                                            <span class="badge bg-{{ $notification->type == 'question' ? 'info' : ($notification->type == 'demande' ? 'primary' : ($notification->type == 'plainte' ? 'danger' : 'secondary')) }}">
                                                {{ ucfirst($notification->type) }}
                                            </span>
                                        </td>
                                        <td>
                                            <strong>{{ $notification->titre }}</strong>
                                            @if(!$notification->lue && $notification->destinataire_id == auth()->id())
                                                <span class="badge bg-danger ms-1">Nouveau</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($notification->expediteur_id == auth()->id())
                                                <span class="text-muted">À: {{ $notification->destinataire->nom ?? 'Administration' }}</span>
                                            @else
                                                <span class="text-muted">De: {{ $notification->expediteur->nom ?? 'Administration' }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $notification->priorite == 'urgente' ? 'danger' : ($notification->priorite == 'haute' ? 'warning' : ($notification->priorite == 'moyenne' ? 'info' : 'secondary')) }}">
                                                {{ ucfirst($notification->priorite) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $notification->statut == 'repondue' ? 'success' : ($notification->statut == 'lue' ? 'info' : 'warning') }}">
                                                {{ ucfirst($notification->statut) }}
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $notification->created_at->format('d/m/Y H:i') }}
                                            </small>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $notifications->links('vendor.pagination.custom') }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-envelope fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Aucun message trouvé</h5>
                        <p class="text-muted">Vous n'avez pas encore de messages ou aucun message ne correspond aux critères de recherche.</p>
                        <a href="{{ route('parent.notifications.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>
                            Envoyer un message
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

