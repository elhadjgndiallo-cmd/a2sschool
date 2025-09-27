@extends('layouts.app')

@section('title', 'Échéances à Venir')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center pt-3 pb-2 mb-3 border-bottom gap-2">
    <div>
        <h1 class="h2 mb-0">
            <i class="fas fa-calendar-alt text-warning me-2"></i>
            Échéances à Venir
        </h1>
        <p class="text-muted mb-0">Consultez les prochaines échéances de paiement</p>
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

<!-- Statistiques rapides -->
<div class="row mb-4 g-3">
    <div class="col-12 col-sm-6 col-md-3">
        <div class="card bg-warning text-white h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-calendar-check fa-2x"></i>
                </div>
                <div class="flex-grow-1 ms-3">
                    <h3 class="mb-0">{{ $echeances->count() }}</h3>
                    <p class="mb-0">Échéances</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3">
        <div class="card bg-info text-white h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-money-bill-wave fa-2x"></i>
                </div>
                <div class="flex-grow-1 ms-3">
                    <h3 class="mb-0">{{ number_format($echeances->sum('montant_tranche'), 0, ',', ' ') }}</h3>
                    <p class="mb-0">Montant total (GNF)</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3">
        <div class="card bg-danger text-white h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                </div>
                <div class="flex-grow-1 ms-3">
                    <h3 class="mb-0">{{ $echeances->where('date_echeance', '<=', now()->addDays(7))->count() }}</h3>
                    <p class="mb-0">Dans 7 jours</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3">
        <div class="card bg-success text-white h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-child fa-2x"></i>
                </div>
                <div class="flex-grow-1 ms-3">
                    <h3 class="mb-0">{{ $echeances->groupBy('fraisScolarite.eleve_id')->count() }}</h3>
                    <p class="mb-0">Enfants concernés</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Échéances par mois -->
@if($echeancesParMois->count() > 0)
    @foreach($echeancesParMois as $mois => $echeancesMois)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-calendar me-2"></i>
                            {{ \Carbon\Carbon::createFromFormat('Y-m', $mois)->locale('fr')->isoFormat('MMMM YYYY') }}
                            <span class="badge bg-primary ms-2">{{ $echeancesMois->count() }} échéance(s)</span>
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date d'échéance</th>
                                        <th>Enfant</th>
                                        <th>Classe</th>
                                        <th>Type de frais</th>
                                        <th>N° Tranche</th>
                                        <th>Montant</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($echeancesMois as $echeance)
                                        <tr>
                                            <td>
                                                <span class="badge bg-{{ $echeance->date_echeance <= now() ? 'danger' : ($echeance->date_echeance <= now()->addDays(7) ? 'warning' : 'light text-dark') }}">
                                                    {{ $echeance->date_echeance->format('d/m/Y') }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2">
                                                        <i class="fas fa-user text-primary"></i>
                                                    </div>
                                                    <div>
                                                        <strong>{{ $echeance->fraisScolarite->eleve->utilisateur->nom }}</strong><br>
                                                        <small class="text-muted">{{ $echeance->fraisScolarite->eleve->utilisateur->prenom }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">
                                                    {{ $echeance->fraisScolarite->eleve->classe->nom ?? 'N/A' }}
                                                </span>
                                            </td>
                                            <td>
                                                <strong>{{ $echeance->fraisScolarite->libelle }}</strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    Tranche {{ $echeance->numero_tranche }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="fw-bold text-success">
                                                    {{ number_format($echeance->montant_tranche, 0, ',', ' ') }} GNF
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $echeance->statut == 'paye' ? 'success' : ($echeance->statut == 'en_attente' ? 'warning' : 'danger') }}">
                                                    {{ ucfirst(str_replace('_', ' ', $echeance->statut)) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('parent.paiements.show', $echeance->fraisScolarite) }}" 
                                                       class="btn btn-outline-primary" title="Voir détails">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if($echeance->statut == 'en_attente')
                                                        <button class="btn btn-outline-success" title="Payer maintenant" disabled>
                                                            <i class="fas fa-credit-card"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
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
                    <i class="fas fa-calendar-check fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Aucune échéance à venir</h5>
                    <p class="text-muted">Toutes les échéances sont à jour ou aucune échéance n'est programmée.</p>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection
