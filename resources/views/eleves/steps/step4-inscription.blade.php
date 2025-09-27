{{-- Étape 4: Inscription --}}
<div class="card shadow">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">
            <i class="fas fa-graduation-cap me-2"></i>
            Étape 4/4 - Inscription
        </h5>
    </div>
    <div class="card-body">
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form action="{{ route('eleves.store-step') }}" method="POST">
            @csrf
            <input type="hidden" name="step" value="4">

            <div class="row">
                {{-- Date d'inscription --}}
                <div class="col-md-6 mb-3">
                    <label for="date_inscription" class="form-label">
                        <i class="fas fa-calendar-plus me-1"></i>Date d'Inscription *
                    </label>
                    <input type="date" class="form-control @error('date_inscription') is-invalid @enderror" 
                           id="date_inscription" name="date_inscription" 
                           value="{{ old('date_inscription', $studentData['date_inscription'] ?? date('Y-m-d')) }}" required>
                    @error('date_inscription')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Type d'inscription --}}
                <div class="col-md-6 mb-3">
                    <label for="type_inscription" class="form-label">
                        <i class="fas fa-tag me-1"></i>Type d'Inscription *
                    </label>
                    <select class="form-select @error('type_inscription') is-invalid @enderror" 
                            id="type_inscription" name="type_inscription" required>
                        <option value="">Sélectionner le type</option>
                        <option value="nouvelle" {{ old('type_inscription', $studentData['type_inscription'] ?? '') == 'nouvelle' ? 'selected' : '' }}>Nouvelle inscription</option>
                        <option value="reinscription" {{ old('type_inscription', $studentData['type_inscription'] ?? '') == 'reinscription' ? 'selected' : '' }}>Réinscription</option>
                        <option value="transfert" {{ old('type_inscription', $studentData['type_inscription'] ?? '') == 'transfert' ? 'selected' : '' }}>Transfert</option>
                    </select>
                    @error('type_inscription')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                {{-- École d'origine --}}
                <div class="col-md-6 mb-3">
                    <label for="ecole_origine" class="form-label">
                        <i class="fas fa-school me-1"></i>École d'Origine
                    </label>
                    <input type="text" class="form-control @error('ecole_origine') is-invalid @enderror" 
                           id="ecole_origine" name="ecole_origine" 
                           value="{{ old('ecole_origine', $studentData['ecole_origine'] ?? '') }}"
                           placeholder="Nom de l'école précédente (si applicable)">
                    @error('ecole_origine')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- État d'activité --}}
                <div class="col-md-6 mb-3">
                    <label for="statut" class="form-label">
                        <i class="fas fa-user-check me-1"></i>État d'Activité *
                    </label>
                    <select class="form-select @error('statut') is-invalid @enderror" id="statut" name="statut" required>
                        <option value="">Sélectionner l'état</option>
                        <option value="inscrit" {{ old('statut', $studentData['statut'] ?? '') == 'inscrit' ? 'selected' : '' }}>Inscrit</option>
                        <option value="en_cours" {{ old('statut', $studentData['statut'] ?? 'en_cours') == 'en_cours' ? 'selected' : '' }}>En cours (Actif)</option>
                    </select>
                    @error('statut')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                {{-- Classe --}}
                <div class="col-md-6 mb-3">
                    <label for="classe_id" class="form-label">
                        <i class="fas fa-chalkboard-teacher me-1"></i>Classe *
                    </label>
                    <select class="form-select @error('classe_id') is-invalid @enderror" id="classe_id" name="classe_id" required>
                        <option value="">Sélectionner la classe</option>
                        @foreach($classes as $classe)
                            <option value="{{ $classe->id }}" {{ old('classe_id', $studentData['classe_id'] ?? '') == $classe->id ? 'selected' : '' }}>
                                {{ $classe->nom }} - {{ $classe->niveau }}
                                @if($classe->capacite_max)
                                    ({{ $classe->eleves_count ?? 0 }}/{{ $classe->capacite_max }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('classe_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Année scolaire --}}
                <div class="col-md-6 mb-3">
                    <label for="annee_scolaire_id" class="form-label">
                        <i class="fas fa-calendar-alt me-1"></i>Année Scolaire *
                    </label>
                    <select class="form-select @error('annee_scolaire_id') is-invalid @enderror" 
                            id="annee_scolaire_id" name="annee_scolaire_id" required>
                        <option value="">Sélectionner l'année</option>
                        @foreach($anneesScolarites as $annee)
                            <option value="{{ $annee->id }}" 
                                    {{ old('annee_scolaire_id', $studentData['annee_scolaire_id'] ?? ($annee->active ? $annee->id : '')) == $annee->id ? 'selected' : '' }}>
                                {{ $annee->nom }}
                                @if($annee->active)
                                    <span class="badge bg-success">Active</span>
                                @endif
                                ({{ $annee->date_debut->format('d/m/Y') }} - {{ $annee->date_fin->format('d/m/Y') }})
                            </option>
                        @endforeach
                    </select>
                    @error('annee_scolaire_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Options de paiement --}}
            <div class="card bg-light mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-credit-card me-2"></i>
                        Options de Paiement
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check mb-3">
                                <input type="hidden" name="exempte_frais" value="0">
                                <input class="form-check-input" type="checkbox" name="exempte_frais" value="1" 
                                       id="exempte_frais" {{ old('exempte_frais', $studentData['exempte_frais'] ?? '') ? 'checked' : '' }}>
                                <label class="form-check-label" for="exempte_frais">
                                    <i class="fas fa-gift me-1 text-success"></i>
                                    <strong>Exempté des frais de scolarité</strong>
                                    <br><small class="text-muted">L'élève ne paiera pas de frais de scolarité</small>
                                </label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-check mb-3">
                                <input type="hidden" name="paiement_annuel" value="0">
                                <input class="form-check-input" type="checkbox" name="paiement_annuel" value="1" 
                                       id="paiement_annuel" {{ old('paiement_annuel', $studentData['paiement_annuel'] ?? '') ? 'checked' : '' }}>
                                <label class="form-check-label" for="paiement_annuel">
                                    <i class="fas fa-calendar-check me-1 text-primary"></i>
                                    <strong>Paiement annuel</strong>
                                    <br><small class="text-muted">Paiement en une seule fois pour l'année</small>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning" id="exempte-warning" style="display: none;">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Attention:</strong> L'option "Paiement annuel" sera ignorée si l'élève est exempté des frais.
                    </div>
                </div>
            </div>

            {{-- Récapitulatif --}}
            <div class="card bg-info text-white mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-clipboard-check me-2"></i>
                        Récapitulatif de l'Inscription
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Élève:</strong> 
                                <span id="recap-student">{{ ($studentData['prenom'] ?? '') . ' ' . ($studentData['nom'] ?? '') }}</span>
                            </p>
                            <p class="mb-1"><strong>Matricule:</strong> 
                                <span id="recap-matricule">{{ $studentData['numero_etudiant'] ?? '' }}</span>
                            </p>
                            <p class="mb-1"><strong>Sexe:</strong> 
                                <span id="recap-sexe">{{ ($studentData['sexe'] ?? '') == 'M' ? 'Masculin' : (($studentData['sexe'] ?? '') == 'F' ? 'Féminin' : '') }}</span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Parent:</strong> 
                                <span id="recap-parent">
                                    @if(($studentData['parent_type'] ?? '') == 'new')
                                        {{ ($studentData['parent_prenom'] ?? '') . ' ' . ($studentData['parent_nom'] ?? '') }}
                                    @else
                                        Parent existant sélectionné
                                    @endif
                                </span>
                            </p>
                            <p class="mb-1"><strong>Lien:</strong> 
                                <span id="recap-lien">{{ ucfirst(str_replace('_', ' ', $studentData['lien_parente'] ?? '')) }}</span>
                            </p>
                            <p class="mb-0"><strong>Date d'inscription:</strong> 
                                <span id="recap-date">{{ date('d/m/Y') }}</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <hr>

            <div class="d-flex justify-content-between">
                <button type="button" class="btn btn-secondary" onclick="previousStep()">
                    <i class="fas fa-arrow-left me-2"></i>Étape Précédente
                </button>
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="fas fa-check me-2"></i>Finaliser l'Inscription
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const exempteCheckbox = document.getElementById('exempte_frais');
    const paiementAnnuelCheckbox = document.getElementById('paiement_annuel');
    const exempteWarning = document.getElementById('exempte-warning');

    function toggleWarning() {
        if (exempteCheckbox.checked && paiementAnnuelCheckbox.checked) {
            exempteWarning.style.display = 'block';
        } else {
            exempteWarning.style.display = 'none';
        }
    }

    exempteCheckbox.addEventListener('change', toggleWarning);
    paiementAnnuelCheckbox.addEventListener('change', toggleWarning);

    // Initialiser
    toggleWarning();
});

// Aller à l'étape précédente
function previousStep() {
    fetch('{{ route("eleves.previous-step") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        }
    })
    .then(response => {
        if (response.ok) {
            window.location.reload();
        }
    });
}
</script>






















