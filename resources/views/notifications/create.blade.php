@extends('layouts.app')

@section('title', 'Créer une notification')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Créer une notification</h4>
                <div class="page-title-right">
                    <a href="{{ route('notifications.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Retour
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Nouvelle notification</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('notifications.store') }}">
                        @csrf
                        
                        <!-- Destinataires -->
                        <div class="mb-3">
                            <label for="utilisateurs" class="form-label">Destinataires <span class="text-danger">*</span></label>
                            <select class="form-select @error('utilisateurs') is-invalid @enderror" 
                                    id="utilisateurs" 
                                    name="utilisateurs[]" 
                                    multiple 
                                    required>
                                @foreach($utilisateurs as $utilisateur)
                                    <option value="{{ $utilisateur->id }}">
                                        {{ $utilisateur->nom }} {{ $utilisateur->prenom }} 
                                        ({{ ucfirst($utilisateur->role) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('utilisateurs')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Maintenez Ctrl (ou Cmd sur Mac) pour sélectionner plusieurs utilisateurs.</div>
                        </div>

                        <!-- Titre -->
                        <div class="mb-3">
                            <label for="titre" class="form-label">Titre <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('titre') is-invalid @enderror" 
                                   id="titre" 
                                   name="titre" 
                                   value="{{ old('titre') }}" 
                                   maxlength="100" 
                                   required>
                            @error('titre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Message -->
                        <div class="mb-3">
                            <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('message') is-invalid @enderror" 
                                      id="message" 
                                      name="message" 
                                      rows="4" 
                                      maxlength="255" 
                                      required>{{ old('message') }}</textarea>
                            @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <span id="message-count">0</span>/255 caractères
                            </div>
                        </div>

                        <!-- Type -->
                        <div class="mb-3">
                            <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('type') is-invalid @enderror" 
                                    id="type" 
                                    name="type" 
                                    required>
                                <option value="">Sélectionner un type</option>
                                <option value="info" {{ old('type') === 'info' ? 'selected' : '' }}>Information</option>
                                <option value="success" {{ old('type') === 'success' ? 'selected' : '' }}>Succès</option>
                                <option value="warning" {{ old('type') === 'warning' ? 'selected' : '' }}>Avertissement</option>
                                <option value="danger" {{ old('type') === 'danger' ? 'selected' : '' }}>Erreur</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Lien (optionnel) -->
                        <div class="mb-3">
                            <label for="lien" class="form-label">Lien (optionnel)</label>
                            <input type="url" 
                                   class="form-control @error('lien') is-invalid @enderror" 
                                   id="lien" 
                                   name="lien" 
                                   value="{{ old('lien') }}" 
                                   placeholder="https://exemple.com">
                            @error('lien')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Lien vers une page ou une action spécifique.</div>
                        </div>

                        <!-- Icône (optionnel) -->
                        <div class="mb-3">
                            <label for="icone" class="form-label">Icône (optionnel)</label>
                            <input type="text" 
                                   class="form-control @error('icone') is-invalid @enderror" 
                                   id="icone" 
                                   name="icone" 
                                   value="{{ old('icone') }}" 
                                   placeholder="fas fa-info-circle">
                            @error('icone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Classe CSS de l'icône FontAwesome (ex: fas fa-info-circle).</div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('notifications.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i> Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-1"></i> Envoyer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Aperçu</h5>
                </div>
                <div class="card-body">
                    <div class="notification-preview">
                        <div class="d-flex align-items-start">
                            <div class="me-3">
                                <i class="fas fa-bell fa-2x text-info" id="preview-icon"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1 fw-bold" id="preview-titre">Titre de la notification</h6>
                                <p class="mb-1" id="preview-message">Message de la notification</p>
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>
                                    Il y a quelques secondes
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Icônes suggérées</h5>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-6">
                            <button type="button" class="btn btn-outline-info btn-sm w-100" onclick="setIcon('fas fa-info-circle')">
                                <i class="fas fa-info-circle me-1"></i> Info
                            </button>
                        </div>
                        <div class="col-6">
                            <button type="button" class="btn btn-outline-success btn-sm w-100" onclick="setIcon('fas fa-check-circle')">
                                <i class="fas fa-check-circle me-1"></i> Succès
                            </button>
                        </div>
                        <div class="col-6">
                            <button type="button" class="btn btn-outline-warning btn-sm w-100" onclick="setIcon('fas fa-exclamation-triangle')">
                                <i class="fas fa-exclamation-triangle me-1"></i> Attention
                            </button>
                        </div>
                        <div class="col-6">
                            <button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="setIcon('fas fa-times-circle')">
                                <i class="fas fa-times-circle me-1"></i> Erreur
                            </button>
                        </div>
                        <div class="col-6">
                            <button type="button" class="btn btn-outline-primary btn-sm w-100" onclick="setIcon('fas fa-bell')">
                                <i class="fas fa-bell me-1"></i> Notification
                            </button>
                        </div>
                        <div class="col-6">
                            <button type="button" class="btn btn-outline-secondary btn-sm w-100" onclick="setIcon('fas fa-envelope')">
                                <i class="fas fa-envelope me-1"></i> Message
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Mise à jour de l'aperçu en temps réel
document.addEventListener('DOMContentLoaded', function() {
    const titreInput = document.getElementById('titre');
    const messageInput = document.getElementById('message');
    const typeSelect = document.getElementById('type');
    const iconeInput = document.getElementById('icone');
    const messageCount = document.getElementById('message-count');
    
    const previewTitre = document.getElementById('preview-titre');
    const previewMessage = document.getElementById('preview-message');
    const previewIcon = document.getElementById('preview-icon');
    
    // Mise à jour du titre
    titreInput.addEventListener('input', function() {
        previewTitre.textContent = this.value || 'Titre de la notification';
    });
    
    // Mise à jour du message
    messageInput.addEventListener('input', function() {
        previewMessage.textContent = this.value || 'Message de la notification';
        messageCount.textContent = this.value.length;
    });
    
    // Mise à jour du type et de l'icône
    typeSelect.addEventListener('change', function() {
        updateIcon();
    });
    
    iconeInput.addEventListener('input', function() {
        updateIcon();
    });
    
    function updateIcon() {
        const type = typeSelect.value;
        const icone = iconeInput.value;
        
        if (icone) {
            previewIcon.className = icone + ' fa-2x text-' + type;
        } else {
            // Icônes par défaut selon le type
            const defaultIcons = {
                'info': 'fas fa-info-circle',
                'success': 'fas fa-check-circle',
                'warning': 'fas fa-exclamation-triangle',
                'danger': 'fas fa-times-circle'
            };
            previewIcon.className = (defaultIcons[type] || 'fas fa-bell') + ' fa-2x text-' + type;
        }
    }
});

// Fonction pour définir une icône
function setIcon(iconClass) {
    document.getElementById('icone').value = iconClass;
    document.getElementById('icone').dispatchEvent(new Event('input'));
}

// Sélection rapide de tous les utilisateurs
document.addEventListener('DOMContentLoaded', function() {
    const select = document.getElementById('utilisateurs');
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'btn btn-sm btn-outline-primary mt-2';
    button.innerHTML = '<i class="fas fa-check-double me-1"></i>Sélectionner tous';
    button.onclick = function() {
        for (let option of select.options) {
            option.selected = true;
        }
    };
    select.parentNode.appendChild(button);
});
</script>
@endpush











