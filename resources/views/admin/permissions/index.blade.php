@extends('layouts.app')

@section('title', 'Gestion des Permissions')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-shield-alt me-2"></i>
                        Gestion des Permissions
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.utilisateurs.index') }}" class="btn btn-primary">
                            <i class="fas fa-users me-1"></i>
                            Gérer les Utilisateurs
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Utilisateur</th>
                                    <th>Rôle</th>
                                    <th>Poste</th>
                                    <th>Permissions</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($utilisateurs as $utilisateur)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($utilisateur->photo_profil)
                                                    <img src="{{ asset('storage/' . $utilisateur->photo_profil) }}" 
                                                         alt="Photo" class="rounded-circle me-2" 
                                                         style="width: 40px; height: 40px; object-fit: cover;">
                                                @else
                                                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-2" 
                                                         style="width: 40px; height: 40px;">
                                                        <i class="fas fa-user text-white"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <strong>{{ $utilisateur->nom }} {{ $utilisateur->prenom }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $utilisateur->email }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($utilisateur->role === 'admin')
                                                <span class="badge bg-danger">Administrateur</span>
                                            @elseif($utilisateur->role === 'personnel_admin')
                                                <span class="badge bg-warning">Personnel Admin</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($utilisateur->personnelAdministration)
                                                {{ $utilisateur->personnelAdministration->poste }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($utilisateur->role === 'admin')
                                                <span class="badge bg-success">Toutes les permissions</span>
                                            @elseif($utilisateur->personnelAdministration && $utilisateur->personnelAdministration->permissions)
                                                <span class="badge bg-info">
                                                    {{ count($utilisateur->personnelAdministration->permissions) }} permission(s)
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">Aucune permission</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($utilisateur->actif)
                                                <span class="badge bg-success">Actif</span>
                                            @else
                                                <span class="badge bg-danger">Inactif</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                @if($utilisateur->role === 'personnel_admin')
                                                    <a href="{{ route('admin.permissions.show', $utilisateur) }}" 
                                                       class="btn btn-outline-primary" title="Gérer les permissions">
                                                        <i class="fas fa-key"></i>
                                                    </a>
                                                @endif
                                                
                                                <a href="{{ route('admin.utilisateurs.show', $utilisateur) }}" 
                                                   class="btn btn-outline-info" title="Voir le profil">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">
                                            <i class="fas fa-users fa-2x mb-2"></i>
                                            <br>
                                            Aucun utilisateur administratif trouvé
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
