@extends('layouts.app')

@section('title', 'Permissions du compte')

@php
use Illuminate\Support\Facades\Storage;
$utilisateur = $adminAccount->utilisateur ?? null;
$photoPath = $utilisateur->photo_profil ?? null;
$photoUrl = ($photoPath && Storage::disk('public')->exists($photoPath))
    ? asset('storage/' . $photoPath)
    : null;
$initiales = $utilisateur
    ? strtoupper(substr($utilisateur->prenom ?? '', 0, 1) . substr($utilisateur->nom ?? '', 0, 1))
    : '?';
$currentPermissions = is_string($adminAccount->permissions)
    ? (json_decode($adminAccount->permissions, true) ?? [])
    : ($adminAccount->permissions ?? []);
$currentPermissions = array_values(array_filter((array) $currentPermissions));
@endphp

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-shield-alt me-2"></i>
        Permissions
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0 gap-2">
        <a href="{{ route('admin.accounts.edit', $adminAccount) }}" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-user-edit me-1"></i>
            Modifier
        </a>
        <a href="{{ route('admin.accounts.index') }}" class="btn btn-sm btn-outline-secondary">
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

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ $errors->first() }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<form action="{{ route('admin.accounts.update-permissions', $adminAccount->id) }}" method="POST" id="form-permissions">
    @csrf
    @method('PUT')

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Compte</h5>
                </div>
                <div class="card-body text-center">
                    @if($photoUrl)
                        <img src="{{ $photoUrl }}" alt="Photo" class="rounded-circle mb-3"
                             style="width: 120px; height: 120px; object-fit: cover;">
                    @else
                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white mx-auto mb-3"
                             style="width: 120px; height: 120px; font-size: 2.2rem;">
                            {{ $initiales }}
                        </div>
                    @endif
                    <h5 class="mb-1">{{ $utilisateur->nom }} {{ $utilisateur->prenom }}</h5>
                    <p class="text-muted mb-2">{{ $adminAccount->poste }}</p>
                    <span class="badge bg-{{ $adminAccount->statut === 'actif' ? 'success' : ($adminAccount->statut === 'inactif' ? 'danger' : 'warning') }} mb-3">
                        {{ ucfirst($adminAccount->statut) }}
                    </span>
                    <div class="text-start small">
                        <p class="mb-1"><strong>Email :</strong> {{ $utilisateur->email }}</p>
                        <p class="mb-1"><strong>Département :</strong> {{ $adminAccount->departement ?? '—' }}</p>
                        <p class="mb-0">
                            <strong>Sélection :</strong>
                            <span class="badge bg-info" id="permissions-count">{{ count($currentPermissions) }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0">Permissions disponibles</h5>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="select-all">
                            Tout sélectionner
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="deselect-all">
                            Tout désélectionner
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Cochez les accès de ce compte. Cliquez sur le titre d’un groupe pour tout cocher ou décocher.
                    </p>
                    <div class="row">
                        @foreach($permissions as $groupName => $groupPermissions)
                            @if(count($groupPermissions) > 0)
                            <div class="col-md-6 mb-3">
                                <div class="border rounded h-100 permission-group">
                                    <div class="px-3 py-2 bg-light border-bottom d-flex justify-content-between align-items-center group-toggle" role="button">
                                        <strong>{{ $groupName }}</strong>
                                        <span class="badge bg-secondary group-count">0</span>
                                    </div>
                                    <div class="p-3">
                                        @foreach($groupPermissions as $key => $label)
                                        <div class="form-check mb-2">
                                            <input class="form-check-input permission-checkbox" type="checkbox"
                                                   name="permissions[]" value="{{ $key }}"
                                                   id="permission_{{ $key }}"
                                                   {{ in_array($key, $currentPermissions, true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="permission_{{ $key }}">
                                                {{ $label }}
                                            </label>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
                <div class="card-footer bg-white d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.accounts.index') }}" class="btn btn-outline-secondary">Annuler</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>
                        Enregistrer
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const boxes = () => document.querySelectorAll('.permission-checkbox');
    const countBadge = document.getElementById('permissions-count');

    function updateCounts() {
        const all = boxes();
        if (countBadge) {
            countBadge.textContent = document.querySelectorAll('.permission-checkbox:checked').length;
        }
        document.querySelectorAll('.permission-group').forEach(function (group) {
            const groupBoxes = group.querySelectorAll('.permission-checkbox');
            const checked = group.querySelectorAll('.permission-checkbox:checked').length;
            const badge = group.querySelector('.group-count');
            if (badge) {
                badge.textContent = checked + '/' + groupBoxes.length;
            }
        });
    }

    document.getElementById('select-all')?.addEventListener('click', function () {
        boxes().forEach(cb => { cb.checked = true; });
        updateCounts();
    });

    document.getElementById('deselect-all')?.addEventListener('click', function () {
        boxes().forEach(cb => { cb.checked = false; });
        updateCounts();
    });

    document.querySelectorAll('.group-toggle').forEach(function (header) {
        header.addEventListener('click', function () {
            const group = header.closest('.permission-group');
            const groupBoxes = group.querySelectorAll('.permission-checkbox');
            const allChecked = Array.from(groupBoxes).every(cb => cb.checked);
            groupBoxes.forEach(cb => { cb.checked = !allChecked; });
            updateCounts();
        });
    });

    document.querySelectorAll('.permission-checkbox').forEach(function (cb) {
        cb.addEventListener('change', updateCounts);
    });

    updateCounts();
});
</script>
@endpush
