@extends('layouts.app')

@section('title', 'Nouvelle Entrée')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Nouvelle Entrée</h4>
                <p class="text-muted">Enregistrer une nouvelle entrée d'argent</p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Informations de l'Entrée</h5>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <h6>Erreurs de validation :</h6>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('entrees.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="libelle" class="form-label">Libellé <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('libelle') is-invalid @enderror" 
                                           id="libelle" name="libelle" value="{{ old('libelle') }}" required>
                                    @error('libelle')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="source" class="form-label">Source <span class="text-danger">*</span></label>
                                    <select class="form-select @error('source') is-invalid @enderror" 
                                            id="source" name="source" required>
                                        <option value="">Sélectionner une source</option>
                                        <option value="Dons" {{ old('source') == 'Dons' ? 'selected' : '' }}>Dons</option>
                                        <option value="Subventions" {{ old('source') == 'Subventions' ? 'selected' : '' }}>Subventions</option>
                                        <option value="Activités" {{ old('source') == 'Activités' ? 'selected' : '' }}>Activités</option>
                                        <option value="Vente de matériel" {{ old('source') == 'Vente de matériel' ? 'selected' : '' }}>Vente de matériel</option>
                                        <option value="Autres revenus" {{ old('source') == 'Autres revenus' ? 'selected' : '' }}>Autres revenus</option>
                                    </select>
                                    @error('source')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="montant" class="form-label">Montant (GNF) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('montant') is-invalid @enderror" 
                                           id="montant" name="montant" value="{{ old('montant') }}" 
                                           min="0" step="0.01" required>
                                    @error('montant')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="date_entree" class="form-label">Date d'entrée <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('date_entree') is-invalid @enderror" 
                                           id="date_entree" name="date_entree" value="{{ old('date_entree', date('Y-m-d')) }}" required>
                                    @error('date_entree')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="mode_paiement" class="form-label">Mode de paiement <span class="text-danger">*</span></label>
                                    <select class="form-select @error('mode_paiement') is-invalid @enderror" 
                                            id="mode_paiement" name="mode_paiement" required>
                                        <option value="">Sélectionner un mode</option>
                                        <option value="especes" {{ old('mode_paiement') == 'especes' ? 'selected' : '' }}>Espèces</option>
                                        <option value="cheque" {{ old('mode_paiement') == 'cheque' ? 'selected' : '' }}>Chèque</option>
                                        <option value="virement" {{ old('mode_paiement') == 'virement' ? 'selected' : '' }}>Virement</option>
                                        <option value="carte" {{ old('mode_paiement') == 'carte' ? 'selected' : '' }}>Carte bancaire</option>
                                        <option value="mobile_money" {{ old('mode_paiement') == 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                                    </select>
                                    @error('mode_paiement')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="reference" class="form-label">Référence</label>
                                    <input type="text" class="form-control @error('reference') is-invalid @enderror" 
                                           id="reference" name="reference" value="{{ old('reference') }}" 
                                           placeholder="N° chèque, référence virement, etc.">
                                    @error('reference')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3" 
                                      placeholder="Description détaillée de l'entrée...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('entrees.index') }}" class="btn btn-secondary me-2">Annuler</a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-1"></i>Enregistrer l'Entrée
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Informations</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle me-2"></i>Note importante</h6>
                        <p class="mb-0">
                            Cette entrée sera différente des frais de scolarité. 
                            Les frais de scolarité payés apparaîtront automatiquement 
                            dans la section "Paiements de Frais de Scolarité".
                        </p>
                    </div>
                    
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i>Sources d'entrée</h6>
                        <ul class="mb-0">
                            <li><strong>Dons :</strong> Contributions volontaires</li>
                            <li><strong>Subventions :</strong> Aides financières</li>
                            <li><strong>Activités :</strong> Revenus d'activités</li>
                            <li><strong>Vente :</strong> Vente de matériel</li>
                            <li><strong>Autres :</strong> Autres revenus</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection