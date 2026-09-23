@extends('layouts.app')

@section('title', 'Fusionner les classes')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-object-group me-2"></i>
        Fusionner les classes
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('notes.mensuel.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Retour
        </a>
    </div>
</div>

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<form method="GET" action="{{ route('notes.mensuel.resultats-fusion') }}" id="form-fusion">
    <div class="row g-2 mb-3">
        @if(isset($anneeScolaireActive))
        <div class="col-12 col-sm-6 col-md-3">
            <input type="text" class="form-control" value="{{ $anneeScolaireActive->nom }}" readonly title="Année scolaire">
        </div>
        @endif
        <div class="col-12 col-sm-6 col-md-3">
            <select name="mois" id="mois" class="form-select" title="Mois">
                @foreach($moisListe as $num => $nom)
                <option value="{{ $num }}" {{ $mois == $num ? 'selected' : '' }}>{{ $nom }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <select name="annee" id="annee" class="form-select" title="Année">
                @foreach(($anneesDisponibles ?? [date('Y')]) as $anneeOption)
                <option value="{{ $anneeOption }}" {{ $annee == $anneeOption ? 'selected' : '' }}>{{ $anneeOption }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="d-flex gap-1">
                <button type="submit" class="btn btn-primary flex-fill" {{ $classes->count() < 2 ? 'disabled' : '' }}>
                    <i class="fas fa-trophy me-1"></i>
                    <span class="d-none d-sm-inline">Voir le classement</span>
                </button>
            </div>
        </div>
    </div>
    <p class="text-danger small mb-3 d-none" id="fusion-error">
        Veuillez sélectionner au moins deux classes.
    </p>

    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-chalkboard-teacher me-2"></i>
                Classes
            </h5>
            @if($classes->count() > 0)
            <button type="button" class="btn btn-sm btn-outline-secondary" id="toggle-all-classes">
                Tout sélectionner
            </button>
            @endif
        </div>
        <div class="card-body">
            @if($classes->count() >= 2)
            @php
                $selectedIds = collect(old('classes', $classesSelectionnees))->map(fn ($id) => (int) $id)->all();
            @endphp
            <div class="row">
                @foreach($classes as $classe)
                <div class="col-md-6 col-lg-4 mb-2">
                    <div class="form-check border rounded p-3 h-100">
                        <input class="form-check-input classe-checkbox" type="checkbox"
                               name="classes[]" value="{{ $classe->id }}" id="classe-{{ $classe->id }}"
                               {{ in_array((int) $classe->id, $selectedIds, true) ? 'checked' : '' }}>
                        <label class="form-check-label w-100" for="classe-{{ $classe->id }}">
                            <strong>{{ $classe->nom }}</strong>
                            <div class="small text-muted">{{ $classe->eleves->count() }} élèves</div>
                        </label>
                    </div>
                </div>
                @endforeach
            </div>
            @elseif($classes->count() === 1)
            <div class="alert alert-warning mb-0">
                Une seule classe est accessible. La fusion nécessite au moins deux classes.
            </div>
            @else
            <div class="text-center py-4">
                <i class="fas fa-chalkboard-teacher fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Aucune classe disponible</h5>
                <p class="text-muted mb-0">Vous n’avez accès à aucune classe pour les tests mensuels.</p>
            </div>
            @endif
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('form-fusion');
    const error = document.getElementById('fusion-error');
    const toggle = document.getElementById('toggle-all-classes');
    const boxes = () => document.querySelectorAll('.classe-checkbox');

    if (form) {
        form.addEventListener('submit', function (e) {
            const checked = document.querySelectorAll('.classe-checkbox:checked').length;
            if (checked < 2) {
                e.preventDefault();
                if (error) error.classList.remove('d-none');
            }
        });
    }

    if (toggle) {
        toggle.addEventListener('click', function () {
            const all = boxes();
            const allChecked = Array.from(all).every(cb => cb.checked);
            all.forEach(cb => { cb.checked = !allChecked; });
            toggle.textContent = allChecked ? 'Tout sélectionner' : 'Tout désélectionner';
        });
    }
});
</script>
@endpush
