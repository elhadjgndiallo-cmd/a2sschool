@extends('layouts.app')

@section('title', 'Gestion des Permissions - ' . $utilisateur->nom)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-key me-2"></i>
                        Permissions de {{ $utilisateur->nom }} {{ $utilisateur->prenom }}
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>
                            Retour
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

                    <form action="{{ route('admin.permissions.update', $utilisateur) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">
                                            <i class="fas fa-user me-2"></i>
                                            Informations utilisateur
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-3">
                                            @if($utilisateur->photo_profil)
                                                <img src="{{ asset('storage/' . $utilisateur->photo_profil) }}" 
                                                     alt="Photo" class="rounded-circle me-3" 
                                                     style="width: 60px; height: 60px; object-fit: cover;">
                                            @else
                                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" 
                                                     style="width: 60px; height: 60px;">
                                                    <i class="fas fa-user text-white fa-lg"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <strong>{{ $utilisateur->nom }} {{ $utilisateur->prenom }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $utilisateur->email }}</small>
                                            </div>
                                        </div>
                                        
                                        <p><strong>Rôle:</strong> 
                                            <span class="badge bg-warning">Personnel Administration</span>
                                        </p>
                                        
                                        @if($utilisateur->personnelAdministration)
                                            <p><strong>Poste:</strong> {{ $utilisateur->personnelAdministration->poste }}</p>
                                            <p><strong>Département:</strong> {{ $utilisateur->personnelAdministration->departement }}</p>
                                            <p><strong>Date d'embauche:</strong> {{ $utilisateur->personnelAdministration->date_embauche->format('d/m/Y') }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">
                                            <i class="fas fa-shield-alt me-2"></i>
                                            Permissions disponibles
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        @foreach($permissions as $category => $perms)
                                            <div class="mb-4">
                                                <h6 class="text-primary border-bottom pb-2 mb-3">
                                                    <i class="fas fa-folder me-2"></i>
                                                    {{ $category }}
                                                </h6>
                                                
                                                <div class="row">
                                                    @foreach($perms as $key => $label)
                                                        <div class="col-md-6 mb-2">
                                                            <div class="form-check">
                                                                <input class="form-check-input" 
                                                                       type="checkbox" 
                                                                       name="permissions[]" 
                                                                       value="{{ $key }}" 
                                                                       id="permission_{{ $key }}"
                                                                       {{ in_array($key, $userPermissions) ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="permission_{{ $key }}">
                                                                    {{ $label }}
                                                                </label>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1">Actions rapides</h6>
                                                <small class="text-muted">Sélectionner toutes les permissions ou les désélectionner</small>
                                            </div>
                                            <div>
                                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="selectAll()">
                                                    <i class="fas fa-check-square me-1"></i>
                                                    Tout sélectionner
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="deselectAll()">
                                                    <i class="fas fa-square me-1"></i>
                                                    Tout désélectionner
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-end">
                                    <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary me-2">
                                        <i class="fas fa-times me-1"></i>
                                        Annuler
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>
                                        Sauvegarder les permissions
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function selectAll() {
    document.querySelectorAll('input[name="permissions[]"]').forEach(checkbox => {
        checkbox.checked = true;
    });
}

function deselectAll() {
    document.querySelectorAll('input[name="permissions[]"]').forEach(checkbox => {
        checkbox.checked = false;
    });
}
</script>
@endsection
