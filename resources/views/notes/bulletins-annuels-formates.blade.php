@extends('layouts.app')

@section('title', 'Bulletins Annuels Formatés')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-file-alt me-2"></i>
            Bulletins Annuels Formatés
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('notes.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Retour aux Notes
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        @forelse($classes as $classe)
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="card h-100 border-success">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-chart-line fa-3x text-success"></i>
                        </div>
                        <h5 class="card-title">{{ $classe->nom }}</h5>
                        <p class="card-text text-muted">{{ $classe->niveau }}</p>
                        <p class="card-text">
                            <small class="text-muted">
                                <i class="fas fa-users me-1"></i>
                                {{ $classe->eleves->count() }} élève(s)
                            </small>
                        </p>
                        <p class="card-text">
                            <small class="text-info">
                                <i class="fas fa-info-circle me-1"></i>
                                Format : {{ $classe->isPrimaire() ? '3 Trimestres' : '2 Trimestres' }}
                            </small>
                        </p>
                        <div class="mb-2">
                            <label class="form-label small mb-1">Type de Bulletin</label>
                            <select class="form-select form-select-sm bulletin-type-select" style="max-width: 220px; margin: 0 auto;">
                                <option value="formate">Bulletin Annuel Formaté</option>
                                <option value="simple">Bulletin Annuel Simple</option>
                            </select>
                        </div>
                        <a href="{{ route('notes.bulletins.annuels.formates', $classe->id) }}" 
                           class="btn btn-success btn-sm generate-bulletins-annuels"
                           data-base-url="{{ route('notes.bulletins.annuels.formates', $classe->id) }}">
                            <i class="fas fa-file-alt me-1"></i>Générer les bulletins
                        </a>
                        <a href="{{ route('notes.bulletins.annuel.pdf', $classe->id) }}" 
                           class="btn btn-primary btn-sm ms-1">
                            <i class="fas fa-download me-1"></i>Télécharger PDF
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle fa-2x mb-3"></i>
                    <h5>Aucune classe trouvée</h5>
                    <p>Aucune classe n'est disponible pour le moment.</p>
                </div>
            </div>
        @endforelse
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gérer le changement de type de bulletin
    document.querySelectorAll('.bulletin-type-select').forEach(function(select) {
        select.addEventListener('change', function() {
            const type = this.value;
            const generateBtn = this.closest('.card-body').querySelector('.generate-bulletins-annuels');
            const baseUrl = generateBtn.getAttribute('data-base-url');
            
            if (type === 'simple') {
                // Rediriger vers les bulletins annuels simples
                generateBtn.setAttribute('href', baseUrl.replace('/formates', ''));
            } else {
                // Rediriger vers les bulletins annuels formatés
                generateBtn.setAttribute('href', baseUrl);
            }
        });
    });
});
</script>
@endsection
