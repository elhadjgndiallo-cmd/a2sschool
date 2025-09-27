@extends('layouts.app')

@section('title', 'Détails de la Sortie')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-file-invoice mr-2"></i>
                        Détails de la Sortie
                    </h3>
                    <div>
                        @if($depense->statut === 'en_attente')
                            <form action="{{ route('depenses.approuver', $depense) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check mr-1"></i>
                                    Approuver
                                </button>
                            </form>
                        @endif
                        @if($depense->statut === 'approuve')
                            <a href="{{ route('depenses.payer', $depense) }}" class="btn btn-primary">
                                <i class="fas fa-money-bill-wave mr-1"></i>
                                Payer
                            </a>
                        @endif
                        <a href="{{ route('depenses.edit', $depense) }}" class="btn btn-warning ml-2">
                            <i class="fas fa-edit mr-1"></i>
                            Modifier
                        </a>
                        <a href="{{ route('depenses.index') }}" class="btn btn-secondary ml-2">
                            <i class="fas fa-arrow-left mr-1"></i>
                            Retour
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    @endif

                    <!-- Informations générales -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Informations Générales</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>Libellé:</strong></td>
                                            <td>{{ $depense->libelle }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Type:</strong></td>
                                            <td>
                                                <span class="badge badge-info">
                                                    {{ $depense->type_depense_libelle }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Montant:</strong></td>
                                            <td>
                                                <strong class="text-danger">
                                                    {{ number_format($depense->montant, 0, ',', ' ') }} GNF
                                                </strong>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Date:</strong></td>
                                            <td>{{ $depense->date_depense->format('d/m/Y') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Statut:</strong></td>
                                            <td>
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
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Détails Supplémentaires</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>Bénéficiaire:</strong></td>
                                            <td>{{ $depense->beneficiaire ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Référence Facture:</strong></td>
                                            <td>{{ $depense->reference_facture ?? '-' }}</td>
                                        </tr>
                                        @if($depense->mode_paiement)
                                            <tr>
                                                <td><strong>Mode de Paiement:</strong></td>
                                                <td>
                                                    <span class="badge badge-secondary">
                                                        {{ ucfirst($depense->mode_paiement) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endif
                                        @if($depense->reference_paiement)
                                            <tr>
                                                <td><strong>Référence Paiement:</strong></td>
                                                <td><code>{{ $depense->reference_paiement }}</code></td>
                                            </tr>
                                        @endif
                                        @if($depense->date_paiement)
                                            <tr>
                                                <td><strong>Date de Paiement:</strong></td>
                                                <td>{{ $depense->date_paiement->format('d/m/Y') }}</td>
                                            </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Description et Observations -->
                    @if($depense->description || $depense->observations)
                        <div class="row mb-4">
                            @if($depense->description)
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="mb-0">Description</h5>
                                        </div>
                                        <div class="card-body">
                                            <p>{{ $depense->description }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            
                            @if($depense->observations)
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="mb-0">Observations</h5>
                                        </div>
                                        <div class="card-body">
                                            <p>{{ $depense->observations }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Historique des actions -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-history mr-2"></i>
                                Historique des Actions
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="timeline">
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-primary"></div>
                                    <div class="timeline-content">
                                        <h6>Sortie créée</h6>
                                        <p class="text-muted mb-0">
                                            Créée le {{ $depense->created_at->format('d/m/Y à H:i') }}
                                        </p>
                                    </div>
                                </div>

                                @if($depense->date_approbation)
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-info"></div>
                                        <div class="timeline-content">
                                            <h6>Sortie approuvée</h6>
                                            <p class="text-muted mb-0">
                                                Approuvée le {{ $depense->date_approbation->format('d/m/Y') }}
                                                @if($depense->approuvePar)
                                                    par {{ $depense->approuvePar->nom }}
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                @endif

                                @if($depense->date_paiement)
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-success"></div>
                                        <div class="timeline-content">
                                            <h6>Sortie payée</h6>
                                            <p class="text-muted mb-0">
                                                Payée le {{ $depense->date_paiement->format('d/m/Y') }}
                                                @if($depense->payePar)
                                                    par {{ $depense->payePar->nom }}
                                                @endif
                                                @if($depense->mode_paiement)
                                                    ({{ ucfirst($depense->mode_paiement) }})
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -35px;
    top: 5px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    border-left: 3px solid #dee2e6;
}

.timeline-content h6 {
    margin-bottom: 5px;
    color: #495057;
}
</style>
@endsection
