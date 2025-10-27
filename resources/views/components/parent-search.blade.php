{{-- Composant de recherche de parents amélioré --}}
<div class="parent-search-container">
    {{-- Champ de recherche --}}
    <div class="row mb-3">
        <div class="col-md-8">
            <label for="parent-search" class="form-label">
                <i class="fas fa-search me-1"></i>Rechercher un Parent
            </label>
            <div class="input-group">
                <input type="text" 
                       class="form-control" 
                       id="parent-search" 
                       placeholder="Tapez le nom, prénom, téléphone ou email..."
                       autocomplete="off">
                <button class="btn btn-outline-secondary" type="button" id="clear-search">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="form-text">
                <i class="fas fa-info-circle me-1"></i>
                Tapez au moins 2 caractères pour commencer la recherche
            </div>
        </div>
        <div class="col-md-4">
            <label for="parent-filters" class="form-label">
                <i class="fas fa-filter me-1"></i>Filtres
            </label>
            <select class="form-select" id="parent-filters">
                <option value="">Tous les parents</option>
                <option value="profession">Par profession</option>
                <option value="enfants">Par nombre d'enfants</option>
            </select>
        </div>
    </div>

    {{-- Résultats de recherche --}}
    <div id="parent-search-results" class="d-none">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="fas fa-users me-1"></i>
                    Résultats de recherche
                </h6>
                <small class="text-muted" id="search-count">0 résultat(s)</small>
            </div>
            <div class="card-body p-0">
                <div id="parents-list" class="list-group list-group-flush">
                    {{-- Les résultats seront chargés ici via AJAX --}}
                </div>
                <div id="loading-indicator" class="text-center p-3 d-none">
                    <div class="spinner-border spinner-border-sm" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <span class="ms-2">Recherche en cours...</span>
                </div>
                <div id="no-results" class="text-center p-4 d-none">
                    <i class="fas fa-search text-muted mb-2"></i>
                    <p class="text-muted mb-0">Aucun parent trouvé</p>
                </div>
                <div id="load-more-container" class="text-center p-3 d-none">
                    <button class="btn btn-outline-primary btn-sm" id="load-more-btn">
                        <i class="fas fa-plus me-1"></i>Charger plus
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Parent sélectionné --}}
    <div id="selected-parent-info" class="d-none">
        <div class="alert alert-success">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="alert-heading mb-1">
                        <i class="fas fa-check-circle me-1"></i>Parent sélectionné
                    </h6>
                    <div id="selected-parent-details">
                        {{-- Les détails du parent sélectionné seront affichés ici --}}
                    </div>
                </div>
                <button type="button" class="btn-close" id="deselect-parent"></button>
            </div>
        </div>
    </div>

    {{-- Champ caché pour le formulaire --}}
    <input type="hidden" name="parent_id" id="selected-parent-id" value="{{ old('parent_id', $studentData['parent_id'] ?? '') }}">
</div>

<style>
.parent-search-container {
    position: relative;
}

.parent-item {
    cursor: pointer;
    transition: all 0.2s ease;
    border-left: 3px solid transparent;
}

.parent-item:hover {
    background-color: #f8f9fa;
    border-left-color: #007bff;
}

.parent-item.selected {
    background-color: #e7f3ff;
    border-left-color: #007bff;
}

.parent-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.parent-details {
    flex: 1;
}

.parent-name {
    font-weight: 600;
    color: #333;
    margin-bottom: 2px;
}

.parent-contact {
    font-size: 0.875rem;
    color: #6c757d;
    margin-bottom: 2px;
}

.parent-meta {
    font-size: 0.75rem;
    color: #6c757d;
}

.parent-stats {
    text-align: right;
    font-size: 0.75rem;
    color: #6c757d;
}

.children-list {
    font-size: 0.75rem;
    color: #6c757d;
    margin-top: 4px;
}

