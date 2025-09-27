@extends('layouts.app')

@section('title', 'Sélectionner une Classe pour les Statistiques')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h2 class="mb-4"><i class="fas fa-chart-bar me-2"></i>Sélectionner une Classe pour les Statistiques</h2>
            <p class="text-muted">Veuillez choisir une classe pour afficher les statistiques de notes et le classement des élèves.</p>

            @if($classes->isEmpty())
                <div class="alert alert-warning" role="alert">
                    Aucune classe disponible pour afficher les statistiques. Veuillez créer des classes d'abord.
                </div>
            @else
                <div class="row">
                    @foreach($classes as $classe)
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 shadow-sm border-primary">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title text-primary">
                                        <i class="fas fa-chalkboard-teacher me-2"></i>{{ $classe->nom }}
                                    </h5>
                                    <p class="card-text text-muted">Niveau: {{ $classe->niveau }}</p>
                                    <p class="card-text">Nombre d'élèves: <strong>{{ $classe->eleves->count() }}</strong></p>
                                    <div class="mt-auto">
                                        <a href="{{ route('notes.statistiques.classe', $classe->id) }}" class="btn btn-primary btn-sm w-100">
                                            <i class="fas fa-chart-line me-2"></i>Voir les Statistiques
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection