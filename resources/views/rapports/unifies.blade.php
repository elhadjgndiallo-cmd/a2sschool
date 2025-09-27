@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line text-primary me-2"></i>
                        Rapports Unifiés
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
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
                            <form method="GET" action="{{ route('rapports.unifies') }}">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="date_debut" class="form-label">Date de début</label>
                                            <input type="date" 
                                                   class="form-control" 
                                                   id="date_debut" 
                                                   name="date_debut" 
                                                   value="{{ $dateDebut }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="date_fin" class="form-label">Date de fin</label>
                                            <input type="date" 
                                                   class="form-control" 
                                                   id="date_fin" 
                                                   name="date_fin" 
                                                   value="{{ $dateFin }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">&nbsp;</label>
                                            <div>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-search me-1"></i>Filtrer
                                                </button>
                                                <a href="{{ route('rapports.unifies') }}" class="btn btn-secondary">
                                                    <i class="fas fa-refresh me-1"></i>Réinitialiser
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Section Rapports Financiers -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="fas fa-chart-line text-success me-2"></i>
                                Rapports Financiers
                                <button class="btn btn-sm btn-outline-success float-end" type="button" data-bs-toggle="collapse" data-bs-target="#rapportsFinanciers" aria-expanded="true">
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            </h5>
                        </div>
                        <div class="collapse show" id="rapportsFinanciers">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-success"><i class="fas fa-arrow-up"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Total Entrées</span>
                                                <span class="info-box-number">{{ number_format($totalEntrees, 0, ',', ' ') }} GNF</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-danger"><i class="fas fa-arrow-down"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Total Sorties</span>
                                                <span class="info-box-number">{{ number_format($totalSorties, 0, ',', ' ') }} GNF</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-info"><i class="fas fa-coins"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Paiements Scolaires</span>
                                                <span class="info-box-number">{{ number_format($totalPaiements, 0, ',', ' ') }} GNF</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-warning"><i class="fas fa-calculator"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Solde</span>
                                                <span class="info-box-number {{ $solde >= 0 ? 'text-success' : 'text-danger' }}">
                                                    {{ number_format($solde, 0, ',', ' ') }} GNF
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section Rapports Dépenses -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="fas fa-chart-bar text-warning me-2"></i>
                                Rapports Dépenses
                                <button class="btn btn-sm btn-outline-warning float-end" type="button" data-bs-toggle="collapse" data-bs-target="#rapportsDepenses" aria-expanded="false">
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            </h5>
                        </div>
                        <div class="collapse" id="rapportsDepenses">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6><i class="fas fa-list me-1"></i>Dépenses par Type</h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Type de Dépense</th>
                                                        <th class="text-end">Montant (GNF)</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($depensesParType as $depense)
                                                    <tr>
                                                        <td>{{ $depense->type_depense ?? 'Non spécifié' }}</td>
                                                        <td class="text-end">{{ number_format($depense->total, 0, ',', ' ') }}</td>
                                                    </tr>
                                                    @empty
                                                    <tr>
                                                        <td colspan="2" class="text-center text-muted">Aucune dépense trouvée</td>
                                                    </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h6><i class="fas fa-calendar me-1"></i>Dépenses par Mois</h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Période</th>
                                                        <th class="text-end">Montant (GNF)</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($depensesParMois as $depense)
                                                    <tr>
                                                        <td>{{ \Carbon\Carbon::create($depense->annee, $depense->mois)->format('M Y') }}</td>
                                                        <td class="text-end">{{ number_format($depense->total, 0, ',', ' ') }}</td>
                                                    </tr>
                                                    @empty
                                                    <tr>
                                                        <td colspan="2" class="text-center text-muted">Aucune dépense trouvée</td>
                                                    </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section Rapports Paiements -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="fas fa-chart-pie text-info me-2"></i>
                                Rapports Paiements
                                <button class="btn btn-sm btn-outline-info float-end" type="button" data-bs-toggle="collapse" data-bs-target="#rapportsPaiements" aria-expanded="false">
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            </h5>
                        </div>
                        <div class="collapse" id="rapportsPaiements">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6><i class="fas fa-school me-1"></i>Paiements par Classe</h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Classe</th>
                                                        <th class="text-end">Montant (GNF)</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($paiementsParClasse as $paiement)
                                                    <tr>
                                                        <td>{{ $paiement->classe }}</td>
                                                        <td class="text-end">{{ number_format($paiement->total, 0, ',', ' ') }}</td>
                                                    </tr>
                                                    @empty
                                                    <tr>
                                                        <td colspan="2" class="text-center text-muted">Aucun paiement trouvé</td>
                                                    </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h6><i class="fas fa-calendar me-1"></i>Paiements par Mois</h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Période</th>
                                                        <th class="text-end">Montant (GNF)</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($paiementsParMois as $paiement)
                                                    <tr>
                                                        <td>{{ \Carbon\Carbon::create($paiement->annee, $paiement->mois)->format('M Y') }}</td>
                                                        <td class="text-end">{{ number_format($paiement->total, 0, ',', ' ') }}</td>
                                                    </tr>
                                                    @empty
                                                    <tr>
                                                        <td colspan="2" class="text-center text-muted">Aucun paiement trouvé</td>
                                                    </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section Rapports Salaires -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="fas fa-coins text-primary me-2"></i>
                                Rapports Salaires
                                <button class="btn btn-sm btn-outline-primary float-end" type="button" data-bs-toggle="collapse" data-bs-target="#rapportsSalaires" aria-expanded="false">
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            </h5>
                        </div>
                        <div class="collapse" id="rapportsSalaires">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6><i class="fas fa-chalkboard-teacher me-1"></i>Salaires par Enseignant</h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Enseignant</th>
                                                        <th class="text-end">Montant (GNF)</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($salairesParEnseignant as $salaire)
                                                    <tr>
                                                        <td>{{ $salaire->nom }} {{ $salaire->prenom }}</td>
                                                        <td class="text-end">{{ number_format($salaire->total, 0, ',', ' ') }}</td>
                                                    </tr>
                                                    @empty
                                                    <tr>
                                                        <td colspan="2" class="text-center text-muted">Aucun salaire trouvé</td>
                                                    </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h6><i class="fas fa-calendar me-1"></i>Salaires par Mois</h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Période</th>
                                                        <th class="text-end">Montant (GNF)</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($salairesParMois as $salaire)
                                                    <tr>
                                                        <td>{{ \Carbon\Carbon::create($salaire->annee, $salaire->mois)->format('M Y') }}</td>
                                                        <td class="text-end">{{ number_format($salaire->total, 0, ',', ' ') }}</td>
                                                    </tr>
                                                    @empty
                                                    <tr>
                                                        <td colspan="2" class="text-center text-muted">Aucun salaire trouvé</td>
                                                    </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
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

@push('scripts')
<script>
$(document).ready(function() {
    // Animation pour les boutons de collapse
    $('[data-bs-toggle="collapse"]').on('click', function() {
        const icon = $(this).find('i');
        const target = $(this).attr('data-bs-target');
        const collapse = $(target);
        
        if (collapse.hasClass('show')) {
            icon.removeClass('fa-chevron-down').addClass('fa-chevron-right');
        } else {
            icon.removeClass('fa-chevron-right').addClass('fa-chevron-down');
        }
    });
    
    // Auto-scroll vers la première section ouverte
    $('.collapse.show').first().get(0)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
});
</script>
@endpush
