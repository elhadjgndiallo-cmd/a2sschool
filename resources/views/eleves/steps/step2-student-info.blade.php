{{-- Étape 2: Informations de l'élève --}}
<div class="card shadow">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">
            <i class="fas fa-user me-2"></i>
            Étape 2/4 - Informations de l'Élève
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
            <input type="hidden" name="step" value="2">

            <div class="row">
                {{-- Matricule (automatique) --}}
                <div class="col-md-6 mb-3">
                    <label for="numero_etudiant" class="form-label">
                        <i class="fas fa-id-card me-1"></i>Matricule *
                    </label>
                    <div class="input-group">
                        <input type="text" class="form-control @error('numero_etudiant') is-invalid @enderror" 
                               id="numero_etudiant" name="numero_etudiant" 
                               value="{{ old('numero_etudiant', $studentData['numero_etudiant'] ?? '') }}" 
                               readonly>
                        <button type="button" class="btn btn-outline-secondary" onclick="generateMatricule()">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                    <small class="text-muted">Généré automatiquement selon la configuration de l'établissement</small>
                    @error('numero_etudiant')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Prénom --}}
                <div class="col-md-6 mb-3">
                    <label for="prenom" class="form-label">
                        <i class="fas fa-user me-1"></i>Prénom *
                    </label>
                    <input type="text" class="form-control @error('prenom') is-invalid @enderror" 
                           id="prenom" name="prenom" value="{{ old('prenom', $studentData['prenom'] ?? '') }}" required>
                    @error('prenom')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                {{-- Nom --}}
                <div class="col-md-6 mb-3">
                    <label for="nom" class="form-label">
                        <i class="fas fa-user me-1"></i>Nom *
                    </label>
                    <input type="text" class="form-control @error('nom') is-invalid @enderror" 
                           id="nom" name="nom" value="{{ old('nom', $studentData['nom'] ?? '') }}" required>
                    @error('nom')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Sexe --}}
                <div class="col-md-6 mb-3">
                    <label for="sexe" class="form-label">
                        <i class="fas fa-venus-mars me-1"></i>Sexe *
                    </label>
                    <select class="form-select @error('sexe') is-invalid @enderror" id="sexe" name="sexe" required>
                        <option value="">Sélectionner le sexe</option>
                        <option value="M" {{ old('sexe', $studentData['sexe'] ?? '') == 'M' ? 'selected' : '' }}>Masculin</option>
                        <option value="F" {{ old('sexe', $studentData['sexe'] ?? '') == 'F' ? 'selected' : '' }}>Féminin</option>
                    </select>
                    @error('sexe')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                {{-- Date de naissance --}}
                <div class="col-md-6 mb-3">
                    <label for="date_naissance" class="form-label">
                        <i class="fas fa-calendar me-1"></i>Date de Naissance *
                    </label>
                    <input type="date" class="form-control @error('date_naissance') is-invalid @enderror" 
                           id="date_naissance" name="date_naissance" 
                           value="{{ old('date_naissance', $studentData['date_naissance'] ?? '') }}" required>
                    @error('date_naissance')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Lieu de naissance --}}
                <div class="col-md-6 mb-3">
                    <label for="lieu_naissance" class="form-label">
                        <i class="fas fa-map-marker-alt me-1"></i>Lieu de Naissance *
                    </label>
                    <input type="text" class="form-control @error('lieu_naissance') is-invalid @enderror" 
                           id="lieu_naissance" name="lieu_naissance" 
                           value="{{ old('lieu_naissance', $studentData['lieu_naissance'] ?? '') }}" required>
                    @error('lieu_naissance')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                {{-- Adresse --}}
                <div class="col-md-6 mb-3">
                    <label for="adresse" class="form-label">
                        <i class="fas fa-home me-1"></i>Adresse *
                    </label>
                    <textarea class="form-control @error('adresse') is-invalid @enderror" 
                              id="adresse" name="adresse" rows="3" required>{{ old('adresse', $studentData['adresse'] ?? '') }}</textarea>
                    @error('adresse')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Téléphone --}}
                <div class="col-md-3 mb-3">
                    <label for="telephone" class="form-label">
                        <i class="fas fa-phone me-1"></i>Téléphone
                    </label>
                    <input type="tel" class="form-control @error('telephone') is-invalid @enderror" 
                           id="telephone" name="telephone" 
                           value="{{ old('telephone', $studentData['telephone'] ?? '') }}">
                    @error('telephone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Situation matrimoniale --}}
                <div class="col-md-3 mb-3">
                    <label for="situation_matrimoniale" class="form-label">
                        <i class="fas fa-heart me-1"></i>Situation Matrimoniale
                    </label>
                    <select class="form-select @error('situation_matrimoniale') is-invalid @enderror" 
                            id="situation_matrimoniale" name="situation_matrimoniale">
                        <option value="">Non spécifiée</option>
                        <option value="celibataire" {{ old('situation_matrimoniale', $studentData['situation_matrimoniale'] ?? '') == 'celibataire' ? 'selected' : '' }}>Célibataire</option>
                        <option value="marie" {{ old('situation_matrimoniale', $studentData['situation_matrimoniale'] ?? '') == 'marie' ? 'selected' : '' }}>Marié(e)</option>
                        <option value="divorce" {{ old('situation_matrimoniale', $studentData['situation_matrimoniale'] ?? '') == 'divorce' ? 'selected' : '' }}>Divorcé(e)</option>
                        <option value="veuf" {{ old('situation_matrimoniale', $studentData['situation_matrimoniale'] ?? '') == 'veuf' ? 'selected' : '' }}>Veuf/Veuve</option>
                    </select>
                    @error('situation_matrimoniale')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <hr>

            <div class="d-flex justify-content-between">
                <button type="button" class="btn btn-secondary" onclick="previousStep()">
                    <i class="fas fa-arrow-left me-2"></i>Étape Précédente
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-arrow-right me-2"></i>Étape Suivante
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Générer automatiquement le matricule
function generateMatricule() {
    fetch('{{ route("eleves.generate-matricule") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.matricule) {
            document.getElementById('numero_etudiant').value = data.matricule;
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
    });
}

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

// Générer automatiquement le matricule au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    const matriculeField = document.getElementById('numero_etudiant');
    if (!matriculeField.value) {
        generateMatricule();
    }
});
</script>









































