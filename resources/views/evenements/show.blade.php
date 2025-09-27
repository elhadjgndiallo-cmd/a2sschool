@extends('layouts.app')

@section('title', 'Détails de l\'Événement')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-calendar-alt mr-2"></i>
                        Détails de l'Événement
                    </h3>
                    <div>
                        <a href="{{ route('evenements.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i>
                            Retour à la liste
                        </a>
                        @if(auth()->user()->hasPermission('evenements.edit') && (auth()->user()->hasPermission('evenements.manage_all') || $evenement->createur_id === Auth::id()))
                            <a href="{{ route('evenements.edit', $evenement->id) }}" class="btn btn-warning">
                                <i class="fas fa-edit mr-1"></i>
                                Modifier
                            </a>
                        @endif
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5 class="text-dark mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="color-indicator" style="background-color: {{ $evenement->couleur }}; width: 20px; height: 20px; border-radius: 4px; margin-right: 10px; border: 1px solid #dee2e6;"></div>
                                            <span style="color: #333; font-weight: 600;">{{ $evenement->titre }}</span>
                                        </div>
                                    </h5>
                                    
                                    <div class="mb-3">
                                        <strong>Type :</strong>
                                        <span class="badge badge-{{ $evenement->type == 'examen' ? 'danger' : ($evenement->type == 'cours' ? 'primary' : 'info') }} ml-2">
                                            {{ ucfirst($evenement->type) }}
                                        </span>
                                    </div>
                                    
                                    @if($evenement->description)
                                        <div class="mb-3">
                                            <strong>Description :</strong>
                                            <p class="mt-1">{{ $evenement->description }}</p>
                                        </div>
                                    @endif
                                    
                                    @if($evenement->lieu)
                                        <div class="mb-3">
                                            <strong>Lieu :</strong>
                                            <span class="ml-2">{{ $evenement->lieu }}</span>
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <strong>Date de début :</strong>
                                        <span class="ml-2">{{ $evenement->date_debut->format('d/m/Y') }}</span>
                                        @if($evenement->heure_debut)
                                            <span class="ml-1">à {{ $evenement->heure_debut }}</span>
                                        @endif
                                    </div>
                                    
                                    <div class="mb-3">
                                        <strong>Date de fin :</strong>
                                        <span class="ml-2">{{ $evenement->date_fin->format('d/m/Y') }}</span>
                                        @if($evenement->heure_fin)
                                            <span class="ml-1">à {{ $evenement->heure_fin }}</span>
                                        @endif
                                    </div>
                                    
                                    @if($evenement->journee_entiere)
                                        <div class="mb-3">
                                            <span class="badge badge-info">Journée entière</span>
                                        </div>
                                    @endif
                                    
                                    <div class="mb-3">
                                        <strong>Visibilité :</strong>
                                        @if($evenement->public)
                                            <span class="badge badge-success ml-2">Public</span>
                                        @else
                                            <span class="badge badge-warning ml-2">Privé</span>
                                        @endif
                                    </div>
                                    
                                    @if($evenement->rappel)
                                        <div class="mb-3">
                                            <strong>Rappel :</strong>
                                            <span class="ml-2">{{ $evenement->rappel }} minutes avant</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Informations</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-2">
                                        <strong>Classe :</strong>
                                        <span class="ml-2">
                                            {{ $evenement->classe ? $evenement->classe->nom : 'Toutes les classes' }}
                                        </span>
                                    </div>
                                    
                                    <div class="mb-2">
                                        <strong>Créé par :</strong>
                                        <span class="ml-2">{{ $evenement->createur->nom ?? 'N/A' }}</span>
                                    </div>
                                    
                                    <div class="mb-2">
                                        <strong>Créé le :</strong>
                                        <span class="ml-2">{{ $evenement->created_at->format('d/m/Y à H:i') }}</span>
                                    </div>
                                    
                                    @if($evenement->updated_at != $evenement->created_at)
                                        <div class="mb-2">
                                            <strong>Modifié le :</strong>
                                            <span class="ml-2">{{ $evenement->updated_at->format('d/m/Y à H:i') }}</span>
                                        </div>
                                    @endif
                                    
                                    <div class="mb-2">
                                        <strong>Statut :</strong>
                                        @if($evenement->estAVenir())
                                            <span class="badge badge-success ml-2">À venir</span>
                                        @elseif($evenement->estEnCours())
                                            <span class="badge badge-warning ml-2">En cours</span>
                                        @else
                                            <span class="badge badge-secondary ml-2">Terminé</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @if(auth()->user()->hasPermission('evenements.edit') && (auth()->user()->hasPermission('evenements.manage_all') || $evenement->createur_id === Auth::id()))
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card border-warning">
                                    <div class="card-header bg-warning text-dark">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            Actions
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="btn-group" role="group">
                                            @if(auth()->user()->hasPermission('evenements.edit'))
                                                <a href="{{ route('evenements.edit', $evenement->id) }}" class="btn btn-warning">
                                                    <i class="fas fa-edit mr-1"></i>
                                                    Modifier
                                                </a>
                                            @endif
                                            @if(auth()->user()->hasPermission('evenements.delete'))
                                                <form action="{{ route('evenements.destroy', $evenement->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet événement ? Cette action est irréversible.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger">
                                                        <i class="fas fa-trash mr-1"></i>
                                                        Supprimer
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<style>
/* Amélioration de la lisibilité des textes */
.color-indicator {
    border: 1px solid #dee2e6 !important;
}

.card-title {
    color: #333 !important;
}

.text-muted {
    color: #6c757d !important;
}

/* Amélioration des badges */
.badge {
    font-weight: 500;
}

/* Amélioration des boutons */
.btn {
    font-weight: 500;
}

/* Amélioration du contenu */
.card-body {
    color: #333;
}

.card-body strong {
    color: #495057;
    font-weight: 600;
}
</style>
