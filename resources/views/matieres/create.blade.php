@extends('layouts.app')

@section('title', 'Ajouter Matière')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-plus me-2"></i>
        Ajouter une Matière
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('matieres.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Retour
        </a>
    </div>
</div>

@if($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ route('matieres.store') }}">
    @csrf
    
    <!-- Informations de base -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Informations de la Matière</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="nom" class="form-label">Nom de la Matière *</label>
                        <input type="text" class="form-control" id="nom" name="nom" value="{{ old('nom') }}" required placeholder="Ex: Mathématiques">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="code" class="form-label">Code Matière *</label>
                        <input type="text" class="form-control" id="code" name="code" value="{{ old('code') }}" required placeholder="Ex: MATH" maxlength="10">
                        <div class="form-text">Code court pour identifier la matière</div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="coefficient" class="form-label">Coefficient *</label>
                        <select class="form-select" id="coefficient" name="coefficient" required>
                            <option value="">Sélectionner un coefficient</option>
                            <option value="1" {{ old('coefficient') == '1' ? 'selected' : '' }}>1</option>
                            <option value="2" {{ old('coefficient') == '2' ? 'selected' : '' }}>2</option>
                            <option value="3" {{ old('coefficient') == '3' ? 'selected' : '' }}>3</option>
                            <option value="4" {{ old('coefficient') == '4' ? 'selected' : '' }}>4</option>
                        </select>
                        <div class="form-text">Coefficient de la matière</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="couleur" class="form-label">Couleur *</label>
                        <input type="color" class="form-control form-control-color" id="couleur" name="couleur" value="{{ old('couleur', '#007bff') }}" required>
                        <div class="form-text">Couleur pour l'emploi du temps</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Aperçu</label>
                        <div class="d-flex align-items-center">
                            <span id="preview-badge" class="badge me-2" style="background-color: #007bff; color: white;">MATH</span>
                            <span id="preview-name">Mathématiques</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3" placeholder="Description optionnelle de la matière">{{ old('description') }}</textarea>
            </div>
        </div>
    </div>

    <!-- Enseignants assignés -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Enseignants Assignés</h5>
        </div>
        <div class="card-body">
            @if($enseignants->count() > 0)
            <div class="row">
                @foreach($enseignants as $enseignant)
                <div class="col-md-4 mb-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="enseignants[]" 
                               value="{{ $enseignant->id }}" id="enseignant_{{ $enseignant->id }}"
                               {{ in_array($enseignant->id, old('enseignants', [])) ? 'checked' : '' }}>
                        <label class="form-check-label" for="enseignant_{{ $enseignant->id }}">
                            {{ $enseignant->utilisateur->name }}
                            <br><small class="text-muted">{{ $enseignant->specialite }}</small>
                        </label>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                Aucun enseignant disponible. Vous pourrez assigner des enseignants plus tard.
            </div>
            @endif
        </div>
    </div>

    <!-- Boutons d'action -->
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <button type="reset" class="btn btn-outline-secondary">
                        <i class="fas fa-undo me-1"></i>
                        Réinitialiser
                    </button>
                </div>
                <div class="col-md-6 text-end">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fas fa-save me-2"></i>
                        Enregistrer la Matière
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const nomField = document.getElementById('nom');
    const codeField = document.getElementById('code');
    const couleurField = document.getElementById('couleur');
    const previewBadge = document.getElementById('preview-badge');
    const previewName = document.getElementById('preview-name');

    // Mise à jour de l'aperçu en temps réel
    function updatePreview() {
        const nom = nomField.value || 'Matière';
        const code = codeField.value || 'CODE';
        const couleur = couleurField.value;
        
        previewBadge.textContent = code.toUpperCase();
        previewBadge.style.backgroundColor = couleur;
        previewName.textContent = nom;
    }

    // Génération automatique du code
    nomField.addEventListener('input', function() {
        if (!codeField.value) {
            const words = this.value.split(' ');
            let code = '';
            
            if (words.length === 1) {
                code = words[0].substring(0, 4).toUpperCase();
            } else {
                code = words.map(word => word.charAt(0)).join('').substring(0, 4).toUpperCase();
            }
            
            codeField.value = code;
        }
        updatePreview();
    });

    codeField.addEventListener('input', function() {
        this.value = this.value.toUpperCase();
        updatePreview();
    });

    couleurField.addEventListener('change', updatePreview);

    // Initialiser l'aperçu
    updatePreview();
});
</script>
@endpush
@endsection
