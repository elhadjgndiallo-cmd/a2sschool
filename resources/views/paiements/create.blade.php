@extends('layouts.app')

@section('title', 'Créer un Frais de Scolarité')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-plus mr-2"></i>
                        Créer un Frais de Scolarité
                    </h3>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('paiements.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="eleve_id">Élève <span class="text-danger">*</span></label>
                                    <select name="eleve_id" id="eleve_id" class="form-control @error('eleve_id') is-invalid @enderror" required>
                                        <option value="">Sélectionner un élève</option>
                                        @foreach($eleves as $eleve)
                                            <option value="{{ $eleve->id }}" data-classe-id="{{ $eleve->classe_id }}" {{ (old('eleve_id', $selectedEleveId) == $eleve->id) ? 'selected' : '' }}>
                                                {{ $eleve->utilisateur->nom ?? 'N/A' }} {{ $eleve->utilisateur->prenom ?? 'N/A' }} - {{ $eleve->numero_etudiant ?? 'N/A' }} - {{ $eleve->classe->nom ?? 'N/A' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Seuls les élèves sans frais de scolarité existants sont listés.</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="type_frais">Type de Frais <span class="text-danger">*</span></label>
                                    <select name="type_frais" id="type_frais" class="form-control @error('type_frais') is-invalid @enderror" required>
                                        <option value="">Sélectionner le type</option>
                                        <option value="inscription" {{ old('type_frais') == 'inscription' ? 'selected' : '' }}>Inscription</option>
                                        <option value="scolarite" {{ old('type_frais') == 'scolarite' ? 'selected' : '' }}>Scolarité</option>
                                        <option value="cantine" {{ old('type_frais') == 'cantine' ? 'selected' : '' }}>Cantine</option>
                                        <option value="transport" {{ old('type_frais') == 'transport' ? 'selected' : '' }}>Transport</option>
                                        <option value="activites" {{ old('type_frais') == 'activites' ? 'selected' : '' }}>Activités</option>
                                        <option value="autre" {{ old('type_frais') == 'autre' ? 'selected' : '' }}>Autre</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="libelle">Libellé <span class="text-danger">*</span></label>
                                    <input type="text" name="libelle" id="libelle" 
                                           class="form-control @error('libelle') is-invalid @enderror" 
                                           value="{{ old('libelle') }}" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="montant">Montant (GNF) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" name="montant" id="montant" step="0.01" min="0"
                                               class="form-control @error('montant') is-invalid @enderror" 
                                               value="{{ old('montant') }}" required>
                                        <button type="button" class="btn btn-outline-info" id="btnTotalAnnuel" title="Récupérer le total annuel de la classe">
                                            <i class="fas fa-calculator"></i>
                                        </button>
                                    </div>
                                    <small class="form-text text-muted" id="totalAnnuelInfo" style="display: none;">
                                        <i class="fas fa-info-circle"></i>
                                        <span id="totalAnnuelFormula"></span>
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="date_echeance">Date d'Échéance <span class="text-danger">*</span></label>
                                    <input type="date" name="date_echeance" id="date_echeance" 
                                           class="form-control @error('date_echeance') is-invalid @enderror" 
                                           value="{{ old('date_echeance') }}" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea name="description" id="description" rows="3" 
                                              class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Section Type de Paiement -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-credit-card me-1"></i>Type de Paiement
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input type="radio" name="type_paiement" id="paiement_unique" 
                                                   value="unique" {{ old('type_paiement', 'unique') == 'unique' ? 'checked' : '' }}>
                                            <label for="paiement_unique" class="form-check-label">
                                                <i class="fas fa-money-bill-wave me-1"></i>Paiement Unique
                                                <small class="text-muted d-block">Payer le montant total en une seule fois</small>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input type="radio" name="type_paiement" id="paiement_tranches" 
                                                   value="tranches" {{ old('type_paiement') == 'tranches' ? 'checked' : '' }}>
                                            <label for="paiement_tranches" class="form-check-label">
                                                <i class="fas fa-calendar-check me-1"></i>Paiement par Tranches
                                                <small class="text-muted d-block">Diviser le paiement en plusieurs mois</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section Paiement Mensuel -->
                        <div class="card mt-4" id="tranches-card" style="display: none;">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-calendar-check me-1"></i>Configuration des Tranches
                                </h5>
                            </div>
                            <div class="card-body" id="tranches-section" style="display: none;">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="nombre_tranches">
                                                <i class="fas fa-calendar-alt me-1"></i>Nombre des Mois
                                            </label>
                                            <select name="nombre_tranches" id="nombre_tranches" class="form-control">
                                                <option value="">Sélectionner</option>
                                                @for($i = 1; $i <= 9; $i++)
                                                <option value="{{ $i }}" {{ old('nombre_tranches', 3) == $i ? 'selected' : '' }}>
                                                    {{ $i }} mois
                                                </option>
                                                @endfor
                                            </select>
                                            <small class="text-muted">Nombre de mois d'école dans l'année</small>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="periode_tranche">
                                                <i class="fas fa-clock me-1"></i>Fréquence
                                            </label>
                                            <select name="periode_tranche" id="periode_tranche" class="form-control">
                                                <option value="">Sélectionner</option>
                                                <option value="mensuel" {{ old('periode_tranche', 'mensuel') == 'mensuel' ? 'selected' : '' }}>Mensuel</option>
                                                <option value="trimestriel" {{ old('periode_tranche') == 'trimestriel' ? 'selected' : '' }}>Trimestriel</option>
                                                <option value="semestriel" {{ old('periode_tranche') == 'semestriel' ? 'selected' : '' }}>Semestriel</option>
                                                <option value="annuel" {{ old('periode_tranche') == 'annuel' ? 'selected' : '' }}>Annuel</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="date_debut_tranches">
                                                <i class="fas fa-calendar me-1"></i>Date de Début des Paiements
                                            </label>
                                            <input type="date" name="date_debut_tranches" id="date_debut_tranches" 
                                                   class="form-control" value="{{ old('date_debut_tranches', now()->format('Y-m-d')) }}">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    <strong>Note:</strong> Le montant sera automatiquement divisé en paiements mensuels égaux selon le nombre de mois choisi.
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i>
                                Enregistrer
                            </button>
                            <a href="{{ route('paiements.index') }}" class="btn btn-secondary ml-2">
                                <i class="fas fa-arrow-left mr-1"></i>
                                Retour
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Ancienne logique (checkbox) protégée si l'élément existe
    const legacyCheckbox = document.getElementById('paiement_par_tranches');
    const legacySection = document.getElementById('tranches-section');
    if (legacyCheckbox && legacySection) {
        legacyCheckbox.addEventListener('change', function() {
            legacySection.style.display = this.checked ? 'block' : 'none';
        });
        if (legacyCheckbox.checked) {
            legacySection.style.display = 'block';
        }
    }

    // Gestion du bouton pour récupérer le total annuel de la classe
    const btnTotalAnnuel = document.getElementById('btnTotalAnnuel');
    const eleveSelect = document.getElementById('eleve_id');
    const typeFrais = document.getElementById('type_frais');
    const montantInput = document.getElementById('montant');
    const totalAnnuelInfo = document.getElementById('totalAnnuelInfo');
    const totalAnnuelValue = document.getElementById('totalAnnuelValue');

    // Données des tarifs des classes (passées depuis le contrôleur)
    const tarifsClasses = @json($tarifsClasses ?? []);
    console.log('Tarifs reçus:', tarifsClasses);

    function computeMontantFromClasse() {
        console.log('=== DÉBUT CALCUL ===');
        
        if (!eleveSelect || !eleveSelect.value) {
            alert('Veuillez d\'abord sélectionner un élève.');
            return;
        }

        const selectedOption = eleveSelect.options[eleveSelect.selectedIndex];
        const classeId = selectedOption.getAttribute('data-classe-id');
        
        console.log('Élève ID:', eleveSelect.value);
        console.log('Classe ID:', classeId);
        console.log('Tarifs disponibles:', Object.keys(tarifsClasses));
        
        if (!classeId) {
            alert('Erreur: Classe ID manquant dans l\'option sélectionnée.');
            return;
        }
        
        const tarif = tarifsClasses[classeId];
        console.log('Tarif trouvé:', tarif);
        
        if (!tarif || !tarif.total_annuel) {
            alert('Aucun tarif trouvé pour cette classe. Vérifiez les tarifs dans "Tarifs Classe".');
            return;
        }
        
        const totalAnnuel = parseFloat(tarif.total_annuel);
        const fraisInscription = parseFloat(tarif.frais_inscription || 0);
        const typeFraisValue = typeFrais ? typeFrais.value : '';
        
        let montant = totalAnnuel;
        let formule = '';
        
        if (typeFraisValue === 'scolarite') {
            montant = Math.max(0, totalAnnuel - fraisInscription);
            formule = `Montant = ${totalAnnuel.toLocaleString('fr-FR')} GNF (Total annuel) − ${fraisInscription.toLocaleString('fr-FR')} GNF (Frais d'inscription) = ${montant.toLocaleString('fr-FR')} GNF`;
        } else if (typeFraisValue === 'inscription') {
            montant = fraisInscription;
            formule = `Montant = ${fraisInscription.toLocaleString('fr-FR')} GNF (Frais d'inscription)`;
        } else {
            formule = `Montant = ${totalAnnuel.toLocaleString('fr-FR')} GNF`;
        }
        
        console.log('Montant calculé:', montant);
        console.log('Formule:', formule);
        
        montantInput.value = montant;
        totalAnnuelInfo.style.display = 'block';
        
        const formulaEl = document.getElementById('totalAnnuelFormula');
        if (formulaEl) {
            formulaEl.innerHTML = formule;
        }
        
        // Effet visuel
        montantInput.classList.add('border-success');
        setTimeout(() => {
            montantInput.classList.remove('border-success');
        }, 2000);
        
        console.log('=== FIN CALCUL ===');
    }

    btnTotalAnnuel.addEventListener('click', computeMontantFromClasse);
    // Recalcul automatique si on change l'élève ou le type de frais
    eleveSelect.addEventListener('change', () => {
        if (montantInput.value === '' || montantInput.value === '0') {
            computeMontantFromClasse();
        }
    });
    typeFrais.addEventListener('change', () => {
        if (totalAnnuelInfo.style.display === 'block') {
            computeMontantFromClasse();
        }
    });

    // Masquer l'info du total annuel quand l'utilisateur modifie le montant manuellement
    montantInput.addEventListener('input', function() {
        if (totalAnnuelInfo.style.display === 'block') {
            totalAnnuelInfo.style.display = 'none';
        }
    });

    // Gestion des types de paiement
    const paiementUnique = document.getElementById('paiement_unique');
    const paiementTranches = document.getElementById('paiement_tranches');
    const tranchesCard = document.getElementById('tranches-card');
    const tranchesSection = document.getElementById('tranches-section');
    const paiementParTranchesInput = document.getElementById('paiement_par_tranches');

    function toggleTranchesSection() {
        if (paiementTranches.checked) {
            tranchesCard.style.display = 'block';
            tranchesSection.style.display = 'block';
            paiementParTranchesInput.checked = true;
            // Rendre les champs obligatoires
            document.getElementById('nombre_tranches').required = true;
            document.getElementById('periode_tranche').required = true;
            document.getElementById('date_debut_tranches').required = true;
        } else {
            tranchesCard.style.display = 'none';
            tranchesSection.style.display = 'none';
            paiementParTranchesInput.checked = false;
            // Rendre les champs non obligatoires
            document.getElementById('nombre_tranches').required = false;
            document.getElementById('periode_tranche').required = false;
            document.getElementById('date_debut_tranches').required = false;
        }
    }

    paiementUnique.addEventListener('change', toggleTranchesSection);
    paiementTranches.addEventListener('change', toggleTranchesSection);

    // Initialiser l'affichage
    toggleTranchesSection();
});
</script>
@endsection