.loading-skeleton {
    animation: pulse 1.5s ease-in-out infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('parent-search');
    const clearBtn = document.getElementById('clear-search');
    const resultsContainer = document.getElementById('parent-search-results');
    const parentsList = document.getElementById('parents-list');
    const loadingIndicator = document.getElementById('loading-indicator');
    const noResults = document.getElementById('no-results');
    const loadMoreContainer = document.getElementById('load-more-container');
    const loadMoreBtn = document.getElementById('load-more-btn');
    const searchCount = document.getElementById('search-count');
    const selectedParentInfo = document.getElementById('selected-parent-info');
    const selectedParentDetails = document.getElementById('selected-parent-details');
    const selectedParentId = document.getElementById('selected-parent-id');
    const deselectBtn = document.getElementById('deselect-parent');
    const filtersSelect = document.getElementById('parent-filters');

    let currentPage = 1;
    let currentSearch = '';
    let currentFilter = '';
    let isLoading = false;
    let hasMorePages = true;
    let selectedParent = null;

    // Délai de recherche pour éviter trop de requêtes
    let searchTimeout;

    // Initialiser si un parent est déjà sélectionné
    const initialParentId = selectedParentId.value;
    if (initialParentId) {
        loadParentDetails(initialParentId);
    }

    // Événements
    searchInput.addEventListener('input', handleSearch);
    clearBtn.addEventListener('click', clearSearch);
    loadMoreBtn.addEventListener('click', loadMoreResults);
    deselectBtn.addEventListener('click', deselectParent);
    filtersSelect.addEventListener('change', handleFilterChange);

    function handleSearch() {
        const query = searchInput.value.trim();
        
        if (query.length < 2) {
            hideResults();
            return;
        }

        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentSearch = query;
            currentPage = 1;
            hasMorePages = true;
            searchParents();
        }, 300);
    }

    function handleFilterChange() {
        currentFilter = filtersSelect.value;
        if (currentSearch.length >= 2) {
            currentPage = 1;
            hasMorePages = true;
            searchParents();
        }
    }

    function searchParents() {
        if (isLoading) return;
        
        isLoading = true;
        showLoading();

        const params = new URLSearchParams({
            search: currentSearch,
            page: currentPage,
            per_page: 10
        });

        if (currentFilter) {
            params.append('filter', currentFilter);
        }

        fetch(`/api/parents/search?${params}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    displayResults(data.data.parents, data.data.pagination);
                } else {
                    showError(data.message);
                }
            })
            .catch(error => {
                console.error('Erreur de recherche:', error);
                showError('Erreur lors de la recherche');
            })
            .finally(() => {
                isLoading = false;
                hideLoading();
            });
    }

    function displayResults(parents, pagination) {
        if (currentPage === 1) {
            parentsList.innerHTML = '';
        }

        if (parents.length === 0 && currentPage === 1) {
            showNoResults();
            return;
        }

        parents.forEach(parent => {
            const parentElement = createParentElement(parent);
            parentsList.appendChild(parentElement);
        });

        updateSearchCount(pagination.total);
        updateLoadMoreButton(pagination.has_more);
        showResults();
    }

    function createParentElement(parent) {
        const div = document.createElement('div');
        div.className = 'list-group-item parent-item';
        div.dataset.parentId = parent.id;
        
        const childrenText = parent.nb_enfants > 0 
            ? `${parent.nb_enfants} enfant(s): ${parent.enfants.map(e => e.nom_complet).join(', ')}`
            : 'Aucun enfant';

        div.innerHTML = `
            <div class="parent-info">
                <div class="parent-details">
                    <div class="parent-name">${parent.nom_complet}</div>
                    <div class="parent-contact">
                        <i class="fas fa-phone me-1"></i>${parent.telephone || 'Non renseigné'}
                        ${parent.email ? `<span class="ms-2"><i class="fas fa-envelope me-1"></i>${parent.email}</span>` : ''}
                    </div>
                    <div class="parent-meta">
                        ${parent.profession ? `<i class="fas fa-briefcase me-1"></i>${parent.profession}` : ''}
                        ${parent.adresse ? `<span class="ms-2"><i class="fas fa-map-marker-alt me-1"></i>${parent.adresse}</span>` : ''}
                    </div>
                    <div class="children-list">
                        <i class="fas fa-child me-1"></i>${childrenText}
                    </div>
                </div>
                <div class="parent-stats">
                    <div><i class="fas fa-users me-1"></i>${parent.nb_enfants}</div>
                </div>
            </div>
        `;

        div.addEventListener('click', () => selectParent(parent));
        return div;
    }

    function selectParent(parent) {
        // Retirer la sélection précédente
        document.querySelectorAll('.parent-item.selected').forEach(item => {
            item.classList.remove('selected');
        });

        // Ajouter la sélection à l'élément cliqué
        const parentElement = document.querySelector(`[data-parent-id="${parent.id}"]`);
        if (parentElement) {
            parentElement.classList.add('selected');
        }

        selectedParent = parent;
        selectedParentId.value = parent.id;
        
        // Afficher les détails du parent sélectionné
        selectedParentDetails.innerHTML = `
            <div class="fw-bold">${parent.nom_complet}</div>
            <div class="small text-muted">
                <i class="fas fa-phone me-1"></i>${parent.telephone || 'Non renseigné'}
                ${parent.email ? `<span class="ms-2"><i class="fas fa-envelope me-1"></i>${parent.email}</span>` : ''}
            </div>
            <div class="small text-muted">
                <i class="fas fa-users me-1"></i>${parent.nb_enfants} enfant(s)
                ${parent.profession ? `<span class="ms-2"><i class="fas fa-briefcase me-1"></i>${parent.profession}</span>` : ''}
            </div>
        `;
        
        selectedParentInfo.classList.remove('d-none');
    }

    function loadParentDetails(parentId) {
        fetch(`/api/parents/${parentId}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    selectParent(data.data);
                }
            })
            .catch(error => {
                console.error('Erreur lors du chargement des détails:', error);
            });
    }

    function deselectParent() {
        selectedParent = null;
        selectedParentId.value = '';
        selectedParentInfo.classList.add('d-none');
        
        // Retirer la sélection visuelle
        document.querySelectorAll('.parent-item.selected').forEach(item => {
            item.classList.remove('selected');
        });
    }

    function loadMoreResults() {
        if (!hasMorePages || isLoading) return;
        
        currentPage++;
        searchParents();
    }

    function clearSearch() {
        searchInput.value = '';
        hideResults();
        deselectParent();
    }

    function showResults() {
        resultsContainer.classList.remove('d-none');
        noResults.classList.add('d-none');
    }

    function hideResults() {
        resultsContainer.classList.add('d-none');
    }

    function showLoading() {
        loadingIndicator.classList.remove('d-none');
    }

    function hideLoading() {
        loadingIndicator.classList.add('d-none');
    }

    function showNoResults() {
        noResults.classList.remove('d-none');
        loadMoreContainer.classList.add('d-none');
    }

    function updateSearchCount(total) {
        searchCount.textContent = `${total} résultat(s)`;
    }

    function updateLoadMoreButton(hasMore) {
        hasMorePages = hasMore;
        if (hasMore) {
            loadMoreContainer.classList.remove('d-none');
        } else {
            loadMoreContainer.classList.add('d-none');
        }
    }

    function showError(message) {
        parentsList.innerHTML = `
            <div class="list-group-item text-center text-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                ${message}
            </div>
        `;
        showResults();
    }
});
</script>
