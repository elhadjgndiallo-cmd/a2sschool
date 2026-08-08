@extends('layouts.app')

@section('title', 'Gestion des utilisateurs')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="page-title mb-0 font-size-18">Gestion des utilisateurs</h4>

                <div class="page-title-right">
                    <a href="{{ route('admin.utilisateurs.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-1"></i> Nouvel utilisateur
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-centered table-hover table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 20px;">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="selectall">
                                            <label class="form-check-label" for="selectall">&nbsp;</label>
                                        </div>
                                    </th>
                                    <th>Nom</th>
                                    <th>Email</th>
                                    <th>Rôle</th>
                                    <th>Statut</th>
                                    <th>Date de création</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($utilisateurs as $utilisateur)
                                <tr class="table-row-clickable" data-href="{{ route('admin.utilisateurs.edit', $utilisateur) }}" role="button" tabindex="0">
                                    <td>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="user-{{ $utilisateur->id }}" onclick="event.stopPropagation()">
                                            <label class="form-check-label" for="user-{{ $utilisateur->id }}">&nbsp;</label>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($utilisateur->photo_profil)
                                                <img src="{{ asset('storage/' . $utilisateur->photo_profil) }}" alt="Photo de profil" class="rounded-circle me-2" width="40" height="40" style="object-fit: cover;">
                                            @else
                                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white me-2" style="width: 40px; height: 40px;">
                                                    {{ substr($utilisateur->prenom, 0, 1) }}{{ substr($utilisateur->nom, 0, 1) }}
                                                </div>
                                            @endif
                                            <div>
                                                <h5 class="font-size-14 mb-0">{{ $utilisateur->prenom }} {{ $utilisateur->nom }}</h5>
                                                @if($utilisateur->telephone)
                                                    <small class="text-muted">{{ $utilisateur->telephone }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $utilisateur->email }}</td>
                                    <td>
                                        @switch($utilisateur->role)
                                            @case('admin')
                                                <span class="badge bg-danger">Administrateur</span>
                                                @break
                                            @case('teacher')
                                                <span class="badge bg-success">Enseignant</span>
                                                @break
                                            @case('student')
                                                <span class="badge bg-primary">Élève</span>
                                                @break
                                            @case('parent')
                                                <span class="badge bg-info">Parent</span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary">{{ $utilisateur->role }}</span>
                                        @endswitch
                                    </td>
                                    <td>
                                        @if($utilisateur->actif)
                                            <span class="badge bg-success">Actif</span>
                                        @else
                                            <span class="badge bg-danger">Inactif</span>
                                        @endif
                                    </td>
                                    <td>{{ $utilisateur->created_at->format('d/m/Y') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">Aucun utilisateur trouvé</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            <small class="text-muted">
                                Affichage de {{ $utilisateurs->firstItem() ?? 0 }} à {{ $utilisateurs->lastItem() ?? 0 }} sur {{ $utilisateurs->total() }} utilisateur{{ $utilisateurs->total() > 1 ? 's' : '' }}
                            </small>
                        </div>
                        <div>
                            {{ $utilisateurs->appends(request()->query())->links('vendor.pagination.custom') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Script pour sélectionner/désélectionner tous les utilisateurs
    document.getElementById('selectall').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('tbody input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });
</script>
@endsection
