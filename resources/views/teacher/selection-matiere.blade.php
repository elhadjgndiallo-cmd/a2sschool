@extends('layouts.app')

@section('title', 'Sélectionner une matière - ' . $classe->nom)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-book me-2"></i>
                            Sélectionner une matière - {{ $classe->nom }}
                        </h4>
                        <div>
                            <a href="{{ route('teacher.eleves-classe', $classe) }}" class="btn btn-light btn-sm">
                                <i class="fas fa-arrow-left me-1"></i>
                                Retour
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if($matieres->isEmpty())
                        <div class="alert alert-warning text-center">
                            <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                            <h5>Aucune matière assignée</h5>
                            <p>Vous n'avez pas de matières assignées pour cette classe.</p>
                            <a href="{{ route('teacher.classes') }}" class="btn btn-primary">
                                <i class="fas fa-arrow-left me-1"></i>
                                Retour aux classes
                            </a>
                        </div>
                    @else
                        <div class="row">
                            @foreach($matieres as $matiere)
                                <div class="col-lg-4 col-md-6 mb-4">
                                    <div class="card h-100 shadow-sm">
                                        <div class="card-header bg-light">
                                            <h5 class="card-title mb-0">
                                                <i class="fas fa-book text-primary me-2"></i>
                                                {{ $matiere->nom }}
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <strong>Code :</strong> {{ $matiere->code ?? 'N/A' }}
                                            </div>
                                            
                                            @if($matiere->description)
                                                <div class="mb-3">
                                                    <strong>Description :</strong>
                                                    <p class="text-muted">{{ Str::limit($matiere->description, 100) }}</p>
                                                </div>
                                            @endif
                                            
                                            <div class="mb-3">
                                                <strong>Coefficient :</strong> 
                                                <span class="badge bg-secondary">{{ $matiere->coefficient ?? 'N/A' }}</span>
                                            </div>
                                            
                                            @if($matiere->couleur)
                                                <div class="mb-3">
                                                    <strong>Couleur :</strong>
                                                    <span class="badge" style="background-color: {{ $matiere->couleur }}; color: white;">
                                                        {{ $matiere->couleur }}
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="card-footer bg-transparent">
                                            <div class="d-grid gap-2">
                                                <a href="{{ route('teacher.saisir-notes', ['classe' => $classe, 'matiere' => $matiere]) }}" 
                                                   class="btn btn-primary">
                                                    <i class="fas fa-edit me-2"></i>
                                                    Saisir des notes
                                                </a>
                                                
                                                <a href="{{ route('teacher.historique-notes', ['classe' => $classe, 'matiere' => $matiere]) }}" 
                                                   class="btn btn-outline-info">
                                                    <i class="fas fa-chart-line me-2"></i>
                                                    Voir l'historique
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
    </div>
</div>

@push('styles')
<style>
.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
}

@media (max-width: 768px) {
    .col-lg-4 {
        margin-bottom: 1rem;
    }
}
</style>
@endpush
@endsection
