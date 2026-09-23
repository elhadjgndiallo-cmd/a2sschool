@extends('layouts.app')

@section('title', 'Absences — ' . ($enseignant->nom_complet ?? 'Enseignant'))
@include('absences-enseignants._styles')

@section('content')
<div class="ae-page">
    <div class="ae-hero d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h1>Absences de {{ $enseignant->nom_complet }}</h1>
            <p>{{ $enseignant->specialite ?: 'Enseignant' }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('enseignants.show', $enseignant) }}" class="btn btn-outline-light">Fiche</a>
            <a href="{{ route('absences-enseignants.index') }}" class="btn btn-outline-light">Liste</a>
        </div>
    </div>

    <div class="card ae-card">
        <div class="card-body p-0">
            @forelse($absences as $absence)
            <a href="{{ route('absences-enseignants.show', $absence) }}" class="text-decoration-none text-dark d-block ae-row px-3 py-3 border-bottom">
                <div class="d-flex justify-content-between align-items-center gap-3">
                    <div>
                        <div class="fw-semibold">{{ $absence->periode_label }} · {{ $absence->type_label }}</div>
                        <div class="text-muted small">{{ $absence->origine_label }}@if($absence->motif) — {{ \Illuminate\Support\Str::limit($absence->motif, 80) }}@endif</div>
                    </div>
                    <span class="badge ae-badge bg-{{ $absence->statut_badge }}">{{ $absence->statut_label }}</span>
                </div>
            </a>
            @empty
            <div class="ae-empty">
                <i class="fas fa-clipboard-list"></i>
                Aucune absence pour cet enseignant.
            </div>
            @endforelse
        </div>
        @if($absences->hasPages())
        <div class="card-body border-top">{{ $absences->links('vendor.pagination.custom') }}</div>
        @endif
    </div>
</div>
@endsection
