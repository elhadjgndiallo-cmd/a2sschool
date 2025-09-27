@extends('layouts.app')

@section('title', 'Payer un Salaire')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-6 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-money-bill-wave mr-2"></i>
                        Payer un Salaire
                    </h3>
                </div>
                <div class="card-body">
                    <!-- Informations du salaire -->
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle mr-2"></i>Informations du Salaire</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Enseignant:</strong> {{ $salaire->enseignant->utilisateur->nom }} {{ $salaire->enseignant->utilisateur->prenom }}</p>
                                <p class="mb-1"><strong>Période:</strong> {{ $salaire->periode_debut->format('d/m/Y') }} - {{ $salaire->periode_fin->format('d/m/Y') }}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Salaire Brut:</strong> {{ number_format($salaire->salaire_brut, 0, ',', ' ') }} GNF</p>
                                <p class="mb-0"><strong>Salaire Net:</strong> <span class="text-success font-weight-bold">{{ number_format($salaire->salaire_net, 0, ',', ' ') }} GNF</span></p>
                            </div>
                        </div>
                    </div>

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <strong>Veuillez remplir tous les champs obligatoires</strong>
                        </div>
                    @endif

                    <form action="{{ route('salaires.payer', $salaire) }}" method="POST">
                        @csrf
                        
                        <div class="form-group">
                            <label for="date_paiement">Date de Paiement <span class="text-danger">*</span></label>
                            <input type="date" name="date_paiement" id="date_paiement" 
                                   class="form-control @error('date_paiement') is-invalid @enderror" 
                                   value="{{ old('date_paiement', now()->toDateString()) }}" required>
                        </div>

                        <div class="form-group">
                            <label for="mode_paiement">Mode de Paiement <span class="text-danger">*</span></label>
                            <select name="mode_paiement" id="mode_paiement" 
                                    class="form-control @error('mode_paiement') is-invalid @enderror" required>
                                <option value="">Sélectionner le mode de paiement</option>
                                <option value="especes" {{ old('mode_paiement') == 'especes' ? 'selected' : '' }}>Espèces</option>
                                <option value="cheque" {{ old('mode_paiement') == 'cheque' ? 'selected' : '' }}>Chèque</option>
                                <option value="virement" {{ old('mode_paiement') == 'virement' ? 'selected' : '' }}>Virement</option>
                                <option value="carte" {{ old('mode_paiement') == 'carte' ? 'selected' : '' }}>Carte bancaire</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="reference_paiement">Référence de Paiement</label>
                            <input type="text" name="reference_paiement" id="reference_paiement" 
                                   class="form-control @error('reference_paiement') is-invalid @enderror" 
                                   value="{{ old('reference_paiement') }}"
                                   placeholder="N° de chèque, référence virement, etc.">
                            <small class="form-text text-muted">Optionnel - Pour tracer le paiement</small>
                        </div>

                        <!-- Récapitulatif -->
                        <div class="card bg-light">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-receipt mr-2"></i>Récapitulatif du Paiement</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Bénéficiaire:</strong> {{ $salaire->enseignant->utilisateur->nom }} {{ $salaire->enseignant->utilisateur->prenom }}</p>
                                        <p><strong>Période:</strong> {{ $salaire->periode_debut->format('d/m/Y') }} - {{ $salaire->periode_fin->format('d/m/Y') }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Montant à payer:</strong> <span class="text-success font-weight-bold">{{ number_format($salaire->salaire_net, 0, ',', ' ') }} GNF</span></p>
                                        <p><strong>Mode de paiement:</strong> <span id="mode-paiement-preview">-</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Avertissement -->
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <strong>Attention:</strong> Cette action va :
                            <ul class="mb-0 mt-2">
                                <li>Marquer le salaire comme payé</li>
                                <li>Créer automatiquement une dépense correspondante</li>
                                <li>Enregistrer le paiement dans l'historique</li>
                            </ul>
                        </div>

                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-money-bill-wave mr-1"></i>
                                Confirmer le Paiement
                            </button>
                            <a href="{{ route('salaires.show', $salaire) }}" class="btn btn-secondary ml-2">
                                <i class="fas fa-arrow-left mr-1"></i>
                                Annuler
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
    const modePaiement = document.getElementById('mode_paiement');
    const modePaiementPreview = document.getElementById('mode-paiement-preview');
    
    const modes = {
        'especes': 'Espèces',
        'cheque': 'Chèque',
        'virement': 'Virement',
        'carte': 'Carte bancaire'
    };
    
    function updateModePreview() {
        const selectedMode = modePaiement.value;
        modePaiementPreview.textContent = selectedMode ? modes[selectedMode] : '-';
    }
    
    modePaiement.addEventListener('change', updateModePreview);
    updateModePreview();
});
</script>
@endsection
