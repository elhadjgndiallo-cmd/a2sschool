@extends('layouts.app')

@section('title', 'Réinscription des Élèves')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-user-edit me-2"></i>
        Réinscription des Élèves
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('eleves.index') }}" class="btn btn-outline-secondary">
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

<!-- Information sur l'année scolaire active -->
<div class="alert alert-info">
    <i class="fas fa-info-circle me-2"></i>
    <strong>Année scolaire active :</strong> 
    {{ $anneeScolaireActive ? $anneeScolaireActive->nom : 'Aucune année active' }}
    @if($anneeScolaireActive)
    ({{ $anneeScolaireActive->date_debut->format('Y') }}-{{ $anneeScolaireActive->date_fin->format('Y') }})
    @endif
</div>

@if(!$anneeScolaireActive)
<div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <strong>Attention :</strong> Aucune année scolaire n'est actuellement active. 
    Veuillez activer une année scolaire avant de procéder aux réinscriptions.
</div>
@else

<form method="POST" action="{{ route('eleves.reinscription.process') }}" id="reinscriptionForm">
    @csrf
    
    <!-- Options de réinscription -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-cogs me-2"></i>Options de réinscription
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <label for="nouvelle_classe" class="form-label">
                        <i class="fas fa-chalkboard me-1"></i>Nouvelle classe (optionnel)
                    </label>
                    <select class="form-select" id="nouvelle_classe" name="nouvelle_classe">
                        <option value="">Garder la classe d'origine</option>
                        @foreach($classes as $classe)
                        <option value="{{ $classe->id }}">
                            {{ $classe->nom }} ({{ $classe->niveau }} {{ $classe->section }})
                        </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Si aucune classe n'est sélectionnée, l'élève sera réinscrit dans sa classe d'origine.</small>
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <div class="d-grid gap-2 d-md-flex">
                        <button type="button" class="btn btn-outline-primary" onclick="selectAll()">
                            <i class="fas fa-check-square me-1"></i>Tout sélectionner
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="deselectAll()">
                            <i class="fas fa-square me-1"></i>Tout désélectionner
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des élèves à réinscrire -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-users me-2"></i>
                Élèves des années passées 
                <span class="badge bg-primary">{{ $elevesPassees->count() }}</span>
            </h5>
        </div>
        <div class="card-body">
            @if($elevesPassees->count() > 0)
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th width="50">
                                <input type="checkbox" class="form-check-input" id="selectAllCheckbox" onchange="toggleAll(this)">
                            </th>
                            <th>Photo</th>
                            <th>Nom Complet</th>
                            <th>Matricule</th>
                            <th>Dernière Classe</th>
                            <th>Année Scolaire</th>
                            <th>Parents</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($elevesPassees as $eleve)
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input eleve-checkbox" 
                                       name="eleves_ids[]" value="{{ $eleve->id }}">
                            </td>
                            <td>
                                <div class="avatar-sm">
                                    @if($eleve->utilisateur && $eleve->utilisateur->photo_profil && Storage::disk('public')->exists($eleve->utilisateur->photo_profil))
                                        <img src="{{ asset('images/profile_images/' . basename($eleve->utilisateur->photo_profi)) }}" 
                                             alt="Photo de {{ $eleve->utilisateur->nom_complet ?? 'élève' }}" 
                                             class="rounded-circle" 
                                             style="width: 40px; height: 40px; object-fit: cover;">
                                    @else
                                        <div class="bg-info rounded-circle d-flex align-items-center justify-content-center text-white" 
                                             style="width: 40px; height: 40px;">
                                            @if($eleve->utilisateur && $eleve->utilisateur->prenom && $eleve->utilisateur->nom)
                                                {{ substr($eleve->utilisateur->prenom, 0, 1) }}{{ substr($eleve->utilisateur->nom, 0, 1) }}
                                            @else
                                                ??
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div>
                                    <strong class="text-primary">
                                        @if($eleve->utilisateur)
                                            {{ strtoupper($eleve->utilisateur->nom) }} {{ ucfirst($eleve->utilisateur->prenom) }}
                                        @else
                                            Utilisateur manquant
                                        @endif
                                    </strong>
                                </div>
                                @if($eleve->utilisateur)
                                <small class="text-muted">
                                    <i class="fas fa-{{ $eleve->utilisateur->sexe == 'M' ? 'mars' : 'venus' }} me-1"></i>
                                    {{ $eleve->utilisateur->sexe == 'M' ? 'Masculin' : 'Féminin' }}
                                </small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $eleve->numero_etudiant }}</span>
                            </td>
                            <td>
                                @if($eleve->classe)
                                <strong>{{ $eleve->classe->nom }}</strong>
                                <br>
                                <small class="text-muted">{{ $eleve->classe->niveau }}</small>
                                @else
                                <span class="text-muted">Classe supprimée</span>
                                @endif
                            </td>
                            <td>
                                @if($eleve->anneeScolaire)
                                <span class="badge bg-warning text-dark">
                                    {{ $eleve->anneeScolaire->nom }}
                                </span>
                                <br>
                                <small class="text-muted">
                                    {{ $eleve->anneeScolaire->date_debut->format('Y') }}-{{ $eleve->anneeScolaire->date_fin->format('Y') }}
                                </small>
                                @else
                                <span class="text-muted">Année inconnue</span>
                                @endif
                            </td>
                            <td>
                                @forelse($eleve->parents as $parent)
                                <div class="mb-1 p-1 border-start border-2 border-success bg-light">
                                    <small class="fw-bold text-dark">
                                        @if($parent->utilisateur)
                                            {{ $parent->utilisateur->nom }} {{ $parent->utilisateur->prenom }}
                                        @else
                                            Parent sans utilisateur
                                        @endif
                                    </small>
                                    <br>
                                    <small class="text-muted">
                                        {{ ucfirst($parent->pivot->lien_parente ?? 'Parent') }}
                                    </small>
                                </div>
                                @empty
                                <span class="text-muted">Aucun parent</span>
                                @endforelse
                            </td>
                            <td>
                                <span class="badge bg-{{ $eleve->actif ? 'success' : 'danger' }}">
                                    {{ $eleve->actif ? 'Actif' : 'Inactif' }}
                                </span>
                                <br>
                                <small class="badge bg-info">{{ ucfirst($eleve->statut) }}</small>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Bouton de réinscription -->
            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
                <button type="submit" class="btn btn-success btn-lg" id="submitBtn" disabled>
                    <i class="fas fa-user-plus me-1"></i>
                    Réinscrire les élèves sélectionnés
                </button>
            </div>
            
            @else
            <div class="text-center text-muted py-5">
                <i class="fas fa-info-circle fa-3x mb-3"></i>
                <h5>Aucun élève à réinscrire</h5>
                <p>Tous les élèves des années passées sont déjà inscrits pour l'année scolaire active, ou il n'y a pas d'élèves dans les années précédentes.</p>
            </div>
            @endif
        </div>
    </div>
