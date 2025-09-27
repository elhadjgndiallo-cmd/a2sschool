@extends('layouts.app')

@section('title', 'Paiement Direct')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-money-bill-wave mr-2"></i>
                        Paiement Direct
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

                    <!-- Informations du frais -->
                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle mr-2"></i>Informations du Frais</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Élève:</strong> {{ $frais->eleve->utilisateur->nom ?? 'N/A' }} {{ $frais->eleve->utilisateur->prenom ?? 'N/A' }}<br>
                                <strong>Libellé:</strong> {{ $frais->libelle }}<br>
                                <strong>Type:</strong> {{ ucfirst($frais->type_frais) }}<br>
                                <strong>Échéance:</strong> {{ $frais->date_echeance->format('d/m/Y') }}
                            </div>
                            <div class="col-md-6">
                                <strong>Montant Total:</strong> {{ number_format($frais->montant, 0, ',', ' ') }} GNF<br>
                                <strong>Déjà Payé:</strong> {{ number_format($frais->montant - $frais->montant_restant, 0, ',', ' ') }} GNF<br>
                                <strong>Reste à Payer:</strong> <span class="text-danger font-weight-bold">{{ number_format($frais->montant_restant, 0, ',', ' ') }} GNF</span>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('paiements.enregistrer-direct', $frais) }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="montant_paye">Montant à Payer (GNF) <span class="text-danger">*</span></label>
                                    <input type="number" name="montant_paye" id="montant_paye" 
                                           step="0.01" min="0" max="{{ $frais->montant_restant }}"
                                           class="form-control @error('montant_paye') is-invalid @enderror" 
                                           value="{{ old('montant_paye', $frais->montant_restant) }}" required>
                                    <small class="form-text text-muted">
                                        Maximum: {{ number_format($frais->montant_restant, 0, ',', ' ') }} GNF
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
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-money-bill-wave mr-2"></i>
                                Enregistrer le Paiement
                            </button>
                            <a href="{{ route('paiements.show', $frais) }}" class="btn btn-secondary btn-lg ml-2">
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
});
</script>
@endsection
