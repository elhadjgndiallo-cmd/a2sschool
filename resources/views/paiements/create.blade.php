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
                                            <option value="{{ $eleve->id }}" {{ (old('eleve_id', $selectedEleveId) == $eleve->id) ? 'selected' : '' }}>
                                                {{ $eleve->utilisateur->nom ?? 'N/A' }} {{ $eleve->utilisateur->prenom ?? 'N/A' }} - {{ $eleve->numero_etudiant ?? 'N/A' }} - {{ $eleve->classe->nom ?? 'N/A' }}
                                            </option>
                                        @endforeach
                                    </select>
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
                                        <i class="fas fa-info-circle"></i> Total annuel de la classe : <span id="totalAnnuelValue"></span> GNF
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

                        <!-- Section Paiement Mensuel -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <input type="checkbox" name="paiement_par_tranches" id="paiement_par_tranches" 
                                           value="1" {{ old('paiement_par_tranches') ? 'checked' : '' }}>
                                    <label for="paiement_par_tranches" class="ml-2">
                                        <i class="fas fa-calendar-check me-1"></i>Paiement Mensuel
                                    </label>
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
                                                <option value="{{ $i }}" {{ old('nombre_tranches', 9) == $i ? 'selected' : '' }}>
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
                                                   class="form-control" value="{{ old('date_debut_tranches') }}">
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
    const checkbox = document.getElementById('paiement_par_tranches');
    const section = document.getElementById('tranches-section');
    
    checkbox.addEventListener('change', function() {
        if (this.checked) {
            section.style.display = 'block';
        } else {
            section.style.display = 'none';
        }
    });
    
    // Afficher la section si déjà cochée
    if (checkbox.checked) {
        section.style.display = 'block';
    }

    // Gestion du bouton pour récupérer le total annuel de la classe
    const btnTotalAnnuel = document.getElementById('btnTotalAnnuel');
    const eleveSelect = document.getElementById('eleve_id');
    const montantInput = document.getElementById('montant');
    const totalAnnuelInfo = document.getElementById('totalAnnuelInfo');
    const totalAnnuelValue = document.getElementById('totalAnnuelValue');

    // Données des tarifs des classes (passées depuis le contrôleur)
    const tarifsClasses = @json($tarifsClasses);

    btnTotalAnnuel.addEventListener('click', function() {
        const selectedEleveId = eleveSelect.value;
        
        if (!selectedEleveId) {
            alert('Veuillez d\'abord sélectionner un élève.');
            return;
        }

        // Trouver l'élève sélectionné pour récupérer sa classe
        const selectedOption = eleveSelect.options[eleveSelect.selectedIndex];
        const optionText = selectedOption.text;
        
        // Extraire l'ID de la classe depuis le texte de l'option
        // Format: "Nom Prénom - Matricule - Classe"
        const parts = optionText.split(' - ');
        
        if (parts.length >= 3) {
            const classeNom = parts[2].trim();
            
            // Trouver le tarif correspondant à cette classe
            let tarifClasse = null;
            for (const [classeId, tarif] of Object.entries(tarifsClasses)) {
                if (tarif.classe && tarif.classe.nom === classeNom) {
                    tarifClasse = tarif;
                    break;
                }
            }
            
            if (tarifClasse && tarifClasse.total_annuel) {
                montantInput.value = tarifClasse.total_annuel;
                totalAnnuelValue.textContent = new Intl.NumberFormat('fr-FR').format(tarifClasse.total_annuel);
                totalAnnuelInfo.style.display = 'block';
                
                // Ajouter une classe pour indiquer que le montant a été auto-rempli
                montantInput.classList.add('border-success');
                setTimeout(() => {
                    montantInput.classList.remove('border-success');
                }, 2000);
            } else {
                alert('Aucun tarif trouvé pour la classe "' + classeNom + '".');
            }
        }
    });

    // Masquer l'info du total annuel quand l'utilisateur modifie le montant manuellement
    montantInput.addEventListener('input', function() {
        if (totalAnnuelInfo.style.display === 'block') {
            totalAnnuelInfo.style.display = 'none';
        }
    });
});
</script>
@endsection
