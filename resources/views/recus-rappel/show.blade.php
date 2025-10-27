@extends('layouts.app')

@section('title', 'Reçu de Rappel - ' . $recuRappel->numero_recu_rappel)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-bell mr-2"></i>
                        Reçu de Rappel - {{ $recuRappel->numero_recu_rappel }}
                    </h3>
                    <div>
                        <a href="{{ route('recus-rappel.pdf', $recuRappel) }}" class="btn btn-success">
                            <i class="fas fa-print mr-1"></i>
                            Imprimer
                        </a>
                        <a href="{{ route('recus-rappel.edit', $recuRappel) }}" class="btn btn-warning">
                            <i class="fas fa-edit mr-1"></i>
                            Modifier
                        </a>
                        <a href="{{ route('recus-rappel.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i>
                            Retour
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Informations générales -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Informations de l'élève</h5>
                                </div>
                                <div class="card-body">
                                    <p><strong>Nom complet:</strong> {{ $recuRappel->eleve->nom_complet }}</p>
                                    <p><strong>Numéro d'étudiant:</strong> {{ $recuRappel->eleve->numero_etudiant }}</p>
                                    <p><strong>Classe:</strong> {{ $recuRappel->eleve->classe->nom ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Informations du reçu</h5>
                                </div>
                                <div class="card-body">
                                    <p><strong>Numéro:</strong> {{ $recuRappel->numero_recu_rappel }}</p>
                                    <p><strong>Date de rappel:</strong> {{ \Carbon\Carbon::parse($recuRappel->date_rappel)->format('d/m/Y') }}</p>
                                    <p><strong>Date d'échéance:</strong> 
                                        <span class="{{ $recuRappel->isExpire() ? 'text-danger' : 'text-success' }}">
                                            {{ \Carbon\Carbon::parse($recuRappel->date_echeance)->format('d/m/Y') }}
                                        </span>
                                    </p>
                                    <p><strong>Statut:</strong> 
                                        @switch($recuRappel->statut)
                                            @case('actif')
                                                <span class="badge bg-success">Actif</span>
                                                @break
                                            @case('expire')
                                                <span class="badge bg-danger">Expiré</span>
                                                @break
                                            @case('paye')
                                                <span class="badge bg-primary">Payé</span>
                                                @break
                                        @endswitch
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Détails financiers -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Détails financiers</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="text-center p-3 bg-light rounded">
                                                <h6 class="text-muted">Montant Total Dû</h6>
                                                <h4 class="text-primary">{{ number_format($recuRappel->montant_total_du, 0, ',', ' ') }} GNF</h4>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-center p-3 bg-light rounded">
                                                <h6 class="text-muted">Montant Payé</h6>
                                                <h4 class="text-success">{{ number_format($recuRappel->montant_paye, 0, ',', ' ') }} GNF</h4>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-center p-3 bg-light rounded">
                                                <h6 class="text-muted">Montant Restant</h6>
                                                <h4 class="text-warning">{{ number_format($recuRappel->montant_restant, 0, ',', ' ') }} GNF</h4>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-center p-3 bg-light rounded">
                                                <h6 class="text-muted">Montant à Payer</h6>
                                                @if($recuRappel->montant_a_payer)
                                                    <h4 class="text-info">{{ number_format($recuRappel->montant_a_payer, 0, ',', ' ') }} GNF</h4>
                                                @else
                                                    <h4 class="text-muted">Non défini</h4>
                                                    <small class="text-muted">À remplir manuellement</small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Détails des frais -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Détails des frais de scolarité</h5>
                                </div>
                                <div class="card-body">
                                    <p><strong>Libellé:</strong> {{ $recuRappel->fraisScolarite->libelle }}</p>
                                    <p><strong>Type:</strong> {{ ucfirst($recuRappel->fraisScolarite->type_frais ?? 'Scolarité') }}</p>
                                    @if($recuRappel->fraisScolarite->description)
                                        <p><strong>Description:</strong> {{ $recuRappel->fraisScolarite->description }}</p>
                                    @endif
                                    <p><strong>Date d'échéance originale:</strong> {{ \Carbon\Carbon::parse($recuRappel->fraisScolarite->date_echeance)->format('d/m/Y') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Observations -->
                    @if($recuRappel->observations)
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">Observations</h5>
                                    </div>
                                    <div class="card-body">
                                        <p>{{ $recuRappel->observations }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Informations de génération -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Informations de génération</h5>
                                </div>
                                <div class="card-body">
                                    <p><strong>Généré par:</strong> {{ $recuRappel->generePar->nom }} {{ $recuRappel->generePar->prenom }}</p>
                                    <p><strong>Date de génération:</strong> {{ \Carbon\Carbon::parse($recuRappel->created_at)->format('d/m/Y à H:i') }}</p>
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
