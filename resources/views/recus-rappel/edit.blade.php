@extends('layouts.app')

@section('title', 'Modifier Reçu de Rappel')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-edit mr-2"></i>
                        Modifier Reçu de Rappel - {{ $recuRappel->numero_recu_rappel }}
                    </h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('recus-rappel.update', $recuRappel) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <!-- Informations de l'élève (lecture seule) -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h5>Informations de l'élève</h5>
                                        <p><strong>Nom:</strong> {{ $recuRappel->eleve->nom_complet }}</p>
                                        <p><strong>Numéro d'étudiant:</strong> {{ $recuRappel->eleve->numero_etudiant }}</p>
                                        <p><strong>Classe:</strong> {{ $recuRappel->eleve->classe->nom ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Détails financiers (lecture seule) -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h5>Détails financiers</h5>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <p><strong>Montant Total Dû:</strong> {{ number_format($recuRappel->montant_total_du, 0, ',', ' ') }} GNF</p>
                                            </div>
                                            <div class="col-md-3">
                                                <p><strong>Montant Payé:</strong> {{ number_format($recuRappel->montant_paye, 0, ',', ' ') }} GNF</p>
                                            </div>
                                            <div class="col-md-3">
                                                <p><strong>Montant Restant:</strong> {{ number_format($recuRappel->montant_restant, 0, ',', ' ') }} GNF</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Montant à payer (modifiable) -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="montant_a_payer" class="form-label">Montant à payer</label>
                                <input type="number" name="montant_a_payer" id="montant_a_payer" class="form-control" 
                                       step="0.01" min="0" max="{{ $recuRappel->montant_restant }}" 
                                       value="{{ $recuRappel->montant_a_payer }}">
                                <small class="form-text text-muted">
                                    Montant que le comptable doit écrire manuellement (max: {{ number_format($recuRappel->montant_restant, 0, ',', ' ') }} GNF)
                                </small>
                                @error('montant_a_payer')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Date d'échéance -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="date_echeance" class="form-label">Date d'échéance</label>
                                <input type="date" name="date_echeance" id="date_echeance" class="form-control" 
                                       value="{{ $recuRappel->date_echeance }}" required>
                                @error('date_echeance')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Observations -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="observations" class="form-label">Observations</label>
                                <textarea name="observations" id="observations" class="form-control" rows="3" 
                                          placeholder="Observations supplémentaires...">{{ $recuRappel->observations }}</textarea>
                                @error('observations')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Boutons -->
                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-1"></i>
                                    Mettre à jour
                                </button>
                                <a href="{{ route('recus-rappel.pdf', $recuRappel) }}" class="btn btn-success">
                                    <i class="fas fa-print mr-1"></i>
                                    Imprimer
                                </a>
                                <a href="{{ route('recus-rappel.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left mr-1"></i>
                                    Retour
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
