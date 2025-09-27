@extends('layouts.app')

@section('title', 'Détails du Message')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center pt-3 pb-2 mb-3 border-bottom gap-2">
    <div>
        <h1 class="h2 mb-0">
            <i class="fas fa-envelope-open text-primary me-2"></i>
            Détails du Message
        </h1>
        <p class="text-muted mb-0">Consultez et répondez aux messages</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('parent.notifications.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                <span class="d-none d-sm-inline">Retour</span>
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 col-lg-8">
        <!-- Message principal -->
        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-envelope me-2"></i>
                        {{ $notification->titre }}
                    </h6>
                    <div>
                        <span class="badge bg-{{ $notification->type == 'question' ? 'info' : ($notification->type == 'demande' ? 'primary' : ($notification->type == 'plainte' ? 'danger' : 'secondary')) }}">
                            {{ ucfirst($notification->type) }}
                        </span>
                        <span class="badge bg-{{ $notification->priorite == 'urgente' ? 'danger' : ($notification->priorite == 'haute' ? 'warning' : ($notification->priorite == 'moyenne' ? 'info' : 'secondary')) }}">
                            {{ ucfirst($notification->priorite) }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>De :</strong> {{ $notification->expediteur->nom ?? 'Administration' }}<br>
                        <strong>À :</strong> {{ $notification->destinataire->nom ?? 'Administration' }}
                    </div>
                    <div class="col-md-6 text-md-end">
                        <strong>Date :</strong> {{ $notification->created_at->format('d/m/Y à H:i') }}<br>
                        <strong>Statut :</strong> 
                        <span class="badge bg-{{ $notification->statut == 'repondue' ? 'success' : ($notification->statut == 'lue' ? 'info' : 'warning') }}">
                            {{ ucfirst($notification->statut) }}
                        </span>
                    </div>
                </div>
                
                <div class="border rounded p-3 bg-light">
                    {!! nl2br(e($notification->message)) !!}
                </div>
            </div>
        </div>

        <!-- Réponses -->
        @if($reponses->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-comments me-2"></i>
                        Réponses ({{ $reponses->count() }})
                    </h6>
                </div>
                <div class="card-body">
                    @foreach($reponses as $reponse)
                        <div class="border rounded p-3 mb-3 {{ $reponse->expediteur_id == auth()->id() ? 'bg-primary bg-opacity-10' : 'bg-light' }}">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <strong>{{ $reponse->expediteur->nom ?? 'Administration' }}</strong>
                                    <span class="text-muted">({{ $reponse->created_at->format('d/m/Y à H:i') }})</span>
                                </div>
                                @if($reponse->expediteur_id == auth()->id())
                                    <span class="badge bg-primary">Vous</span>
                                @else
                                    <span class="badge bg-secondary">Administration</span>
                                @endif
                            </div>
                            <div>
                                {!! nl2br(e($reponse->message)) !!}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Formulaire de réponse -->
        @if($notification->destinataire_id == auth()->id() && $notification->statut != 'repondue')
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-reply me-2"></i>
                        Répondre
                    </h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('parent.notifications.repondre', $notification) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="message" class="form-label">Votre réponse</label>
                            <textarea name="message" id="message" rows="5" 
                                      class="form-control @error('message') is-invalid @enderror" 
                                      placeholder="Tapez votre réponse ici..." required>{{ old('message') }}</textarea>
                            @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-1"></i>
                                Envoyer la réponse
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>

    <div class="col-12 col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Informations
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Type :</strong><br>
                    <span class="badge bg-{{ $notification->type == 'question' ? 'info' : ($notification->type == 'demande' ? 'primary' : ($notification->type == 'plainte' ? 'danger' : 'secondary')) }}">
                        {{ ucfirst($notification->type) }}
                    </span>
                </div>

                <div class="mb-3">
                    <strong>Priorité :</strong><br>
                    <span class="badge bg-{{ $notification->priorite == 'urgente' ? 'danger' : ($notification->priorite == 'haute' ? 'warning' : ($notification->priorite == 'moyenne' ? 'info' : 'secondary')) }}">
                        {{ ucfirst($notification->priorite) }}
                    </span>
                </div>

                <div class="mb-3">
                    <strong>Statut :</strong><br>
                    <span class="badge bg-{{ $notification->statut == 'repondue' ? 'success' : ($notification->statut == 'lue' ? 'info' : 'warning') }}">
                        {{ ucfirst($notification->statut) }}
                    </span>
                </div>

                <div class="mb-3">
                    <strong>Date d'envoi :</strong><br>
                    <small class="text-muted">{{ $notification->created_at->format('d/m/Y à H:i') }}</small>
                </div>

                @if($notification->updated_at != $notification->created_at)
                    <div class="mb-3">
                        <strong>Dernière modification :</strong><br>
                        <small class="text-muted">{{ $notification->updated_at->format('d/m/Y à H:i') }}</small>
                    </div>
                @endif

                @if($notification->expediteur_id == auth()->id())
                    <div class="mt-4">
                        <form action="{{ route('parent.notifications.destroy', $notification) }}" 
                              method="POST" 
                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce message ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                <i class="fas fa-trash me-1"></i>
                                Supprimer ce message
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
