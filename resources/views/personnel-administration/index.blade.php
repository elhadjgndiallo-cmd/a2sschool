@extends('layouts.app')

@section('title', 'Personnel d\'Administration')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-users-cog me-2"></i>Personnel d'Administration
                    </h3>
                    <a href="{{ route('personnel-administration.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Nouveau Personnel
                    </a>
                </div>
                
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Photo</th>
                                    <th>Nom & Prénom</th>
                                    <th>Poste</th>
                                    <th>Département</th>
                                    <th>Date d'embauche</th>
                                    <th>Salaire</th>
                                    <th>Statut</th>
                                    <th>Permissions</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($personnel as $p)
                                    <tr>
                                        <td>
                                            @php use Illuminate\Support\Facades\Storage; @endphp
                                            @if($p->utilisateur->photo_profil && Storage::disk('public')->exists($p->utilisateur->photo_profil))
                                                <img src="{{ asset('images/profile_images/' . basename($p->utilisateur->photo_profi)) }}" 
                                                     alt="Photo" 
                                                     class="rounded-circle" 
                                                     style="width: 40px; height: 40px; object-fit: cover;">
                                            @else
                                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white" 
                                                     style="width: 40px; height: 40px;">
                                                    {{ substr($p->utilisateur->prenom, 0, 1) }}{{ substr($p->utilisateur->nom, 0, 1) }}
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="fw-bold">{{ $p->utilisateur->nom }} {{ $p->utilisateur->prenom }}</div>
                                            <small class="text-muted">{{ $p->utilisateur->email }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $p->poste }}</span>
                                        </td>
                                        <td>{{ $p->departement ?? 'Non défini' }}</td>
                                        <td>{{ $p->date_embauche->format('d/m/Y') }}</td>
                                        <td>
                                            @if($p->salaire)
                                                {{ number_format($p->salaire, 0, ',', ' ') }} GNF
                                            @else
                                                <span class="text-muted">Non défini</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($p->statut === 'actif')
                                                <span class="badge bg-success">Actif</span>
                                            @elseif($p->statut === 'inactif')
                                                <span class="badge bg-secondary">Inactif</span>
                                            @else
                                                <span class="badge bg-warning">Suspendu</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">{{ count($p->permissions ?? []) }} permission(s)</span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('personnel-administration.show', $p) }}" 
                                                   class="btn btn-sm btn-outline-info" title="Voir">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('personnel-administration.edit', $p) }}" 
                                                   class="btn btn-sm btn-outline-warning" title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="{{ route('personnel-administration.permissions', $p) }}" 
                                                   class="btn btn-sm btn-outline-primary" title="Permissions">
                                                    <i class="fas fa-key"></i>
                                                </a>
                                                <form method="POST" action="{{ route('personnel-administration.reset-password', $p) }}" 
                                                      class="d-inline" 
                                                      onsubmit="return confirm('Réinitialiser le mot de passe ?')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-secondary" title="Réinitialiser mot de passe">
                                                        <i class="fas fa-key"></i>
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('personnel-administration.destroy', $p) }}" 
                                                      class="d-inline" 
                                                      onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce personnel ?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-users fa-3x mb-3"></i>
                                                <p>Aucun personnel d'administration trouvé</p>
                                                <a href="{{ route('personnel-administration.create') }}" class="btn btn-primary">
                                                    <i class="fas fa-plus me-1"></i>Créer le premier personnel
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($personnel->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $personnel->links('vendor.pagination.custom') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection










