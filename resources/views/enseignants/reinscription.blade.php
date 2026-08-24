@extends('layouts.app')

@section('title', 'Réinscription des Enseignants')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-chalkboard-teacher me-2"></i>
        Réinscription des Enseignants
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('enseignants.index') }}" class="btn btn-outline-secondary">
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

<!-- Information sur l'année scolaire active -->
<div class="alert alert-info">
    <i class="fas fa-info-circle me-2"></i>
    <strong>Année scolaire active :</strong> 
    {{ $anneeScolaireActive ? $anneeScolaireActive->nom : 'Aucune année active' }}
    @if($anneeScolaireActive)
    ({{ $anneeScolaireActive->date_debut->format('Y') }}-{{ $anneeScolaireActive->date_fin->format('Y') }})
    @endif
</div>

<!-- Filtres de recherche -->
<form method="GET" action="{{ route('enseignants.reinscription') }}" id="filterForm" class="mb-3">
    <div class="row g-2">
        <div class="col-12 col-sm-6 col-md-3">
            <input type="text" class="form-control" id="search" name="search"
                   value="{{ request('search') }}" placeholder="Rechercher un enseignant...">
        </div>
        <div class="col-12 col-sm-6 col-md-2">
            <input type="text" class="form-control" id="numero_employe" name="numero_employe"
                   value="{{ request('numero_employe') }}" placeholder="Numéro employé...">
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <select class="form-select" id="filter_annee" name="annee_scolaire_id" title="Année scolaire">
                <option value="">Toutes les années</option>
                @foreach($anneesPassees as $annee)
                <option value="{{ $annee->id }}" {{ request('annee_scolaire_id') == $annee->id ? 'selected' : '' }}>
                    {{ $annee->nom }}
                </option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-sm-4 col-md-1">
            <select class="form-select" id="per_page" name="per_page" onchange="document.getElementById('filterForm').submit();" title="Par page">
                <option value="20" {{ request('per_page') == '20' ? 'selected' : '' }}>20</option>
                <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50</option>
                <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100</option>
            </select>
        </div>
        <div class="col-6 col-sm-4 col-md-3">
            <div class="d-flex gap-1">
                <button type="submit" class="btn btn-primary flex-fill">
                    <i class="fas fa-search"></i>
                    <span class="d-none d-sm-inline">Filtrer</span>
                </button>
                <a href="{{ route('enseignants.reinscription') }}" class="btn btn-outline-secondary" title="Réinitialiser">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </div>
    </div>
</form>

<form method="POST" action="{{ route('enseignants.reinscription.process') }}" id="reinscriptionForm">
    @csrf

    <!-- Liste des enseignants -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>Enseignants des années passées
            </h5>
            <span class="badge bg-primary">
                {{ $enseignantsPassees->total() }} enseignant(s)
            </span>
        </div>
        <div class="card-body">
            @if($enseignantsPassees->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">
                                <input type="checkbox" id="selectAll" class="form-check-input">
                            </th>
                            <th>Nom Complet</th>
                            <th>Numéro Employé</th>
                            <th>Spécialité</th>
                            <th>Statut</th>
                            <th>Année Scolaire</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($enseignantsPassees as $enseignant)
                        <tr>
                            <td>
                                <input type="checkbox" name="enseignants_ids[]" 
                                       value="{{ $enseignant->id }}" 
                                       class="form-check-input enseignant-checkbox">
                            </td>
                            <td>
                                <strong>{{ $enseignant->utilisateur->prenom }} {{ $enseignant->utilisateur->nom }}</strong>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $enseignant->numero_employe }}</span>
                            </td>
                            <td>{{ $enseignant->specialite ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-info">{{ ucfirst($enseignant->statut) }}</span>
                            </td>
                            <td>{{ $enseignant->anneeScolaire->nom ?? 'N/A' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($enseignantsPassees->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    <small class="text-muted">
                        Affichage de {{ $enseignantsPassees->firstItem() ?? 0 }} à {{ $enseignantsPassees->lastItem() ?? 0 }} sur {{ $enseignantsPassees->total() }} enseignant{{ $enseignantsPassees->total() > 1 ? 's' : '' }}
                    </small>
                </div>
                <div>
                    {{ $enseignantsPassees->appends(request()->query())->links('vendor.pagination.custom') }}
                </div>
            </div>
            @endif
            
            <!-- Bouton de réinscription -->
            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
                <button type="submit" class="btn btn-success btn-lg" id="submitBtn" disabled>
                    <i class="fas fa-user-plus me-1"></i>
                    Réinscrire les enseignants sélectionnés
                </button>
            </div>
            
            @else
            <div class="alert alert-info mb-0">
                <i class="fas fa-info-circle me-2"></i>
                Aucun enseignant des années passées trouvé. Tous les enseignants sont déjà inscrits dans l'année active.
            </div>
            @endif
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const enseignantCheckboxes = document.querySelectorAll('.enseignant-checkbox');
    const submitBtn = document.getElementById('submitBtn');
    
    // Sélectionner/Désélectionner tous
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            enseignantCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateSubmitButton();
        });
    }
    
    // Mettre à jour le bouton de soumission
    enseignantCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSubmitButton();
            updateSelectAllCheckbox();
        });
    });
    
    function updateSubmitButton() {
        const checkedCount = document.querySelectorAll('.enseignant-checkbox:checked').length;
        submitBtn.disabled = checkedCount === 0;
        
        if (checkedCount > 0) {
            submitBtn.textContent = `Réinscrire ${checkedCount} enseignant(s) sélectionné(s)`;
        } else {
            submitBtn.innerHTML = '<i class="fas fa-user-plus me-1"></i>Réinscrire les enseignants sélectionnés';
        }
    }
    
    function updateSelectAllCheckbox() {
        const totalCheckboxes = enseignantCheckboxes.length;
        const checkedCheckboxes = document.querySelectorAll('.enseignant-checkbox:checked').length;
        
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = totalCheckboxes === checkedCheckboxes && totalCheckboxes > 0;
            selectAllCheckbox.indeterminate = checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes;
        }
    }
    
    // Confirmation avant soumission
    document.getElementById('reinscriptionForm').addEventListener('submit', function(e) {
        const checkedCount = document.querySelectorAll('.enseignant-checkbox:checked').length;
        
        if (checkedCount === 0) {
            e.preventDefault();
            alert('Veuillez sélectionner au moins un enseignant à réinscrire.');
            return false;
        }
        
        const confirmation = confirm(`Vous êtes sur le point de réinscrire ${checkedCount} enseignant(s). Continuer ?`);
        if (!confirmation) {
            e.preventDefault();
            return false;
        }
    });
});
</script>
@endsection
