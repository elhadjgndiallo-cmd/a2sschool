@extends('layouts.app')

@section('title', 'Payer une Tranche')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-credit-card mr-2"></i>
                        Paiement du Mois {{ $tranche->numero_tranche }}
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

                    <!-- Informations du mois -->
                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle mr-2"></i>Informations du Mois</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Élève:</strong> {{ $tranche->fraisScolarite->eleve->utilisateur->nom ?? 'N/A' }} {{ $tranche->fraisScolarite->eleve->utilisateur->prenom ?? 'N/A' }}<br>
                                <strong>Frais:</strong> {{ $tranche->fraisScolarite->libelle }}<br>
                                <strong>Échéance:</strong> {{ $tranche->date_echeance->format('d/m/Y') }}
                            </div>
                            <div class="col-md-6">
                                <strong>Montant du Mois:</strong> {{ number_format($tranche->montant_tranche, 0, ',', ' ') }} GNF<br>
                                <strong>Déjà Payé:</strong> {{ number_format($tranche->montant_paye, 0, ',', ' ') }} GNF<br>
                                <strong>Reste à Payer:</strong> <span class="text-danger font-weight-bold">{{ number_format($tranche->montant_restant, 0, ',', ' ') }} GNF</span>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('paiements.enregistrer-tranche', $tranche) }}" method="POST" id="paiement-form">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="montant_paye">Montant à Payer (GNF) <span class="text-danger">*</span></label>
                                    <input type="number" name="montant_paye" id="montant_paye" 
                                           step="0.01" min="0" max="{{ $tranche->montant_restant }}"
                                           class="form-control @error('montant_paye') is-invalid @enderror" 
                                           value="{{ old('montant_paye', $tranche->montant_restant) }}" required>
                                    <small class="form-text text-muted">
                                        Maximum: {{ number_format($tranche->montant_restant, 0, ',', ' ') }} GNF
                                    </small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="date_paiement">Date de Paiement <span class="text-danger">*</span></label>
                                    <input type="date" name="date_paiement" id="date_paiement" 
                                           class="form-control @error('date_paiement') is-invalid @enderror" 
                                           value="{{ old('date_paiement', date('Y-m-d')) }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="mode_paiement">Mode de Paiement <span class="text-danger">*</span></label>
                                    <select name="mode_paiement" id="mode_paiement" 
                                            class="form-control @error('mode_paiement') is-invalid @enderror" required>
                                        <option value="">Sélectionner le mode</option>
                                        <option value="especes" {{ old('mode_paiement') == 'especes' ? 'selected' : '' }}>Espèces</option>
                                        <option value="cheque" {{ old('mode_paiement') == 'cheque' ? 'selected' : '' }}>Chèque</option>
                                        <option value="virement" {{ old('mode_paiement') == 'virement' ? 'selected' : '' }}>Virement</option>
                                        <option value="carte" {{ old('mode_paiement') == 'carte' ? 'selected' : '' }}>Carte Bancaire</option>
                                        <option value="mobile_money" {{ old('mode_paiement') == 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="reference_paiement">Référence Paiement</label>
                                    <input type="text" name="reference_paiement" id="reference_paiement" 
                                           class="form-control @error('reference_paiement') is-invalid @enderror" 
                                           value="{{ old('reference_paiement') }}"
                                           placeholder="N° chèque, référence virement, etc.">
                                    <small class="form-text text-muted">
                                        Obligatoire pour chèque, virement et carte
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="observations">Observations</label>
                            <textarea name="observations" id="observations" rows="3" 
                                      class="form-control @error('observations') is-invalid @enderror" 
                                      placeholder="Commentaires ou notes sur ce paiement">{{ old('observations') }}</textarea>
                        </div>

                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-success btn-lg" id="submit-btn">
                                <i class="fas fa-credit-card mr-2"></i>
                                Enregistrer le Paiement
                            </button>
                            <a href="{{ route('paiements.show', $tranche->fraisScolarite) }}" class="btn btn-secondary btn-lg ml-2">
                                <i class="fas fa-arrow-left mr-2"></i>
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
                const referencePaiement = document.getElementById('reference_paiement');
                const form = document.getElementById('paiement-form');
                const submitBtn = document.getElementById('submit-btn');
                
                // Gestion du mode de paiement
                modePaiement.addEventListener('change', function() {
                    const modesAvecReference = ['cheque', 'virement', 'carte'];
                    if (modesAvecReference.includes(this.value)) {
                        referencePaiement.required = true;
                        referencePaiement.parentElement.querySelector('small').textContent = 'Obligatoire pour ce mode de paiement';
                    } else {
                        referencePaiement.required = false;
                        referencePaiement.parentElement.querySelector('small').textContent = 'Obligatoire pour chèque, virement et carte';
                    }
                });
                
                // Gestion de la soumission du formulaire
                form.addEventListener('submit', function(e) {
                    // Désactiver le bouton pour éviter les double-clics
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Enregistrement...';
                    
                    // Réactiver le bouton après 5 secondes au cas où
                    setTimeout(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="fas fa-credit-card mr-2"></i>Enregistrer le Paiement';
                    }, 5000);
                });
            });
            </script>
@endsection
