@extends('layouts.app')

@section('title', 'Modifier Matière')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-edit me-2"></i>
        Modifier la Matière: {{ $matiere->nom }}
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

<form method="POST" action="{{ route('matieres.update', $matiere) }}">
    @csrf
    @method('PUT')
    
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
                        <input type="text" class="form-control" id="nom" name="nom" value="{{ old('nom', $matiere->nom) }}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="code" class="form-label">Code Matière *</label>
                        <input type="text" class="form-control" id="code" name="code" value="{{ old('code', $matiere->code) }}" required maxlength="10">
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="coefficient" class="form-label">Coefficient *</label>
                        <select class="form-select" id="coefficient" name="coefficient" required>
                            <option value="">Sélectionner un coefficient</option>
                            <option value="1" {{ (old('coefficient', $matiere->coefficient) == '1') ? 'selected' : '' }}>1</option>
                            <option value="2" {{ (old('coefficient', $matiere->coefficient) == '2') ? 'selected' : '' }}>2</option>
                            <option value="3" {{ (old('coefficient', $matiere->coefficient) == '3') ? 'selected' : '' }}>3</option>
                            <option value="4" {{ (old('coefficient', $matiere->coefficient) == '4') ? 'selected' : '' }}>4</option>
                        </select>
                        <div class="form-text">Coefficient de la matière</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="couleur" class="form-label">Couleur *</label>
                        <input type="color" class="form-control form-control-color" id="couleur" name="couleur" value="{{ old('couleur', $matiere->couleur) }}" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Aperçu</label>
                        <div class="d-flex align-items-center">
                            <span id="preview-badge" class="badge me-2" style="background-color: {{ $matiere->couleur }}; color: white;">{{ $matiere->code }}</span>
                            <span id="preview-name">{{ $matiere->nom }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $matiere->description) }}</textarea>
            </div>
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
                        Mettre à Jour
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

    function updatePreview() {
        const nom = nomField.value || 'Matière';
        const code = codeField.value || 'CODE';
        const couleur = couleurField.value;
        
        previewBadge.textContent = code.toUpperCase();
        previewBadge.style.backgroundColor = couleur;
        previewName.textContent = nom;
    }

    nomField.addEventListener('input', updatePreview);
    codeField.addEventListener('input', function() {
        this.value = this.value.toUpperCase();
        updatePreview();
    });
    couleurField.addEventListener('change', updatePreview);
});
</script>
@endpush
@endsection
