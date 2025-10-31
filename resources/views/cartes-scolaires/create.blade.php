@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-id-card text-primary me-2"></i>
                        Créer une Nouvelle Carte Scolaire
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('cartes-scolaires.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Retour
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('cartes-scolaires.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Informations de la carte</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="eleve_id" class="form-label">Élève <span class="text-danger">*</span></label>
                                            <input type="text" 
                                                   class="form-control @error('eleve_id') is-invalid @enderror" 
                                                   id="eleve_search" 
                                                   placeholder="Rechercher un élève par nom, prénom ou matricule..."
                                                   autocomplete="off">
                                            <input type="hidden" 
                                                   id="eleve_id" 
                                                   name="eleve_id" 
                                                   value="{{ old('eleve_id', $selectedEleveId ?? '') }}"
                                                   required>
                                            <div id="eleve_search_results" class="list-group mt-2" style="max-height: 300px; overflow-y: auto; display: none;"></div>
                                            <div id="selected_eleve" class="mt-2">
                                                @if(isset($selectedEleveId) && $selectedEleveId)
                                                    @php
                                                        $preSelectedEleve = $eleves->firstWhere('id', $selectedEleveId);
                                                    @endphp
                                                    @if($preSelectedEleve)
                                                        <div class="alert alert-success">
                                                            <strong><i class="fas fa-check-circle me-2"></i>Élève sélectionné :</strong>
                                                            {{ $preSelectedEleve->utilisateur->nom ?? '' }} {{ $preSelectedEleve->utilisateur->prenom ?? '' }} 
                                                            (Matricule: {{ $preSelectedEleve->numero_etudiant ?? 'N/A' }}) 
                                                            - {{ $preSelectedEleve->classe->nom ?? 'Non assigné' }}
                                                            <button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="clearEleveSelection()">
                                                                <i class="fas fa-times"></i> Changer
                                                            </button>
                                                        </div>
                                                    @endif
                                                @endif
                                            </div>
                                            @error('eleve_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="type_carte" class="form-label">Type de carte <span class="text-danger">*</span></label>
                                            <select class="form-select @error('type_carte') is-invalid @enderror" 
                                                    id="type_carte" 
                                                    name="type_carte" 
                                                    required>
                                                <option value="">Sélectionner un type</option>
                                                <option value="standard" {{ old('type_carte') == 'standard' ? 'selected' : '' }}>Standard</option>
                                                <option value="temporaire" {{ old('type_carte') == 'temporaire' ? 'selected' : '' }}>Temporaire</option>
                                                <option value="remplacement" {{ old('type_carte') == 'remplacement' ? 'selected' : '' }}>Remplacement</option>
                                            </select>
                                            @error('type_carte')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">
                                                <strong>Standard :</strong> Carte normale pour l'année scolaire<br>
                                                <strong>Temporaire :</strong> Carte de courte durée<br>
                                                <strong>Remplacement :</strong> Carte de remplacement
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="date_emission" class="form-label">Date d'émission <span class="text-danger">*</span></label>
                                            <input type="date" 
                                                   class="form-control @error('date_emission') is-invalid @enderror" 
                                                   id="date_emission" 
                                                   name="date_emission" 
                                                   value="{{ old('date_emission', now()->format('Y-m-d')) }}" 
                                                   required>
                                            @error('date_emission')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="date_expiration" class="form-label">Date d'expiration <span class="text-danger">*</span></label>
                                            <input type="date" 
                                                   class="form-control @error('date_expiration') is-invalid @enderror" 
                                                   id="date_expiration" 
                                                   name="date_expiration" 
                                                   value="{{ old('date_expiration') }}" 
                                                   required>
                                            @error('date_expiration')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Informations supplémentaires</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="observations" class="form-label">Observations</label>
                                            <textarea class="form-control @error('observations') is-invalid @enderror" 
                                                      id="observations" 
                                                      name="observations" 
                                                      rows="4" 
                                                      placeholder="Observations sur la carte...">{{ old('observations') }}</textarea>
                                            @error('observations')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="alert alert-info">
                                            <h6><i class="fas fa-info-circle me-2"></i>Informations importantes :</h6>
                                            <ul class="mb-0">
                                                <li>Le numéro de carte sera généré automatiquement</li>
                                                <li>Un QR code sera créé avec les informations de l'élève</li>
                                                <li>Si vous créez une carte standard, les autres cartes actives de l'élève seront annulées</li>
                                                <li>La carte sera immédiatement active après création</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-end">
                                    <a href="{{ route('cartes-scolaires.index') }}" class="btn btn-secondary me-2">
                                        <i class="fas fa-times me-2"></i>Annuler
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Créer la carte
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
document.addEventListener('DOMContentLoaded', function() {
    const typeCarteSelect = document.getElementById('type_carte');
    const dateExpirationInput = document.getElementById('date_expiration');
    
    // Définir la date d'expiration par défaut selon le type de carte
    typeCarteSelect.addEventListener('change', function() {
        const today = new Date();
        let expirationDate = new Date();
        
        switch(this.value) {
            case 'standard':
                // Carte standard : fin d'année scolaire (30 juin)
                expirationDate = new Date(today.getFullYear(), 5, 30); // Juin = mois 5 (0-indexé)
                if (expirationDate < today) {
                    expirationDate = new Date(today.getFullYear() + 1, 5, 30);
                }
                break;
            case 'temporaire':
                // Carte temporaire : 3 mois
                expirationDate.setMonth(today.getMonth() + 3);
                break;
            case 'remplacement':
                // Carte de remplacement : 1 mois
                expirationDate.setMonth(today.getMonth() + 1);
                break;
        }
        
        if (this.value && !dateExpirationInput.value) {
            dateExpirationInput.value = expirationDate.toISOString().split('T')[0];
        }
    });

    // Système de recherche d'élèves
    const eleveSearchInput = document.getElementById('eleve_search');
    const eleveIdInput = document.getElementById('eleve_id');
    const searchResults = document.getElementById('eleve_search_results');
    const selectedEleveDiv = document.getElementById('selected_eleve');
    const eleves = @json($eleves);

    let searchTimeout;

    eleveSearchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const searchTerm = this.value.toLowerCase().trim();

        if (searchTerm.length < 2) {
            searchResults.style.display = 'none';
            return;
        }

        searchTimeout = setTimeout(() => {
            const filtered = eleves.filter(eleve => {
                const nom = (eleve.utilisateur?.nom || '').toLowerCase();
                const prenom = (eleve.utilisateur?.prenom || '').toLowerCase();
                const matricule = (eleve.numero_etudiant || '').toLowerCase();
                const classe = (eleve.classe?.nom || '').toLowerCase();
                
                return nom.includes(searchTerm) || 
                       prenom.includes(searchTerm) || 
                       matricule.includes(searchTerm) ||
                       classe.includes(searchTerm);
            });

            if (filtered.length === 0) {
                searchResults.innerHTML = '<div class="list-group-item text-muted">Aucun élève trouvé</div>';
                searchResults.style.display = 'block';
            } else {
                searchResults.innerHTML = filtered.map(eleve => `
                    <button type="button" class="list-group-item list-group-item-action" 
                            onclick="selectEleve(${eleve.id}, '${eleve.utilisateur?.nom || ''} ${eleve.utilisateur?.prenom || ''}', '${eleve.numero_etudiant || 'N/A'}', '${eleve.classe?.nom || 'Non assigné'}')">
                        <strong>${eleve.utilisateur?.nom || ''} ${eleve.utilisateur?.prenom || ''}</strong>
                        <br>
                        <small class="text-muted">
                            Matricule: ${eleve.numero_etudiant || 'N/A'} | 
                            Classe: ${eleve.classe?.nom || 'Non assigné'}
                        </small>
                    </button>
                `).join('');
                searchResults.style.display = 'block';
            }
        }, 300);
    });

    // Fonction pour sélectionner un élève
    window.selectEleve = function(id, nom, matricule, classe) {
        eleveIdInput.value = id;
        eleveSearchInput.value = '';
        searchResults.style.display = 'none';
        
        selectedEleveDiv.innerHTML = `
            <div class="alert alert-success">
                <strong><i class="fas fa-check-circle me-2"></i>Élève sélectionné :</strong>
                ${nom} (Matricule: ${matricule}) - ${classe}
                <button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="clearEleveSelection()">
                    <i class="fas fa-times"></i> Changer
                </button>
            </div>
        `;
    };

    // Si un élève est déjà pré-sélectionné (venant de l'URL), le champ de recherche reste fonctionnel
    @if(isset($selectedEleveId) && $selectedEleveId)
        // L'élève est déjà pré-sélectionné via le template Blade ci-dessus
        // Le champ de recherche reste actif pour permettre de changer d'élève
    @endif

    // Fonction pour effacer la sélection
    window.clearEleveSelection = function() {
        eleveIdInput.value = '';
        selectedEleveDiv.innerHTML = '';
        eleveSearchInput.focus();
    };

    // Masquer les résultats si on clique ailleurs
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#eleve_search') && !e.target.closest('#eleve_search_results')) {
            searchResults.style.display = 'none';
        }
    });
});
</script>
@endsection



























