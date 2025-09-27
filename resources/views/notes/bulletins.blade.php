@extends('layouts.app')

@section('title', 'Bulletins de Notes')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-file-alt me-2"></i>Bulletins de Notes</h2>
                <a href="{{ route('notes.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour
                </a>
            </div>

            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-0"><i class="fas fa-chalkboard-teacher me-2"></i>Sélectionner une classe</h4>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-4">Choisissez une classe pour générer les bulletins de notes de ses élèves.</p>
                            
                            @if($classes->count() > 0)
                                <div class="row">
                                    @foreach($classes as $classe)
                                        <div class="col-md-6 col-lg-4 mb-3">
                                            <div class="card h-100 border-primary">
                                                <div class="card-body text-center">
                                                    <div class="mb-3">
                                                        <i class="fas fa-chalkboard-teacher fa-3x text-primary"></i>
                                                    </div>
                                                    <h5 class="card-title">{{ $classe->nom }}</h5>
                                                    <p class="card-text text-muted">{{ $classe->niveau }}</p>
                                                    <p class="card-text">
                                                        <small class="text-muted">
                                                            <i class="fas fa-users me-1"></i>
                                                            {{ $classe->eleves->count() }} élève(s)
                                                        </small>
                                                    </p>
                                                    <a href="{{ route('notes.bulletins.classe', $classe->id) }}" 
                                                       class="btn btn-primary btn-sm">
                                                        <i class="fas fa-file-alt me-1"></i>Générer les bulletins
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="fas fa-chalkboard-teacher fa-4x text-muted mb-3"></i>
                                    <h5 class="text-muted">Aucune classe trouvée</h5>
                                    <p class="text-muted">Il n'y a actuellement aucune classe dans le système.</p>
                                    <a href="{{ route('classes.index') }}" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>Créer une classe
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection














