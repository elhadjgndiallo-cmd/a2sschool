{{-- Étape 3: Informations du parent --}}
<div class="card shadow">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">
            <i class="fas fa-users me-2"></i>
            Étape 3/4 - Informations du Parent/Tuteur
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
            <input type="hidden" name="step" value="3">

            {{-- Type de parent --}}
            <div class="alert alert-info mb-4">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Information:</strong> Vous pouvez soit sélectionner un parent existant, soit créer un nouveau parent.
            </div>

            <div class="row mb-4">
                <div class="col-12">
                    <label class="form-label">
                        <i class="fas fa-user-check me-1"></i>Type de Parent *
                    </label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="parent_type" id="existing_parent" 
                               value="existing" {{ old('parent_type', $studentData['parent_type'] ?? '') == 'existing' ? 'checked' : '' }}>
                        <label class="form-check-label" for="existing_parent">
                            Sélectionner un parent existant
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="parent_type" id="new_parent" 
                               value="new" {{ old('parent_type', $studentData['parent_type'] ?? 'new') == 'new' ? 'checked' : '' }}>
                        <label class="form-check-label" for="new_parent">
                            Créer un nouveau parent
                        </label>
                    </div>
                </div>
            </div>

            {{-- Sélection parent existant --}}
            <div id="existing-parent-section" class="d-none">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="parent_id" class="form-label">
                            <i class="fas fa-search me-1"></i>Sélectionner le Parent
                        </label>
                        <select class="form-select @error('parent_id') is-invalid @enderror" id="parent_id" name="parent_id">
                            <option value="">Choisir un parent</option>
                            @foreach($parents as $parent)
                                <option value="{{ $parent->id }}" {{ old('parent_id', $studentData['parent_id'] ?? '') == $parent->id ? 'selected' : '' }}>
                                    {{ $parent->utilisateur->nom ?? '' }} {{ $parent->utilisateur->prenom ?? '' }}
                                    @if($parent->utilisateur->telephone)
                                        - {{ $parent->utilisateur->telephone }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('parent_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Nouveau parent --}}
            <div id="new-parent-section">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="parent_prenom" class="form-label">
                            <i class="fas fa-user me-1"></i>Prénom du Parent *
                        </label>
                        <input type="text" class="form-control @error('parent_prenom') is-invalid @enderror" 
                               id="parent_prenom" name="parent_prenom" 
                               value="{{ old('parent_prenom', $studentData['parent_prenom'] ?? '') }}">
                        @error('parent_prenom')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="parent_nom" class="form-label">
                            <i class="fas fa-user me-1"></i>Nom du Parent *
                        </label>
                        <input type="text" class="form-control @error('parent_nom') is-invalid @enderror" 
                               id="parent_nom" name="parent_nom" 
                               value="{{ old('parent_nom', $studentData['parent_nom'] ?? '') }}">
                        @error('parent_nom')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="parent_telephone" class="form-label">
                            <i class="fas fa-phone me-1"></i>Téléphone (Optionnel)
                        </label>
                        <input type="tel" class="form-control @error('parent_telephone') is-invalid @enderror" 
                               id="parent_telephone" name="parent_telephone" 
                               value="{{ old('parent_telephone', $studentData['parent_telephone'] ?? '') }}">
                        @error('parent_telephone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="parent_email" class="form-label">
                            <i class="fas fa-envelope me-1"></i>Email (Optionnel)
                        </label>
                        <input type="email" class="form-control @error('parent_email') is-invalid @enderror" 
                               id="parent_email" name="parent_email" 
                               value="{{ old('parent_email', $studentData['parent_email'] ?? '') }}">
                        @error('parent_email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="parent_adresse" class="form-label">
                        <i class="fas fa-home me-1"></i>Adresse Complète (Optionnel)
                    </label>
                    <textarea class="form-control @error('parent_adresse') is-invalid @enderror" 
                              id="parent_adresse" name="parent_adresse" rows="3">{{ old('parent_adresse', $studentData['parent_adresse'] ?? '') }}</textarea>
                    @error('parent_adresse')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Lien de parenté --}}
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="lien_parente" class="form-label">
                        <i class="fas fa-heart me-1"></i>Lien de Parenté *
                    </label>
                    <select class="form-select @error('lien_parente') is-invalid @enderror" id="lien_parente" name="lien_parente" required>
                        <option value="">Sélectionner le lien</option>
                        <option value="pere" {{ old('lien_parente', $studentData['lien_parente'] ?? '') == 'pere' ? 'selected' : '' }}>Père</option>
                        <option value="mere" {{ old('lien_parente', $studentData['lien_parente'] ?? '') == 'mere' ? 'selected' : '' }}>Mère</option>
                        <option value="tuteur" {{ old('lien_parente', $studentData['lien_parente'] ?? '') == 'tuteur' ? 'selected' : '' }}>Tuteur</option>
                        <option value="tutrice" {{ old('lien_parente', $studentData['lien_parente'] ?? '') == 'tutrice' ? 'selected' : '' }}>Tutrice</option>
                        <option value="grand_pere" {{ old('lien_parente', $studentData['lien_parente'] ?? '') == 'grand_pere' ? 'selected' : '' }}>Grand-père</option>
                        <option value="grand_mere" {{ old('lien_parente', $studentData['lien_parente'] ?? '') == 'grand_mere' ? 'selected' : '' }}>Grand-mère</option>
                        <option value="oncle" {{ old('lien_parente', $studentData['lien_parente'] ?? '') == 'oncle' ? 'selected' : '' }}>Oncle</option>
                        <option value="tante" {{ old('lien_parente', $studentData['lien_parente'] ?? '') == 'tante' ? 'selected' : '' }}>Tante</option>
                        <option value="autre" {{ old('lien_parente', $studentData['lien_parente'] ?? '') == 'autre' ? 'selected' : '' }}>Autre</option>
                    </select>
                    @error('lien_parente')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6" id="autre-lien-section" style="display: none;">
                    <label for="autre_lien_parente" class="form-label">
                        <i class="fas fa-edit me-1"></i>Préciser le Lien
                    </label>
                    <input type="text" class="form-control @error('autre_lien_parente') is-invalid @enderror" 
                           id="autre_lien_parente" name="autre_lien_parente" 
                           value="{{ old('autre_lien_parente', $studentData['autre_lien_parente'] ?? '') }}"
                           placeholder="Préciser le lien de parenté">
                    @error('autre_lien_parente')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Options supplémentaires --}}
            <div class="row">
                <div class="col-12">
                    <div class="form-check mb-2">
                        <input type="hidden" name="responsable_legal" value="0">
                        <input class="form-check-input" type="checkbox" name="responsable_legal" value="1" 
                               id="responsable_legal" {{ old('responsable_legal', $studentData['responsable_legal'] ?? '') ? 'checked' : '' }}>
                        <label class="form-check-label" for="responsable_legal">
                            <i class="fas fa-gavel me-1"></i>Responsable légal
                        </label>
                    </div>

                    <div class="form-check mb-2">
                        <input type="hidden" name="contact_urgence" value="0">
                        <input class="form-check-input" type="checkbox" name="contact_urgence" value="1" 
                               id="contact_urgence" {{ old('contact_urgence', $studentData['contact_urgence'] ?? '') ? 'checked' : '' }}>
                        <label class="form-check-label" for="contact_urgence">
                            <i class="fas fa-phone-alt me-1"></i>Contact d'urgence
                        </label>
                    </div>

                    <div class="form-check mb-3">
                        <input type="hidden" name="autorise_sortie" value="0">
                        <input class="form-check-input" type="checkbox" name="autorise_sortie" value="1" 
                               id="autorise_sortie" {{ old('autorise_sortie', $studentData['autorise_sortie'] ?? '') ? 'checked' : '' }}>
                        <label class="form-check-label" for="autorise_sortie">
                            <i class="fas fa-door-open me-1"></i>Autorisé à récupérer l'élève
                        </label>
                    </div>
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
document.addEventListener('DOMContentLoaded', function() {
    const existingParentRadio = document.getElementById('existing_parent');
    const newParentRadio = document.getElementById('new_parent');
    const existingParentSection = document.getElementById('existing-parent-section');
    const newParentSection = document.getElementById('new-parent-section');
    const lienParenteSelect = document.getElementById('lien_parente');
    const autreLienSection = document.getElementById('autre-lien-section');

    function toggleParentSections() {
        if (existingParentRadio.checked) {
            existingParentSection.classList.remove('d-none');
            newParentSection.classList.add('d-none');
            
            // Rendre les champs nouveau parent optionnels
            newParentSection.querySelectorAll('input[required]').forEach(input => {
                input.removeAttribute('required');
            });
        } else {
            existingParentSection.classList.add('d-none');
            newParentSection.classList.remove('d-none');
            
            // Rendre les champs nouveau parent requis
            document.getElementById('parent_prenom').setAttribute('required', 'required');
            document.getElementById('parent_nom').setAttribute('required', 'required');
        }
    }

    function toggleAutreLien() {
        if (lienParenteSelect.value === 'autre') {
            autreLienSection.style.display = 'block';
            document.getElementById('autre_lien_parente').setAttribute('required', 'required');
        } else {
            autreLienSection.style.display = 'none';
            document.getElementById('autre_lien_parente').removeAttribute('required');
        }
    }

    existingParentRadio.addEventListener('change', toggleParentSections);
    newParentRadio.addEventListener('change', toggleParentSections);
    lienParenteSelect.addEventListener('change', toggleAutreLien);

    // Initialiser l'état
    toggleParentSections();
    toggleAutreLien();
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









































