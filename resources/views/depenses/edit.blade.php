@extends('layouts.app')

@section('title', 'Modifier une Sortie')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-edit mr-2"></i>
                        Modifier une Sortie
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

                    <form action="{{ route('depenses.update', $depense) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="libelle">Libellé <span class="text-danger">*</span></label>
                                    <input type="text" name="libelle" id="libelle" 
                                           class="form-control @error('libelle') is-invalid @enderror" 
                                           value="{{ old('libelle', $depense->libelle) }}" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="montant">Montant (GNF) <span class="text-danger">*</span></label>
                                    <input type="number" name="montant" id="montant" step="0.01" min="0"
                                           class="form-control @error('montant') is-invalid @enderror" 
                                           value="{{ old('montant', $depense->montant) }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="date_depense">Date de la Sortie <span class="text-danger">*</span></label>
                                    <input type="date" name="date_depense" id="date_depense" 
                                           class="form-control @error('date_depense') is-invalid @enderror" 
                                           value="{{ old('date_depense', $depense->date_depense->format('Y-m-d')) }}" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="type_depense">Type de Sortie <span class="text-danger">*</span></label>
                                    <select name="type_depense" id="type_depense" 
                                            class="form-control @error('type_depense') is-invalid @enderror" required>
                                        <option value="">Sélectionner le type</option>
                                        <option value="salaire_enseignant" {{ old('type_depense', $depense->type_depense) == 'salaire_enseignant' ? 'selected' : '' }}>Salaire Enseignant</option>
                                        <option value="salaire_personnel" {{ old('type_depense', $depense->type_depense) == 'salaire_personnel' ? 'selected' : '' }}>Salaire Personnel</option>
                                        <option value="achat_materiel" {{ old('type_depense', $depense->type_depense) == 'achat_materiel' ? 'selected' : '' }}>Achat Matériel</option>
                                        <option value="maintenance" {{ old('type_depense', $depense->type_depense) == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                        <option value="electricite" {{ old('type_depense', $depense->type_depense) == 'electricite' ? 'selected' : '' }}>Électricité</option>
                                        <option value="eau" {{ old('type_depense', $depense->type_depense) == 'eau' ? 'selected' : '' }}>Eau</option>
                                        <option value="nourriture" {{ old('type_depense', $depense->type_depense) == 'nourriture' ? 'selected' : '' }}>Nourriture</option>
                                        <option value="transport" {{ old('type_depense', $depense->type_depense) == 'transport' ? 'selected' : '' }}>Transport</option>
                                        <option value="communication" {{ old('type_depense', $depense->type_depense) == 'communication' ? 'selected' : '' }}>Communication</option>
                                        <option value="formation" {{ old('type_depense', $depense->type_depense) == 'formation' ? 'selected' : '' }}>Formation</option>
                                        <option value="autre" {{ old('type_depense', $depense->type_depense) == 'autre' ? 'selected' : '' }}>Autre</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="beneficiaire">Bénéficiaire</label>
                                    <input type="text" name="beneficiaire" id="beneficiaire" 
                                           class="form-control @error('beneficiaire') is-invalid @enderror" 
                                           value="{{ old('beneficiaire', $depense->beneficiaire) }}"
                                           placeholder="Nom du bénéficiaire">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="reference_facture">Référence Facture</label>
                                    <input type="text" name="reference_facture" id="reference_facture" 
                                           class="form-control @error('reference_facture') is-invalid @enderror" 
                                           value="{{ old('reference_facture', $depense->reference_facture) }}"
                                           placeholder="N° de facture ou référence">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" rows="3" 
                                      class="form-control @error('description') is-invalid @enderror" 
                                      placeholder="Description détaillée de la sortie">{{ old('description', $depense->description) }}</textarea>
                        </div>

                        <div class="form-group">
                            <label for="observations">Observations</label>
                            <textarea name="observations" id="observations" rows="2" 
                                      class="form-control @error('observations') is-invalid @enderror" 
                                      placeholder="Observations ou notes">{{ old('observations', $depense->observations) }}</textarea>
                        </div>

                        <!-- Affichage du statut actuel -->
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle mr-2"></i>Statut Actuel</h6>
                            <p class="mb-0">
                                <strong>Statut:</strong> 
                                @switch($depense->statut)
                                    @case('en_attente')
                                        <span class="badge badge-warning">En Attente</span>
                                        @break
                                    @case('approuve')
                                        <span class="badge badge-info">Approuvé</span>
                                        @break
                                    @case('paye')
                                        <span class="badge badge-success">Payé</span>
                                        @break
                                    @case('annule')
                                        <span class="badge badge-danger">Annulé</span>
                                        @break
                                    @default
                                        <span class="badge badge-secondary">{{ $depense->statut }}</span>
                                @endswitch
                            </p>
                            @if($depense->statut === 'paye')
                                <small class="text-muted">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    Attention: Cette sortie a déjà été payée. Les modifications peuvent affecter la comptabilité.
                                </small>
                            @endif
                        </div>

                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i>
                                Mettre à Jour
                            </button>
                            <a href="{{ route('depenses.show', $depense) }}" class="btn btn-secondary ml-2">
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
@endsection
