@extends('layouts.app')

@section('title', 'Modifier un Salaire')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-edit mr-2"></i>
                        Modifier un Salaire
                    </h3>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <strong>Veuillez remplir tous les champs obligatoires</strong>
                        </div>
                    @endif

                    <form action="{{ route('salaires.update', $salaire) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="enseignant_id">Enseignant <span class="text-danger">*</span></label>
                                    <select name="enseignant_id" id="enseignant_id" 
                                            class="form-control @error('enseignant_id') is-invalid @enderror" required>
                                        <option value="">Sélectionner un enseignant</option>
                                        @foreach($enseignants as $enseignant)
                                            <option value="{{ $enseignant->id }}" 
                                                    {{ old('enseignant_id', $salaire->enseignant_id) == $enseignant->id ? 'selected' : '' }}>
                                                {{ $enseignant->utilisateur->nom }} {{ $enseignant->utilisateur->prenom }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="periode_debut">Période Début <span class="text-danger">*</span></label>
                                    <input type="date" name="periode_debut" id="periode_debut" 
                                           class="form-control @error('periode_debut') is-invalid @enderror" 
                                           value="{{ old('periode_debut', $salaire->periode_debut->format('Y-m-d')) }}" required>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="periode_fin">Période Fin <span class="text-danger">*</span></label>
                                    <input type="date" name="periode_fin" id="periode_fin" 
                                           class="form-control @error('periode_fin') is-invalid @enderror" 
                                           value="{{ old('periode_fin', $salaire->periode_fin->format('Y-m-d')) }}" required>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">
                        <h5><i class="fas fa-clock mr-2"></i>Informations de Calcul</h5>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="nombre_heures">Nombre d'Heures</label>
                                    <input type="number" name="nombre_heures" id="nombre_heures" 
                                           class="form-control @error('nombre_heures') is-invalid @enderror" 
                                           value="{{ old('nombre_heures', $salaire->nombre_heures) }}" min="0">
                                    <small class="form-text text-muted">Nombre d'heures enseignées dans la période</small>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="taux_horaire">Taux Horaire (GNF)</label>
                                    <input type="number" name="taux_horaire" id="taux_horaire" step="0.01" min="0"
                                           class="form-control @error('taux_horaire') is-invalid @enderror" 
                                           value="{{ old('taux_horaire', $salaire->taux_horaire) }}">
                                    <small class="form-text text-muted">Montant par heure en GNF</small>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="salaire_base">Salaire de Base (GNF)</label>
                                    <input type="number" name="salaire_base" id="salaire_base" step="0.01" min="0"
                                           class="form-control @error('salaire_base') is-invalid @enderror" 
                                           value="{{ old('salaire_base', $salaire->salaire_base) }}">
                                    <small class="form-text text-muted">Salaire fixe de base (optionnel)</small>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">
                        <h5><i class="fas fa-gift mr-2"></i>Primes</h5>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="prime_anciennete">Prime d'Ancienneté (GNF)</label>
                                    <input type="number" name="prime_anciennete" id="prime_anciennete" step="0.01" min="0"
                                           class="form-control @error('prime_anciennete') is-invalid @enderror" 
                                           value="{{ old('prime_anciennete', $salaire->prime_anciennete) }}">
                                    <small class="form-text text-muted">Prime basée sur l'ancienneté</small>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="prime_performance">Prime de Performance (GNF)</label>
                                    <input type="number" name="prime_performance" id="prime_performance" step="0.01" min="0"
                                           class="form-control @error('prime_performance') is-invalid @enderror" 
                                           value="{{ old('prime_performance', $salaire->prime_performance) }}">
                                    <small class="form-text text-muted">Prime basée sur les performances</small>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="prime_heures_supplementaires">Heures Supplémentaires (GNF)</label>
                                    <input type="number" name="prime_heures_supplementaires" id="prime_heures_supplementaires" step="0.01" min="0"
                                           class="form-control @error('prime_heures_supplementaires') is-invalid @enderror" 
                                           value="{{ old('prime_heures_supplementaires', $salaire->prime_heures_supplementaires) }}">
                                    <small class="form-text text-muted">Montant pour heures supplémentaires</small>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">
                        <h5><i class="fas fa-minus-circle mr-2"></i>Déductions</h5>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="deduction_absences">Déduction Absences (GNF)</label>
                                    <input type="number" name="deduction_absences" id="deduction_absences" step="0.01" min="0"
                                           class="form-control @error('deduction_absences') is-invalid @enderror" 
                                           value="{{ old('deduction_absences', $salaire->deduction_absences) }}">
                                    <small class="form-text text-muted">Montant déduit pour absences</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="deduction_autres">Autres Déductions (GNF)</label>
                                    <input type="number" name="deduction_autres" id="deduction_autres" step="0.01" min="0"
                                           class="form-control @error('deduction_autres') is-invalid @enderror" 
                                           value="{{ old('deduction_autres', $salaire->deduction_autres) }}">
                                    <small class="form-text text-muted">Autres déductions (retard, etc.)</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="observations">Observations</label>
                            <textarea name="observations" id="observations" rows="3" 
                                      class="form-control @error('observations') is-invalid @enderror" 
                                      placeholder="Observations sur le calcul du salaire">{{ old('observations', $salaire->observations) }}</textarea>
                        </div>

                        <!-- Aperçu du calcul -->
                        <div class="card bg-light">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-calculator mr-2"></i>Aperçu du Calcul</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Salaire Horaire:</strong> <span id="salaire-horaire">0 GNF</span></p>
                                        <p><strong>Salaire de Base:</strong> <span id="salaire-base-preview">0 GNF</span></p>
                                        <p><strong>Total Primes:</strong> <span id="total-primes">0 GNF</span></p>
                                        <p><strong>Salaire Brut:</strong> <span id="salaire-brut" class="text-primary">0 GNF</span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Total Déductions:</strong> <span id="total-deductions">0 GNF</span></p>
                                        <p><strong>Salaire Net:</strong> <span id="salaire-net" class="text-success font-weight-bold">0 GNF</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Affichage du statut actuel -->
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle mr-2"></i>Statut Actuel</h6>
                            <p class="mb-0">
                                <strong>Statut:</strong> 
                                @switch($salaire->statut)
                                    @case('calculé')
                                        <span class="badge badge-warning">Calculé</span>
                                        @break
                                    @case('validé')
                                        <span class="badge badge-info">Validé</span>
                                        @break
                                    @case('payé')
                                        <span class="badge badge-success">Payé</span>
                                        @break
                                    @case('annulé')
                                        <span class="badge badge-danger">Annulé</span>
                                        @break
                                    @default
                                        <span class="badge badge-secondary">{{ $salaire->statut }}</span>
                                @endswitch
                            </p>
                            @if($salaire->statut === 'payé')
                                <small class="text-muted">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    Attention: Ce salaire a déjà été payé. Les modifications peuvent affecter la comptabilité.
                                </small>
                            @endif
                        </div>

                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i>
                                Mettre à Jour et Recalculer
                            </button>
                            <a href="{{ route('salaires.show', $salaire) }}" class="btn btn-secondary ml-2">
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
    const nombreHeures = document.getElementById('nombre_heures');
    const tauxHoraire = document.getElementById('taux_horaire');
    const salaireBase = document.getElementById('salaire_base');
    const primeAnciennete = document.getElementById('prime_anciennete');
    const primePerformance = document.getElementById('prime_performance');
    const primeHeuresSupp = document.getElementById('prime_heures_supplementaires');
    const deductionAbsences = document.getElementById('deduction_absences');
    const deductionAutres = document.getElementById('deduction_autres');

    function calculerApercu() {
        const heures = parseFloat(nombreHeures.value) || 0;
        const taux = parseFloat(tauxHoraire.value) || 0;
        const base = parseFloat(salaireBase.value) || 0;
        const primeAnc = parseFloat(primeAnciennete.value) || 0;
        const primePerf = parseFloat(primePerformance.value) || 0;
        const primeHS = parseFloat(primeHeuresSupp.value) || 0;
        const dedAbs = parseFloat(deductionAbsences.value) || 0;
        const dedAutres = parseFloat(deductionAutres.value) || 0;

        // Calculate hourly salary only if both hours and rate are provided
        const salaireHoraire = (heures > 0 && taux > 0) ? heures * taux : 0;
        const totalPrimes = primeAnc + primePerf + primeHS;
        const salaireBrut = base + salaireHoraire + totalPrimes;
        const totalDeductions = dedAbs + dedAutres;
        const salaireNet = salaireBrut - totalDeductions;

        document.getElementById('salaire-horaire').textContent = new Intl.NumberFormat('fr-FR').format(salaireHoraire) + ' GNF';
        document.getElementById('salaire-base-preview').textContent = new Intl.NumberFormat('fr-FR').format(base) + ' GNF';
        document.getElementById('total-primes').textContent = new Intl.NumberFormat('fr-FR').format(totalPrimes) + ' GNF';
        document.getElementById('salaire-brut').textContent = new Intl.NumberFormat('fr-FR').format(salaireBrut) + ' GNF';
        document.getElementById('total-deductions').textContent = new Intl.NumberFormat('fr-FR').format(totalDeductions) + ' GNF';
        document.getElementById('salaire-net').textContent = new Intl.NumberFormat('fr-FR').format(salaireNet) + ' GNF';
    }

    // Écouter les changements sur tous les champs
    [nombreHeures, tauxHoraire, salaireBase, primeAnciennete, primePerformance, 
     primeHeuresSupp, deductionAbsences, deductionAutres].forEach(input => {
        input.addEventListener('input', calculerApercu);
    });

    // Calcul initial
    calculerApercu();
});
</script>
@endsection
