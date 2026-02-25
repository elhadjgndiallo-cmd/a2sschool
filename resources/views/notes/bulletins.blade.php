@extends('layouts.app')

@section('title', 'Bulletins de Notes')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-file-alt me-2"></i>Bulletins Scolaires</h2>
        <a href="{{ route('notes.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Retour aux Notes
        </a>
    </div>

            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-0"><i class="fas fa-chalkboard-teacher me-2"></i>Sélectionner une classe</h4>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-4">Choisissez une classe et une période pour générer les bulletins de notes de ses élèves.</p>

                            <div class="row mb-4">
                                <div class="col-12 col-md-6">
                                    <label for="periode-global" class="form-label">Période</label>
                                    <select id="periode-global" class="form-select">
                                        <option value="trimestre1">Trimestre 1</option>
                                        <option value="trimestre2">Trimestre 2</option>
                                        <option value="trimestre3">Trimestre 3</option>
                                    </select>
                                    <small class="text-muted">Ce choix sera appliqué à tous les boutons "Générer les bulletins".</small>
                                </div>
                            </div>
                            
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
                                                    <div class="mb-2">
                                                        <label class="form-label small mb-1">Période</label>
                                                        <select class="form-select form-select-sm periode-select" style="max-width: 220px; margin: 0 auto;">
                                                            <option value="trimestre1">Trimestre 1</option>
                                                            <option value="trimestre2">Trimestre 2</option>
                                                            <option value="trimestre3">Trimestre 3</option>
                                                        </select>
                                                    </div>
                                                    <a href="{{ route('notes.bulletins.classe', $classe->id) }}" 
                                                       class="btn btn-primary btn-sm generate-bulletins"
                                                       data-base-url="{{ route('notes.bulletins.classe', $classe->id) }}">
                                                        <i class="fas fa-file-alt me-1"></i>Générer les bulletins
                                                    </a>
                                                    <a href="{{ route('notes.bulletins.annuels.formates', $classe->id) }}" 
                                                       class="btn btn-success btn-sm generate-bulletins-annuels"
                                                       data-base-url="{{ route('notes.bulletins.annuels.formates', $classe->id) }}">
                                                        <i class="fas fa-file-alt me-1"></i>Générer Bulletin Annuel
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
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const periodeSelect = document.getElementById('periode-global');
    document.querySelectorAll('.generate-bulletins').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const base = this.getAttribute('data-base-url');
            const card = this.closest('.card-body');
            const localSelect = card ? card.querySelector('.periode-select') : null;
            const periode = localSelect && localSelect.value ? localSelect.value : (periodeSelect ? periodeSelect.value : 'trimestre1');
            window.location.href = base + '?periode=' + encodeURIComponent(periode);
        });
    });
});
</script>
@endpush
@endsection














