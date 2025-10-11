@extends('layouts.app')

@section('title', 'Gérer les Permissions')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-key me-2"></i>
        Gérer les Permissions
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.accounts.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Retour à la liste
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

<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Informations du Compte</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    @if($adminAccount->utilisateur->photo_profil && Storage::disk('public')->exists($adminAccount->utilisateur->photo_profil))
                        <img src="{{ asset('storage/' . $adminAccount->utilisateur->photo_profil) }}" 
                             alt="Photo de {{ $adminAccount->utilisateur->nom }}" 
                             class="rounded-circle mb-3" 
                             style="width: 80px; height: 80px; object-fit: cover;">
                    @else
                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white mx-auto mb-3" style="width: 80px; height: 80px;">
                            {{ substr($adminAccount->utilisateur->prenom, 0, 1) }}{{ substr($adminAccount->utilisateur->nom, 0, 1) }}
                        </div>
                    @endif
                </div>
                
                <h6 class="text-center mb-3">{{ $adminAccount->utilisateur->nom }} {{ $adminAccount->utilisateur->prenom }}</h6>
                
                <div class="mb-2">
                    <strong>Email:</strong> {{ $adminAccount->utilisateur->email }}
                </div>
                <div class="mb-2">
                    <strong>Poste:</strong> {{ $adminAccount->poste }}
                </div>
                <div class="mb-2">
                    <strong>Département:</strong> {{ $adminAccount->departement ?? 'Non spécifié' }}
                </div>
                <div class="mb-2">
                    <strong>Statut:</strong> 
                    <span class="badge bg-{{ $adminAccount->statut === 'actif' ? 'success' : ($adminAccount->statut === 'inactif' ? 'danger' : 'warning') }}">
                        {{ ucfirst($adminAccount->statut) }}
                    </span>
                </div>
                <div class="mb-2">
                    <strong>Permissions actuelles:</strong> 
                    <span class="badge bg-info">{{ count($adminAccount->permissions ?? []) }} permission(s)</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Permissions Disponibles</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.accounts.update-permissions', $adminAccount->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Sélectionnez les permissions que ce compte administrateur peut utiliser. 
                        Au moins une permission doit être sélectionnée.
                    </div>
                    
                    <div class="row">
                        @foreach($permissions as $groupName => $groupPermissions)
                        @if(count($groupPermissions) > 0)
                        <div class="col-md-6 mb-4">
                            <div class="card border">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">{{ $groupName }}</h6>
                                </div>
                                <div class="card-body">
                                    @foreach($groupPermissions as $key => $label)
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" 
                                               name="permissions[]" value="{{ $key }}" 
                                               id="permission_{{ $key }}"
                                               {{ in_array($key, $adminAccount->permissions ?? []) ? 'checked' : '' }}>
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
                    
                    @error('permissions')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                    
                    <div class="d-flex justify-content-end mt-4">
                        <a href="{{ route('admin.accounts.index') }}" class="btn btn-secondary me-2">Annuler</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>
                            Mettre à jour les Permissions
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Sélectionner/désélectionner toutes les permissions d'un groupe
function toggleGroupPermissions(groupElement) {
    const checkboxes = groupElement.querySelectorAll('input[type="checkbox"]');
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    
    checkboxes.forEach(cb => {
        cb.checked = !allChecked;
    });
}

// Gestion automatique du token CSRF
let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Fonction pour régénérer le token CSRF
async function regenerateCsrfToken() {
    try {
        const response = await fetch(window.location.href, {
            method: 'GET',
            credentials: 'include'
        });
        
        if (response.ok) {
            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newToken = doc.querySelector('meta[name="csrf-token"]');
            
            if (newToken) {
                csrfToken = newToken.getAttribute('content');
                document.querySelector('input[name="_token"]').value = csrfToken;
                console.log('Token CSRF régénéré:', csrfToken);
                return true;
            }
        }
    } catch (error) {
        console.error('Erreur lors de la régénération du token:', error);
    }
    return false;
}

// Gestion des erreurs 419
document.addEventListener('DOMContentLoaded', function() {
    // Chercher le formulaire avec plusieurs sélecteurs
    const form = document.querySelector('form[action*="update-permissions"]') || 
                 document.querySelector('form[action*="permissions"]') ||
                 document.querySelector('form[method="POST"]');
    
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(form);
            formData.set('_token', csrfToken);
            
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    credentials: 'include',
                    body: formData
                });
                
                if (response.ok) {
                    // Succès - recharger la page
                    window.location.reload();
                } else if (response.status === 419) {
                    // Erreur 419 - régénérer le token et réessayer
                    console.log('Erreur 419 détectée, régénération du token...');
                    
                    if (await regenerateCsrfToken()) {
                        // Réessayer avec le nouveau token
                        const newFormData = new FormData(form);
                        newFormData.set('_token', csrfToken);
                        
                        const retryResponse = await fetch(form.action, {
                            method: 'POST',
                            credentials: 'include',
                            body: newFormData
                        });
                        
                        if (retryResponse.ok) {
                            window.location.reload();
                        } else {
                            alert('Session expirée. Veuillez vous reconnecter.');
                            window.location.href = '/login';
                        }
                    } else {
                        alert('Session expirée. Veuillez vous reconnecter.');
                        window.location.href = '/login';
                    }
                } else {
                    alert('Erreur lors de la sauvegarde. Code: ' + response.status);
                }
            } catch (error) {
                console.error('Erreur lors de la soumission:', error);
                alert('Erreur de connexion. Veuillez réessayer.');
            }
        });
    }
});

// Ajouter des boutons pour sélectionner/désélectionner tout un groupe
document.addEventListener('DOMContentLoaded', function() {
    const groupHeaders = document.querySelectorAll('.card-header h6');
    groupHeaders.forEach(header => {
        const groupCard = header.closest('.card');
        const checkboxes = groupCard.querySelectorAll('input[type="checkbox"]');
        
        if (checkboxes.length > 1) {
            header.style.cursor = 'pointer';
            header.title = 'Cliquer pour sélectionner/désélectionner tout le groupe';
            
            header.addEventListener('click', function() {
                toggleGroupPermissions(groupCard);
            });
        }
    });
});
</script>
@endpush
@endsection

