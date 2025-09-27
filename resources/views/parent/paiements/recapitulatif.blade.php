@extends('layouts.app')

@section('title', 'Récapitulatif des Paiements')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center pt-3 pb-2 mb-3 border-bottom gap-2">
    <div>
        <h1 class="h2 mb-0">
            <i class="fas fa-chart-pie text-info me-2"></i>
            Récapitulatif des Paiements
        </h1>
        <p class="text-muted mb-0">Vue d'ensemble des paiements par enfant</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('parent.paiements.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                <span class="d-none d-sm-inline">Retour</span>
            </a>
        </div>
    </div>
</div>

<!-- Filtre année scolaire -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('parent.recapitulatif') }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-md-4">
                            <label for="annee_scolaire" class="form-label">Année scolaire</label>
                            <select name="annee_scolaire" id="annee_scolaire" class="form-select">
                                <option value="{{ now()->year }}-{{ now()->year + 1 }}" {{ $anneeScolaire == now()->year . '-' . (now()->year + 1) ? 'selected' : '' }}>
                                    {{ now()->year }}-{{ now()->year + 1 }}
                                </option>
                                <option value="{{ now()->year - 1 }}-{{ now()->year }}" {{ $anneeScolaire == (now()->year - 1) . '-' . now()->year ? 'selected' : '' }}>
                                    {{ now()->year - 1 }}-{{ now()->year }}
                                </option>
                            </select>
                        </div>
                        <div class="col-12 col-md-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter me-1"></i>
                                Filtrer
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Récapitulatif par enfant -->
@if(count($recapitulatif) > 0)
    @foreach($recapitulatif as $enfantId => $data)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3">
                                <i class="fas fa-user text-primary"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">
                                    {{ $data['enfant']->utilisateur->nom }} {{ $data['enfant']->utilisateur->prenom }}
                                </h6>
                                <small class="text-muted">
                                    Classe: {{ $data['enfant']->classe->nom ?? 'N/A' }}
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Statistiques de l'enfant -->
                        <div class="row mb-4 g-3">
                            <div class="col-12 col-sm-6 col-md-3">
                                <div class="card bg-primary text-white h-100">
                                    <div class="card-body d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-receipt fa-2x"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h3 class="mb-0">{{ $data['total_frais'] }}</h3>
                                            <p class="mb-0">Frais total (GNF)</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-md-3">
                                <div class="card bg-success text-white h-100">
                                    <div class="card-body d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-check-circle fa-2x"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h3 class="mb-0">{{ number_format($data['total_paye'], 0, ',', ' ') }}</h3>
                                            <p class="mb-0">Payé (GNF)</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-md-3">
                                <div class="card bg-warning text-white h-100">
                                    <div class="card-body d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-clock fa-2x"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h3 class="mb-0">{{ number_format($data['total_restant'], 0, ',', ' ') }}</h3>
                                            <p class="mb-0">Restant (GNF)</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-md-3">
                                <div class="card bg-info text-white h-100">
                                    <div class="card-body d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-percentage fa-2x"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h3 class="mb-0">{{ $data['taux_paiement'] }}%</h3>
                                            <p class="mb-0">Taux de paiement</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Barre de progression -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold">Progression des paiements</span>
                                <span class="text-muted">{{ $data['taux_paiement'] }}%</span>
                            </div>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-success" role="progressbar" 
                                     style="width: {{ $data['taux_paiement'] }}%" 
                                     aria-valuenow="{{ $data['taux_paiement'] }}" 
                                     aria-valuemin="0" aria-valuemax="100">
                                    {{ $data['taux_paiement'] }}%
                                </div>
                            </div>
                        </div>

                        <!-- Détail des frais -->
                        @if($data['frais']->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Type de frais</th>
                                            <th>Montant total</th>
                                            <th>Montant payé</th>
                                            <th>Montant restant</th>
                                            <th>Statut</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($data['frais'] as $frais)
                                            <tr>
                                                <td>
                                                    <strong>{{ $frais->libelle }}</strong>
                                                    @if($frais->paiement_par_tranches)
                                                        <br><small class="text-muted">Paiement par tranches</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="fw-bold">
                                                        {{ number_format($frais->montant, 0, ',', ' ') }} GNF
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="text-success fw-bold">
                                                        {{ number_format($frais->paiements->sum('montant_paye'), 0, ',', ' ') }} GNF
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="text-warning fw-bold">
                                                        {{ number_format($frais->montant_restant, 0, ',', ' ') }} GNF
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $frais->statut == 'paye' ? 'success' : ($frais->statut == 'en_attente' ? 'warning' : 'danger') }}">
                                                        {{ ucfirst(str_replace('_', ' ', $frais->statut)) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="{{ route('parent.paiements.show', $frais) }}" 
                                                           class="btn btn-outline-primary" title="Voir détails">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-3">
                                <i class="fas fa-receipt fa-2x text-muted mb-2"></i>
                                <p class="text-muted mb-0">Aucun frais de scolarité pour cette année scolaire.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@else
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-chart-pie fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Aucun récapitulatif disponible</h5>
                    <p class="text-muted">Aucun frais de scolarité trouvé pour cette année scolaire.</p>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection
