@extends('layouts.app')

@section('title', 'Mes absences')
@include('absences-enseignants._styles')

@section('content')
<div class="ae-page">
    <div class="ae-hero d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h1><i class="fas fa-user-clock me-2"></i>Mes absences</h1>
            <p>Déclarez un congé, une absence ou un retard. L’administration validera votre demande.</p>
        </div>
        <a href="{{ route('teacher.mes-absences.declarer') }}" class="btn btn-light">
            <i class="fas fa-plus me-1"></i> Nouvelle déclaration
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card ae-stat"><div class="ae-stat-body">
                <div class="ae-stat-icon wait"><i class="fas fa-hourglass-half"></i></div>
                <div><h3>{{ $compteurs['en_attente'] }}</h3><small>En attente</small></div>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card ae-stat"><div class="ae-stat-body">
                <div class="ae-stat-icon ok"><i class="fas fa-check"></i></div>
                <div><h3>{{ $compteurs['justifiees'] }}</h3><small>Validées</small></div>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card ae-stat"><div class="ae-stat-body">
                <div class="ae-stat-icon open"><i class="fas fa-exclamation"></i></div>
                <div><h3>{{ $compteurs['non_justifiees'] }}</h3><small>Non justifiées</small></div>
            </div></div>
        </div>
    </div>

    <div class="card ae-card">
        <div class="card-body p-0">
            @forelse($absences as $absence)
            <div class="ae-row px-3 py-3 border-bottom">
                <div class="d-flex flex-wrap justify-content-between gap-2 align-items-start">
                    <div>
                        <div class="fw-semibold">{{ $absence->periode_label }} · {{ $absence->type_label }}</div>
                        <div class="text-muted small mt-1">{{ $absence->motif ?: $absence->motif_categorie_label }}</div>
                        <div class="text-muted small">{{ $absence->origine_label }}</div>
                        @if($absence->commentaire_admin && $absence->statut === 'refusee')
                            <div class="text-danger small mt-1">{{ $absence->commentaire_admin }}</div>
                        @endif
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge ae-badge bg-{{ $absence->statut_badge }}">{{ $absence->statut_label }}</span>
                        @if($absence->peutEtreAnnulee())
                        <form method="POST" action="{{ route('teacher.mes-absences.annuler', $absence) }}" onsubmit="return confirm('Annuler cette déclaration ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Annuler</button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="ae-empty">
                <i class="fas fa-clipboard-list"></i>
                Vous n’avez aucune absence enregistrée.
                <div class="mt-3">
                    <a href="{{ route('teacher.mes-absences.declarer') }}" class="btn btn-primary">Faire une déclaration</a>
                </div>
            </div>
            @endforelse
        </div>
        @if($absences->hasPages())
        <div class="card-body border-top">{{ $absences->links('vendor.pagination.custom') }}</div>
        @endif
    </div>
</div>
@endsection
