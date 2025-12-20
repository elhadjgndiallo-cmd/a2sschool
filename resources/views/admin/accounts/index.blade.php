@extends('layouts.app')

@section('title', 'Gestion des Comptes Administrateurs')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-user-shield me-2"></i>
        Gestion des Comptes Administrateurs
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('cartes-personnel-administration.index') }}" class="btn btn-success me-2">
            <i class="fas fa-id-card me-1"></i>
            Cartes Administrateurs
        </a>
        <a href="{{ route('admin.accounts.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>
            Créer un Compte Administrateur
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Liste des Comptes Administrateurs</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Photo</th>
                        <th>Nom Complet</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Poste</th>
                        <th>Département</th>
                        <th>Statut</th>
                        <th>Permissions</th>
                        <th>Date Embauche</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($adminAccounts as $account)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <div class="avatar-sm">
                                @if($account->utilisateur && $account->utilisateur->photo_profil && Storage::disk('public')->exists($account->utilisateur->photo_profil))
                                    <img src="{{ asset('storage/' . $account->utilisateur->photo_profil) }}" 
                                         alt="Photo de {{ $account->utilisateur->nom }} {{ $account->utilisateur->prenom }}" 
                                         class="rounded-circle" 
                                         style="width: 40px; height: 40px; object-fit: cover;">
                                @else
                                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 40px; height: 40px;">
                                        {{ substr($account->utilisateur->prenom, 0, 1) }}{{ substr($account->utilisateur->nom, 0, 1) }}
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td>
                            <strong>{{ $account->utilisateur->nom }} {{ $account->utilisateur->prenom }}</strong>
                            <br>
                            <small class="text-muted">{{ $account->utilisateur->sexe == 'M' ? 'Masculin' : 'Féminin' }}</small>
                        </td>
                        <td>{{ $account->utilisateur->email }}</td>
                        <td>{{ $account->utilisateur->telephone }}</td>
                        <td>{{ $account->poste }}</td>
                        <td>{{ $account->departement ?? 'Non spécifié' }}</td>
                        <td>
                            <span class="badge bg-{{ $account->statut === 'actif' ? 'success' : ($account->statut === 'inactif' ? 'danger' : 'warning') }}">
                                {{ ucfirst($account->statut) }}
                            </span>
                        </td>
                        <td>
                            @php
                                $permissions = $account->permissions ?? [];
                                if (is_string($permissions)) {
                                    $permissions = json_decode($permissions, true) ?? [];
                                }
                                $permissionsCount = is_array($permissions) ? count($permissions) : 0;
                            @endphp
                            <div class="d-flex flex-column">
                                <span class="badge bg-info mb-1">{{ $permissionsCount }} permission(s)</span>
                                @if($permissionsCount > 0)
                                    <small class="text-muted">
                                        @php
                                            $firstThree = array_slice($permissions, 0, 3);
                                        @endphp
                                        @foreach($firstThree as $perm)
                                            <span class="badge bg-light text-dark me-1 mb-1">{{ $perm }}</span>
                                        @endforeach
                                        @if($permissionsCount > 3)
                                            <span class="badge bg-secondary">+{{ $permissionsCount - 3 }} autres</span>
                                        @endif
                                    </small>
                                @endif
                            </div>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($account->date_embauche)->format('d/m/Y') }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.accounts.show', $account->id) }}" 
                                   class="btn btn-outline-info" title="Voir détails">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.accounts.edit', $account->id) }}" 
                                   class="btn btn-outline-warning" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('admin.accounts.permissions', $account->id) }}" 
                                   class="btn btn-outline-primary" title="Gérer permissions">
                                    <i class="fas fa-key"></i>
                                </a>
                                <a href="{{ route('cartes-personnel-administration.create') }}?personnel_administration_id={{ $account->id }}" 
                                   class="btn btn-outline-success" title="Créer une carte">
                                    <i class="fas fa-id-card"></i>
                                </a>
                                <button type="button" 
                                        class="btn btn-outline-secondary" 
                                        onclick="resetPassword({{ $account->id }})"
                                        title="Réinitialiser mot de passe">
                                    <i class="fas fa-lock"></i>
                                </button>
                                @if($account->utilisateur->email !== 'admin@gmail.com')
                                <button type="button" 
                                        class="btn btn-outline-{{ $account->statut === 'actif' ? 'danger' : 'success' }}" 
                                        onclick="toggleStatus({{ $account->id }})"
                                        title="{{ $account->statut === 'actif' ? 'Désactiver' : 'Activer' }}">
                                    <i class="fas fa-{{ $account->statut === 'actif' ? 'ban' : 'check' }}"></i>
                                </button>
                                <form method="POST" action="{{ route('admin.accounts.destroy', $account) }}" class="d-inline" 
                                      onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer définitivement le compte administrateur {{ $account->utilisateur->prenom }} {{ $account->utilisateur->nom }} ?\n\nCette action supprimera :\n- Le compte administrateur et son compte utilisateur\n- Toutes ses cartes\n- Sa photo de profil\n\nCette action est irréversible !')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="btn btn-outline-danger" 
                                            title="Supprimer définitivement">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center text-muted">
                            <i class="fas fa-info-circle fa-2x mb-3"></i>
                            <p>Aucun compte administrateur trouvé.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <small class="text-muted">
                    Affichage de {{ $adminAccounts->firstItem() ?? 0 }} à {{ $adminAccounts->lastItem() ?? 0 }} sur {{ $adminAccounts->total() }} compte(s) administrateur
                </small>
            </div>
            <div>
                {{ $adminAccounts->appends(request()->query())->links('vendor.pagination.custom') }}
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>

function resetPassword(id) {
    if (confirm('Êtes-vous sûr de vouloir réinitialiser le mot de passe de ce compte administrateur ?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ url('/admin/accounts') }}/${id}/reset-password`;
        form.style.display = 'none';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        form.appendChild(csrfToken);
        
        document.body.appendChild(form);
        form.submit();
    }
}

function toggleStatus(id) {
    if (confirm('Êtes-vous sûr de vouloir changer le statut de ce compte administrateur ?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ url('/admin/accounts') }}/${id}/toggle-status`;
        form.style.display = 'none';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        form.appendChild(csrfToken);
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush
@endsection

