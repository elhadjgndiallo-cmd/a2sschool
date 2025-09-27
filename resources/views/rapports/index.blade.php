@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar text-primary me-2"></i>
                        Rapports Comptables
                    </h3>
                </div>
                <div class="card-body">
                    <!-- Formulaire de filtres -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="fas fa-filter me-2"></i>Filtres des Rapports
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="{{ route('rapports.index') }}">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="date_debut" class="form-label">Date de début</label>
                                            <input type="date" 
                                                   class="form-control" 
                                                   id="date_debut" 
                                                   name="date_debut" 
                                                   value="{{ $dateDebut }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="date_fin" class="form-label">Date de fin</label>
                                            <input type="date" 
                                                   class="form-control" 
                                                   id="date_fin" 
                                                   name="date_fin" 
                                                   value="{{ $dateFin }}">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="mb-3">
                                            <label for="type_depense" class="form-label">Type de dépense</label>
                                            <select class="form-select" id="type_depense" name="type_depense">
                                                <option value="">Tous les types</option>
                                                @foreach($typesDepense as $type)
                                                    <option value="{{ $type }}" {{ $typeDepense == $type ? 'selected' : '' }}>
                                                        {{ ucfirst(str_replace('_', ' ', $type)) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="mb-3">
                                            <label for="source_entree" class="form-label">Source d'entrée</label>
                                            <select class="form-select" id="source_entree" name="source_entree">
                                                <option value="">Toutes les sources</option>
                                                @foreach($sourcesEntree as $source)
                                                    <option value="{{ $source }}" {{ $sourceEntree == $source ? 'selected' : '' }}>
                                                        {{ ucfirst(str_replace('_', ' ', $source)) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="mb-3">
                                            <label for="statut_depense" class="form-label">Statut dépense</label>
                                            <select class="form-select" id="statut_depense" name="statut_depense">
                                                <option value="">Tous les statuts</option>
                                                <option value="en_attente" {{ $statutDepense == 'en_attente' ? 'selected' : '' }}>En attente</option>
                                                <option value="approuve" {{ $statutDepense == 'approuve' ? 'selected' : '' }}>Approuvé</option>
                                                <option value="paye" {{ $statutDepense == 'paye' ? 'selected' : '' }}>Payé</option>
                                                <option value="annule" {{ $statutDepense == 'annule' ? 'selected' : '' }}>Annulé</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary me-2">
                                            <i class="fas fa-search me-2"></i>Appliquer les filtres
                                        </button>
                                        <a href="{{ route('rapports.index') }}" class="btn btn-secondary">
                                            <i class="fas fa-times me-2"></i>Réinitialiser
                                        </a>
                                        
                                        @if($typeDepense || $sourceEntree || $statutDepense)
                                            <div class="mt-3">
                                                <h6 class="text-info mb-2">
                                                    <i class="fas fa-filter me-2"></i>Filtres actifs :
                                                </h6>
                                                <div class="d-flex flex-wrap gap-2">
                                                    @if($typeDepense)
                                                        <span class="badge bg-primary">
                                                            <i class="fas fa-tag me-1"></i>Type: {{ ucfirst(str_replace('_', ' ', $typeDepense)) }}
                                                        </span>
                                                    @endif
                                                    @if($sourceEntree)
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-folder me-1"></i>Source: {{ ucfirst(str_replace('_', ' ', $sourceEntree)) }}
                                                        </span>
                                                    @endif
                                                    @if($statutDepense)
                                                        <span class="badge bg-warning">
                                                            <i class="fas fa-info-circle me-1"></i>Statut: {{ ucfirst(str_replace('_', ' ', $statutDepense)) }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Informations sur la période -->
                    <div class="alert alert-info mb-4">
                        <div class="row">
                            <div class="col-md-8">
                                <h6 class="mb-1">
                                    <i class="fas fa-calendar me-2"></i>Période analysée : 
                                    {{ \Carbon\Carbon::parse($dateDebut)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($dateFin)->format('d/m/Y') }}
                                </h6>
                                @if($typeDepense || $sourceEntree || $statutDepense)
                                    <small class="text-muted">
                                        Filtres appliqués : 
                                        @if($typeDepense) Type de dépense: {{ ucfirst(str_replace('_', ' ', $typeDepense)) }} @endif
                                        @if($sourceEntree) @if($typeDepense), @endif Source: {{ ucfirst(str_replace('_', ' ', $sourceEntree)) }} @endif
                                        @if($statutDepense) @if($typeDepense || $sourceEntree), @endif Statut: {{ ucfirst(str_replace('_', ' ', $statutDepense)) }} @endif
                                    </small>
                                @endif
                            </div>
                            <div class="col-md-4 text-end">
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>
                                    Mis à jour le {{ now()->format('d/m/Y à H:i') }}
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Statistiques générales -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-success text-white h-100">
                                <div class="card-body d-flex flex-column">
                                    <div class="text-center mb-3">
                                        <h2 class="card-title mb-0 font-weight-bold">{{ number_format($totalEntrees, 0, ',', ' ') }} GNF</h2>
                                        <p class="card-text mb-2">Total Entrées</p>
                                    </div>
                                    <div class="mt-auto">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <small class="text-light">Manuelles:</small>
                                            <small class="text-light font-weight-bold">{{ number_format($totalEntreesManuelles, 0, ',', ' ') }} GNF</small>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-light">Scolarité:</small>
                                            <small class="text-light font-weight-bold">{{ number_format($totalPaiements, 0, ',', ' ') }} GNF</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white h-100">
                                <div class="card-body d-flex flex-column">
                                    <div class="text-center mb-3">
                                        <h2 class="card-title mb-0 font-weight-bold">{{ number_format($totalSorties, 0, ',', ' ') }} GNF</h2>
                                        <p class="card-text mb-2">Total Sorties</p>
                                    </div>
                                    <div class="mt-auto">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <small class="text-light">Manuelles:</small>
                                            <small class="text-light font-weight-bold">{{ number_format($totalSortiesManuelles, 0, ',', ' ') }} GNF</small>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-light">Salaires:</small>
                                            <small class="text-light font-weight-bold">{{ number_format($totalSalairesEnseignants, 0, ',', ' ') }} GNF</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white h-100">
                                <div class="card-body d-flex flex-column justify-content-center text-center">
                                    <h2 class="card-title mb-0 font-weight-bold">{{ number_format($totalPaiements, 0, ',', ' ') }} GNF</h2>
                                    <p class="card-text">Frais de Scolarité</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card {{ $solde >= 0 ? 'bg-primary' : 'bg-warning' }} text-white h-100">
                                <div class="card-body d-flex flex-column">
                                    <div class="text-center mb-3">
                                        <h2 class="card-title mb-0 font-weight-bold">{{ number_format($solde, 0, ',', ' ') }} GNF</h2>
                                        <p class="card-text mb-2">Solde</p>
                                    </div>
                                    <div class="mt-auto text-center">
                                        <small class="text-light">Entrées - Sorties</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Formulaire de rapport détaillé -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="fas fa-search me-2"></i>Rapport Détaillé avec Filtres Avancés
                            </h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('rapports.detaille') }}" method="POST">
                                @csrf
                                
                                <!-- Filtres de base -->
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="date_debut" class="form-label">Date de début <span class="text-danger">*</span></label>
                                            <input type="date" 
                                                   class="form-control" 
                                                   id="date_debut" 
                                                   name="date_debut" 
                                                   value="{{ date('Y-m-01') }}" 
                                                   required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="date_fin" class="form-label">Date de fin <span class="text-danger">*</span></label>
                                            <input type="date" 
                                                   class="form-control" 
                                                   id="date_fin" 
                                                   name="date_fin" 
                                                   value="{{ date('Y-m-t') }}" 
                                                   required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="type" class="form-label">Type de rapport <span class="text-danger">*</span></label>
                                            <select class="form-select" id="type" name="type" required>
                                                <option value="tous">Tous les mouvements</option>
                                                <option value="entrees">Entrées seulement</option>
                                                <option value="sorties">Sorties seulement</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label class="form-label">&nbsp;</label>
                                            <button type="submit" class="btn btn-primary d-block w-100">
                                                <i class="fas fa-chart-line me-2"></i>Générer Rapport
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Filtres avancés -->
                                <div class="row">
                                    <div class="col-12">
                                        <div class="accordion" id="filtresAvances">
                                            <div class="accordion-item">
                                                <h2 class="accordion-header" id="headingFiltres">
                                                    <button class="accordion-button collapsed" type="button" 
                                                            data-bs-toggle="collapse" data-bs-target="#collapseFiltres" 
                                                            aria-expanded="false" aria-controls="collapseFiltres">
                                                        <i class="fas fa-filter me-2"></i>Filtres Avancés
                                                    </button>
                                                </h2>
                                                <div id="collapseFiltres" class="accordion-collapse collapse" 
                                                     aria-labelledby="headingFiltres" data-bs-parent="#filtresAvances">
                                                    <div class="accordion-body">
                                                        <div class="row">
                                                            <!-- Filtres pour les sorties -->
                                                            <div class="col-md-6">
                                                                <h6 class="text-primary mb-3">
                                                                    <i class="fas fa-arrow-up me-2"></i>Filtres Sorties
                                                                </h6>
                                                                
                                                                <div class="mb-3">
                                                                    <label for="type_depense" class="form-label">Type de dépense</label>
                                                                    <select class="form-select" id="type_depense" name="type_depense">
                                                                        <option value="">Tous les types</option>
                                                                        <option value="salaire_enseignant">Salaire Enseignant</option>
                                                                        <option value="salaire_personnel">Salaire Personnel</option>
                                                                        <option value="achat_materiel">Achat Matériel</option>
                                                                        <option value="maintenance">Maintenance</option>
                                                                        <option value="electricite">Électricité</option>
                                                                        <option value="eau">Eau</option>
                                                                        <option value="nourriture">Nourriture</option>
                                                                        <option value="transport">Transport</option>
                                                                        <option value="communication">Communication</option>
                                                                        <option value="formation">Formation</option>
                                                                        <option value="autre">Autre</option>
                                                                    </select>
                                                                </div>
                                                                
                                                                <div class="mb-3">
                                                                    <label for="statut_depense" class="form-label">Statut de la dépense</label>
                                                                    <select class="form-select" id="statut_depense" name="statut_depense">
                                                                        <option value="">Tous les statuts</option>
                                                                        <option value="en_attente">En attente</option>
                                                                        <option value="approuve">Approuvé</option>
                                                                        <option value="paye">Payé</option>
                                                                        <option value="annule">Annulé</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            
                                                            <!-- Filtres pour les entrées -->
                                                            <div class="col-md-6">
                                                                <h6 class="text-success mb-3">
                                                                    <i class="fas fa-arrow-down me-2"></i>Filtres Entrées
                                                                </h6>
                                                                
                                                                <div class="mb-3">
                                                                    <label for="source_entree" class="form-label">Source d'entrée</label>
                                                                    <select class="form-select" id="source_entree" name="source_entree">
                                                                        <option value="">Toutes les sources</option>
                                                                        <option value="frais_scolarite">Frais de Scolarité</option>
                                                                        <option value="don">Don</option>
                                                                        <option value="subvention">Subvention</option>
                                                                        <option value="autre">Autre</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <!-- Filtres par montant -->
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <h6 class="text-info mb-3">
                                                                    <i class="fas fa-money-bill-wave me-2"></i>Filtres par Montant
                                                                </h6>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="mb-3">
                                                                    <label for="montant_min" class="form-label">Montant minimum (GNF)</label>
                                                                    <input type="number" 
                                                                           class="form-control" 
                                                                           id="montant_min" 
                                                                           name="montant_min" 
                                                                           min="0" 
                                                                           step="0.01"
                                                                           placeholder="0">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="mb-3">
                                                                    <label for="montant_max" class="form-label">Montant maximum (GNF)</label>
                                                                    <input type="number" 
                                                                           class="form-control" 
                                                                           id="montant_max" 
                                                                           name="montant_max" 
                                                                           min="0" 
                                                                           step="0.01"
                                                                           placeholder="Aucune limite">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Graphiques et analyses -->
                    <div class="row">
                        <!-- Entrées par source -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-chart-pie me-2"></i>Entrées par Source
                                    </h5>
                                </div>
                                <div class="card-body">
                                    @if($entreesParSource->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Source</th>
                                                        <th class="text-end">Montant</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($entreesParSource as $source)
                                                        <tr>
                                                            <td>{{ $source->source }}</td>
                                                            <td class="text-end">
                                                                <strong class="text-success">
                                                                    {{ number_format($source->total, 0, ',', ' ') }} GNF
                                                                </strong>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <p class="text-muted text-center">Aucune donnée disponible</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Sorties par type -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-chart-pie me-2"></i>Sorties par Type
                                    </h5>
                                </div>
                                <div class="card-body">
                                    @if($sortiesParType->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Type</th>
                                                        <th class="text-end">Montant</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($sortiesParType as $type)
                                                        <tr>
                                                            <td>
                                                                @switch($type->type_depense)
                                                                    @case('salaire_enseignant')
                                                                        Salaire Enseignant
                                                                        @break
                                                                    @case('salaire_personnel')
                                                                        Salaire Personnel
                                                                        @break
                                                                    @case('achat_materiel')
                                                                        Achat Matériel
                                                                        @break
                                                                    @case('maintenance')
                                                                        Maintenance
                                                                        @break
                                                                    @case('electricite')
                                                                        Électricité
                                                                        @break
                                                                    @case('eau')
                                                                        Eau
                                                                        @break
                                                                    @case('nourriture')
                                                                        Nourriture
                                                                        @break
                                                                    @case('transport')
                                                                        Transport
                                                                        @break
                                                                    @case('communication')
                                                                        Communication
                                                                        @break
                                                                    @case('formation')
                                                                        Formation
                                                                        @break
                                                                    @default
                                                                        Autre
                                                                @endswitch
                                                            </td>
                                                            <td class="text-end">
                                                                <strong class="text-danger">
                                                                    {{ number_format($type->total, 0, ',', ' ') }} GNF
                                                                </strong>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <p class="text-muted text-center">Aucune donnée disponible</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Évolution mensuelle -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-chart-line me-2"></i>Évolution des 6 Derniers Mois
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6 class="text-success">Entrées par mois</h6>
                                            @if($entreesParMois->count() > 0)
                                                <div class="table-responsive">
                                                    <table class="table table-sm">
                                                        <thead>
                                                            <tr>
                                                                <th>Mois</th>
                                                                <th class="text-end">Montant</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($entreesParMois as $entree)
                                                                <tr>
                                                                    <td>{{ $entree->mois }}/{{ $entree->annee }}</td>
                                                                    <td class="text-end">
                                                                        <strong class="text-success">
                                                                            {{ number_format($entree->total, 0, ',', ' ') }} GNF
                                                                        </strong>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @else
                                                <p class="text-muted">Aucune donnée disponible</p>
                                            @endif
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="text-danger">Sorties par mois</h6>
                                            @if($sortiesParMois->count() > 0)
                                                <div class="table-responsive">
                                                    <table class="table table-sm">
                                                        <thead>
                                                            <tr>
                                                                <th>Mois</th>
                                                                <th class="text-end">Montant</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($sortiesParMois as $sortie)
                                                                <tr>
                                                                    <td>{{ $sortie->mois }}/{{ $sortie->annee }}</td>
                                                                    <td class="text-end">
                                                                        <strong class="text-danger">
                                                                            {{ number_format($sortie->total, 0, ',', ' ') }} GNF
                                                                        </strong>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @else
                                                <p class="text-muted">Aucune donnée disponible</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
