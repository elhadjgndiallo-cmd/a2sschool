@extends('layouts.app')

@section('title', 'Nouveau Reçu de Rappel')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bell mr-2"></i>
                        Nouveau Reçu de Rappel
                    </h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('recus-rappel.store') }}" method="POST">
                        @csrf
                        
                        <!-- Champ caché pour l'ID de l'élève -->
                        <input type="hidden" name="eleve_id" id="eleve_id" value="{{ $eleve ? $eleve->id : '' }}">
                        
                        <!-- Sélection de l'élève -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="eleve_search" class="form-label">Rechercher un élève</label>
                                <div class="input-group">
                                    <input type="text" id="eleve_search" class="form-control" placeholder="Nom, prénom ou numéro d'étudiant..." value="{{ $eleve ? $eleve->utilisateur->nom . ' ' . $eleve->utilisateur->prenom : '' }}">
                                    <button type="button" class="btn btn-outline-secondary" id="search_eleve_btn">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                                <div id="eleves_results" class="mt-2" style="display: none;">
                                    <!-- Résultats de recherche -->
                                </div>
                            </div>
                        </div>

                        <!-- Informations de l'élève sélectionné -->
                        <div id="eleve_info" class="row mb-3" style="display: {{ $eleve ? 'block' : 'none' }};">
                            <div class="col-md-12">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h5>Informations de l'élève</h5>
                                        <div id="eleve_details">
                                            @if($eleve)
                                                <p><strong>Nom :</strong> {{ $eleve->utilisateur->nom }} {{ $eleve->utilisateur->prenom }}</p>
                                                <p><strong>Numéro d'étudiant :</strong> {{ $eleve->numero_etudiant }}</p>
                                                <p><strong>Classe :</strong> {{ $eleve->classe->nom ?? 'Non assignée' }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sélection des frais -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="frais_scolarite_id" class="form-label">Frais de scolarité</label>
                                <select name="frais_scolarite_id" id="frais_scolarite_id" class="form-select" required>
                                    <option value="">Sélectionnez d'abord un élève</option>
                                </select>
                                @error('frais_scolarite_id')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Informations des frais -->
                        <div id="frais_info" class="row mb-3" style="display: none;">
                            <div class="col-md-12">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h5>Détails des frais</h5>
                                        <div id="frais_details"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Montant à payer (case vide pour le comptable) -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="montant_a_payer" class="form-label">Montant à payer (optionnel)</label>
                                <input type="number" name="montant_a_payer" id="montant_a_payer" class="form-control" step="0.01" min="0">
                                <small class="form-text text-muted">Laissez vide pour que le comptable remplisse manuellement</small>
                                @error('montant_a_payer')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Date d'échéance -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="date_echeance" class="form-label">Date d'échéance</label>
                                <input type="date" name="date_echeance" id="date_echeance" class="form-control" required>
                                @error('date_echeance')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Observations -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="observations" class="form-label">Observations</label>
                                <textarea name="observations" id="observations" class="form-control" rows="3" placeholder="Observations supplémentaires..."></textarea>
                                @error('observations')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Boutons -->
                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-1"></i>
                                    Créer le Reçu de Rappel
                                </button>
                                <a href="{{ route('recus-rappel.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left mr-1"></i>
                                    Retour
                                </a>
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
    const eleveSearch = document.getElementById('eleve_search');
    const searchBtn = document.getElementById('search_eleve_btn');
    const elevesResults = document.getElementById('eleves_results');
    const eleveInfo = document.getElementById('eleve_info');
    const eleveDetails = document.getElementById('eleve_details');
    const fraisSelect = document.getElementById('frais_scolarite_id');
    const fraisInfo = document.getElementById('frais_info');
    const fraisDetails = document.getElementById('frais_details');

    // Recherche d'élèves
    function searchEleves() {
        const search = eleveSearch.value.trim();
        if (search.length < 2) {
            elevesResults.style.display = 'none';
            return;
        }

        // Utiliser XMLHttpRequest pour une meilleure compatibilité
        const xhr = new XMLHttpRequest();
        xhr.open('GET', `/recus-rappel/search-eleves?search=${encodeURIComponent(search)}`, true);
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.withCredentials = true;
        
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    try {
                        const data = JSON.parse(xhr.responseText);
                        
                        if (data.error) {
                            elevesResults.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
                            elevesResults.style.display = 'block';
                            return;
                        }
                        
                        if (data.length > 0) {
                            let html = '<div class="list-group">';
                            data.forEach(eleve => {
                                html += `
                                    <a href="#" class="list-group-item list-group-item-action" onclick="selectEleve(${eleve.id}, '${eleve.utilisateur.nom}', '${eleve.utilisateur.prenom}', '${eleve.numero_etudiant}', '${eleve.classe?.nom || 'N/A'}')">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1">${eleve.utilisateur.prenom} ${eleve.utilisateur.nom}</h6>
                                            <small>${eleve.numero_etudiant}</small>
                                        </div>
                                        <p class="mb-1">Classe: ${eleve.classe?.nom || 'N/A'}</p>
                                    </a>
                                `;
                            });
                            html += '</div>';
                            elevesResults.innerHTML = html;
                            elevesResults.style.display = 'block';
                        } else {
                            elevesResults.innerHTML = '<div class="alert alert-info">Aucun élève trouvé</div>';
                            elevesResults.style.display = 'block';
                        }
                    } catch (e) {
                        console.error('Erreur parsing JSON:', e);
                        elevesResults.innerHTML = '<div class="alert alert-danger">Erreur lors du traitement de la réponse</div>';
                        elevesResults.style.display = 'block';
                    }
                } else {
                    console.error('Erreur HTTP:', xhr.status, xhr.statusText);
                    elevesResults.innerHTML = `<div class="alert alert-danger">Erreur HTTP ${xhr.status}: ${xhr.statusText}</div>`;
                    elevesResults.style.display = 'block';
                }
            }
        };
        
        xhr.send();
    }

    // Sélection d'un élève
    window.selectEleve = function(eleveId, nom, prenom, numero, classe) {
        eleveSearch.value = `${prenom} ${nom} (${numero})`;
        elevesResults.style.display = 'none';
        
        // Définir l'ID de l'élève dans le champ caché
        document.getElementById('eleve_id').value = eleveId;
        
        // Afficher les informations de l'élève
        eleveDetails.innerHTML = `
            <p><strong>Nom:</strong> ${prenom} ${nom}</p>
            <p><strong>Numéro d'étudiant:</strong> ${numero}</p>
            <p><strong>Classe:</strong> ${classe}</p>
        `;
        eleveInfo.style.display = 'block';

        // Charger les frais de scolarité de l'élève
        loadFraisScolarite(eleveId);
    };

    // Charger les frais de scolarité
    function loadFraisScolarite(eleveId) {
        fraisSelect.innerHTML = '<option value="">Chargement...</option>';
        
        const xhr = new XMLHttpRequest();
        xhr.open('GET', `/recus-rappel/eleve/${eleveId}/frais`, true);
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.withCredentials = true;
        
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    try {
                        const data = JSON.parse(xhr.responseText);
                        
                        if (data.error) {
                            fraisSelect.innerHTML = `<option value="">Erreur: ${data.error}</option>`;
                            return;
                        }
                        
                        if (data.length > 0) {
                            let html = '<option value="">Sélectionnez un frais de scolarité</option>';
                            data.forEach(frais => {
                                const montantPaye = frais.paiements ? frais.paiements.reduce((sum, p) => sum + parseFloat(p.montant_paye), 0) : 0;
                                const montantRestant = parseFloat(frais.montant) - montantPaye;
                                html += `<option value="${frais.id}" data-montant="${frais.montant}" data-paye="${montantPaye}" data-restant="${montantRestant}">${frais.libelle} (Restant: ${montantRestant.toLocaleString()} GNF)</option>`;
                            });
                            fraisSelect.innerHTML = html;
                        } else {
                            fraisSelect.innerHTML = '<option value="">Aucun frais en attente trouvé</option>';
                        }
                    } catch (e) {
                        console.error('Erreur parsing JSON:', e);
                        fraisSelect.innerHTML = '<option value="">Erreur lors du traitement de la réponse</option>';
                    }
                } else {
                    console.error('Erreur HTTP:', xhr.status, xhr.statusText);
                    fraisSelect.innerHTML = `<option value="">Erreur HTTP ${xhr.status}: ${xhr.statusText}</option>`;
                }
            }
        };
        
        xhr.send();
    }

    // Événements
    searchBtn.addEventListener('click', searchEleves);
    eleveSearch.addEventListener('keyup', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            searchEleves();
        }
    });

    // Changement de frais
    fraisSelect.addEventListener('change', function() {
        if (this.value) {
            const selectedOption = this.options[this.selectedIndex];
            const montant = parseFloat(selectedOption.dataset.montant);
            const paye = parseFloat(selectedOption.dataset.paye);
            const restant = parseFloat(selectedOption.dataset.restant);
            
            // Afficher les détails des frais
            fraisDetails.innerHTML = `
                <p><strong>Libellé:</strong> ${selectedOption.text}</p>
                <p><strong>Montant total:</strong> ${montant.toLocaleString()} GNF</p>
                <p><strong>Montant payé:</strong> ${paye.toLocaleString()} GNF</p>
                <p><strong>Montant restant:</strong> ${restant.toLocaleString()} GNF</p>
            `;
            fraisInfo.style.display = 'block';
            
            // Définir le montant maximum pour le champ montant à payer
            document.getElementById('montant_a_payer').max = restant;
        } else {
            fraisInfo.style.display = 'none';
        }
    });

    // Définir la date d'échéance par défaut (dans 30 jours)
    const today = new Date();
    const futureDate = new Date(today.getTime() + (30 * 24 * 60 * 60 * 1000));
    document.getElementById('date_echeance').value = futureDate.toISOString().split('T')[0];
    
    // Si un élève est pré-sélectionné, charger ses frais
    const eleveId = document.getElementById('eleve_id').value;
    const fraisId = '{{ $frais ? $frais->id : "" }}';
    
    if (eleveId && eleveId !== '') {
        // Charger les frais de l'élève
        loadFraisScolarite(eleveId);
        
        // Pré-sélectionner le frais spécifique si fourni
        if (fraisId && fraisId !== '') {
            setTimeout(() => {
                const fraisSelect = document.getElementById('frais_scolarite_id');
                fraisSelect.value = fraisId;
                
                // Déclencher l'événement change pour afficher les détails
                const event = new Event('change');
                fraisSelect.dispatchEvent(event);
            }, 1000); // Attendre que les frais soient chargés
        }
    }
});
</script>
@endsection