</form>

@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.eleve-checkbox');
    const submitBtn = document.getElementById('submitBtn');
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    
    // Fonction pour mettre à jour l'état du bouton submit
    function updateSubmitButton() {
        const checkedBoxes = document.querySelectorAll('.eleve-checkbox:checked');
        submitBtn.disabled = checkedBoxes.length === 0;
        
        if (checkedBoxes.length > 0) {
            submitBtn.innerHTML = `<i class="fas fa-user-plus me-1"></i>Réinscrire ${checkedBoxes.length} élève(s) sélectionné(s)`;
        } else {
            submitBtn.innerHTML = `<i class="fas fa-user-plus me-1"></i>Réinscrire les élèves sélectionnés`;
        }
    }
    
    // Écouter les changements sur les checkboxes
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSubmitButton);
    });
    
    // Confirmation avant soumission
    document.getElementById('reinscriptionForm').addEventListener('submit', function(e) {
        const checkedBoxes = document.querySelectorAll('.eleve-checkbox:checked');
        if (checkedBoxes.length === 0) {
            e.preventDefault();
            alert('Veuillez sélectionner au moins un élève à réinscrire.');
            return false;
        }
        
        const confirmed = confirm(`Êtes-vous sûr de vouloir réinscrire ${checkedBoxes.length} élève(s) pour l'année scolaire active ?`);
        if (!confirmed) {
            e.preventDefault();
            return false;
        }
    });
});

// Fonction pour sélectionner tous les élèves
function selectAll() {
    const checkboxes = document.querySelectorAll('.eleve-checkbox');
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = true;
    });
    selectAllCheckbox.checked = true;
    
    updateSubmitButton();
}

// Fonction pour désélectionner tous les élèves
function deselectAll() {
    const checkboxes = document.querySelectorAll('.eleve-checkbox');
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    selectAllCheckbox.checked = false;
    
    updateSubmitButton();
}

// Fonction pour le checkbox "Tout sélectionner"
function toggleAll(selectAllCheckbox) {
    const checkboxes = document.querySelectorAll('.eleve-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });
    
    updateSubmitButton();
}

// Fonction pour mettre à jour l'état du bouton submit (accessible globalement)
function updateSubmitButton() {
    const checkedBoxes = document.querySelectorAll('.eleve-checkbox:checked');
    const submitBtn = document.getElementById('submitBtn');
    
    if (submitBtn) {
        submitBtn.disabled = checkedBoxes.length === 0;
        
        if (checkedBoxes.length > 0) {
            submitBtn.innerHTML = `<i class="fas fa-user-plus me-1"></i>Réinscrire ${checkedBoxes.length} élève(s) sélectionné(s)`;
        } else {
            submitBtn.innerHTML = `<i class="fas fa-user-plus me-1"></i>Réinscrire les élèves sélectionnés`;
        }
    }
}
</script>
@endpush
@endsection







































