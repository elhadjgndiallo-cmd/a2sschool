@extends('layouts.app')

@section('title', 'Créer un Parent')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-user-plus me-2"></i>
            Créer un Nouveau Parent
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('parents.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Retour à la liste
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Erreurs de validation :</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <form action="{{ route('parents.store') }}" method="POST">
        @csrf

        <!-- Informations personnelles -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-user me-2"></i>
                    Informations Personnelles
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="prenom" class="form-label">
                            Prénom <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control @error('prenom') is-invalid @enderror" 
                               id="prenom" name="prenom" value="{{ old('prenom') }}" required>
                        @error('prenom')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="nom" class="form-label">
                            Nom <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control @error('nom') is-invalid @enderror" 
                               id="nom" name="nom" value="{{ old('nom') }}" required>
                        @error('nom')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="sexe" class="form-label">
                            Sexe <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @error('sexe') is-invalid @enderror" 
                                id="sexe" name="sexe" required>
                            <option value="">Sélectionner...</option>
                            <option value="M" {{ old('sexe') == 'M' ? 'selected' : '' }}>Masculin</option>
                            <option value="F" {{ old('sexe') == 'F' ? 'selected' : '' }}>Féminin</option>
                        </select>
                        @error('sexe')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="date_naissance" class="form-label">
                            Date de Naissance
                        </label>
                        <input type="date" class="form-control @error('date_naissance') is-invalid @enderror" 
                               id="date_naissance" name="date_naissance" value="{{ old('date_naissance') }}">
                        @error('date_naissance')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="telephone" class="form-label">
                            Téléphone <span class="text-danger">*</span>
                        </label>
                        <input type="tel" class="form-control @error('telephone') is-invalid @enderror" 
                               id="telephone" name="telephone" value="{{ old('telephone') }}" required>
                        @error('telephone')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label">
                            Email
                        </label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                               id="email" name="email" value="{{ old('email') }}"
                               placeholder="Laisser vide pour générer automatiquement">
                        @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Si vide, un email sera généré automatiquement</small>
                    </div>

                    <div class="col-md-12">
                        <label for="adresse" class="form-label">
                            Adresse
                        </label>
                        <textarea class="form-control @error('adresse') is-invalid @enderror" 
                                  id="adresse" name="adresse" rows="2">{{ old('adresse') }}</textarea>
                        @error('adresse')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Sélection des élèves -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-graduation-cap me-2"></i>
                    Lier à des Élèves (Optionnel)
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Rechercher et sélectionner des élèves</label>
                    <input type="text" class="form-control mb-3" id="searchEleve" 
                           placeholder="Rechercher par nom, prénom ou matricule...">
                </div>

                <div style="max-height: 400px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 4px; padding: 10px;">
                    @foreach($eleves as $eleve)
                    <div class="form-check eleve-item" data-search="{{ strtolower($eleve->utilisateur->nom . ' ' . $eleve->utilisateur->prenom . ' ' . $eleve->numero_etudiant) }}">
                        <input class="form-check-input" type="checkbox" 
                               id="eleve_{{ $eleve->id }}" 
                               name="eleves_ids[]" 
                               value="{{ $eleve->id }}"
                               {{ in_array($eleve->id, old('eleves_ids', [])) ? 'checked' : '' }}>
                        <label class="form-check-label w-100" for="eleve_{{ $eleve->id }}">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $eleve->utilisateur->prenom }} {{ $eleve->utilisateur->nom }}</strong>
                                    <span class="badge bg-secondary ms-2">{{ $eleve->numero_etudiant }}</span>
                                </div>
                                <div>
                                    <span class="badge bg-info">{{ $eleve->classe->nom ?? 'Sans classe' }}</span>
                                </div>
                            </div>
                        </label>
                    </div>
                    <hr class="my-2">
                    @endforeach
                </div>

                <small class="text-muted d-block mt-2">
                    <i class="fas fa-info-circle me-1"></i>
                    Vous pouvez sélectionner plusieurs élèves pour les lier à ce parent.
                </small>
            </div>
        </div>

        <!-- Boutons d'action -->
        <div class="d-grid gap-2 d-md-flex justify-content-md-end mb-4">
            <a href="{{ route('parents.index') }}" class="btn btn-secondary">
                <i class="fas fa-times me-1"></i>
                Annuler
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i>
                Créer le Parent
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Recherche d'élèves
    const searchInput = document.getElementById('searchEleve');
    const eleveItems = document.querySelectorAll('.eleve-item');
    
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        
        eleveItems.forEach(function(item) {
            const searchData = item.getAttribute('data-search');
            if (searchData.includes(searchTerm)) {
                item.style.display = 'block';
                item.nextElementSibling.style.display = 'block'; // hr
            } else {
                item.style.display = 'none';
                item.nextElementSibling.style.display = 'none'; // hr
            }
        });
    });
});
</script>
@endsection
