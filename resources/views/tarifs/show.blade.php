@extends('layouts.app')

@section('title', 'Détails du Tarif')

@section('content')
<div class="container-fluid">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-eye me-2"></i>Détails du Tarif
            </h1>
            <p class="text-muted">{{ $tarif->classe->nom }} - {{ $tarif->annee_scolaire }}</p>
        </div>
        <div>
            <a href="{{ route('tarifs.edit', $tarif) }}" class="btn btn-warning">
                <i class="fas fa-edit me-2"></i>Modifier
            </a>
            <a href="{{ route('tarifs.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Retour
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Informations principales -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Informations Générales
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Classe</label>
                                <div class="fw-bold">{{ $tarif->classe->nom }}</div>
                                @if($tarif->classe->niveau)
                                <small class="text-muted">{{ $tarif->classe->niveau }}</small>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Année Scolaire</label>
                                <div>
                                    <span class="badge bg-info fs-6">{{ $tarif->annee_scolaire }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Statut</label>
                                <div>
                                    @if($tarif->actif)
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle me-1"></i>Actif
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-pause-circle me-1"></i>Inactif
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Dernière modification</label>
                                <div class="fw-bold">{{ $tarif->updated_at->format('d/m/Y à H:i') }}</div>
                            </div>
                        </div>
                    </div>

                    @if($tarif->description)
                    <div class="mb-3">
                        <label class="form-label text-muted">Description</label>
                        <div class="alert alert-light">{{ $tarif->description }}</div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Détail des frais -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-money-bill-wave me-2"></i>Détail des Frais
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <td class="fw-bold">
                                        <i class="fas fa-file-signature me-2 text-primary"></i>Frais d'Inscription
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-primary">{{ number_format($tarif->frais_inscription, 0, ',', ' ') }} GNF</span>
                                    </td>
                                </tr>
                                @if($tarif->frais_reinscription > 0)
                                <tr>
                                    <td class="fw-bold">
                                        <i class="fas fa-user-check me-2 text-warning"></i>Frais de Réinscription
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-warning">{{ number_format($tarif->frais_reinscription, 0, ',', ' ') }} GNF</span>
                                    </td>
                                </tr>
                                @endif
                                <tr>
                                    <td class="fw-bold">
                                        <i class="fas fa-graduation-cap me-2 text-success"></i>Scolarité Mensuelle
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-success">{{ number_format($tarif->frais_scolarite_mensuel, 0, ',', ' ') }} GNF</span>
                                    </td>
                                </tr>
                                @if($tarif->frais_cantine_mensuel > 0)
                                <tr>
                                    <td class="fw-bold">
                                        <i class="fas fa-utensils me-2 text-warning"></i>Cantine Mensuelle
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-warning">{{ number_format($tarif->frais_cantine_mensuel, 0, ',', ' ') }} GNF</span>
                                    </td>
                                </tr>
                                @endif
                                @if($tarif->frais_transport_mensuel > 0)
                                <tr>
                                    <td class="fw-bold">
                                        <i class="fas fa-bus me-2 text-info"></i>Transport Mensuel
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-info">{{ number_format($tarif->frais_transport_mensuel, 0, ',', ' ') }} GNF</span>
                                    </td>
                                </tr>
                                @endif
                                @if($tarif->frais_uniforme > 0)
                                <tr>
                                    <td class="fw-bold">
                                        <i class="fas fa-tshirt me-2 text-secondary"></i>Uniforme
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-secondary">{{ number_format($tarif->frais_uniforme, 0, ',', ' ') }} GNF</span>
                                    </td>
                                </tr>
                                @endif
                                @if($tarif->frais_livres > 0)
                                <tr>
                                    <td class="fw-bold">
                                        <i class="fas fa-book me-2 text-dark"></i>Livres
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-dark">{{ number_format($tarif->frais_livres, 0, ',', ' ') }} GNF</span>
                                    </td>
                                </tr>
                                @endif
                                @if($tarif->frais_autres > 0)
                                <tr>
                                    <td class="fw-bold">
                                        <i class="fas fa-ellipsis-h me-2 text-muted"></i>Autres Frais
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-light text-dark">{{ number_format($tarif->frais_autres, 0, ',', ' ') }} GNF</span>
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Résumé des coûts -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-calculator me-2"></i>Résumé des Coûts
                    </h5>
                </div>
                <div class="card-body">
                    @php
                        $totalMensuel = $tarif->frais_scolarite_mensuel + 
                                       $tarif->frais_cantine_mensuel + 
                                       $tarif->frais_transport_mensuel;
                        $totalAnnuel = $tarif->frais_inscription + 
                                      ($totalMensuel * $tarif->nombre_tranches) + 
                                      $tarif->frais_uniforme + 
                                      $tarif->frais_livres + 
                                      $tarif->frais_autres;
                    @endphp

                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Frais d'inscription:</span>
                            <strong>{{ number_format($tarif->frais_inscription, 0, ',', ' ') }} GNF</strong>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Coût mensuel:</span>
                            <strong class="text-primary">{{ number_format($totalMensuel, 0, ',', ' ') }} GNF</strong>
                        </div>
                        <small class="text-muted">
                            Scolarité + Cantine + Transport
                        </small>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Coût annuel total:</span>
                            <strong class="text-success fs-5">{{ number_format($totalAnnuel, 0, ',', ' ') }} GNF</strong>
                        </div>
                        <small class="text-muted">
                            Inscription + {{ $tarif->nombre_tranches }} mois + Uniforme + Livres + Autres
                        </small>
                    </div>

                    @if($tarif->frais_uniforme > 0 || $tarif->frais_livres > 0 || $tarif->frais_autres > 0)
                    <div class="alert alert-info">
                        <small>
                            <i class="fas fa-info-circle me-1"></i>
                            Les frais d'uniforme, livres et autres sont généralement payés une seule fois par année.
                        </small>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Configuration de paiement -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-credit-card me-2"></i>Configuration Mensuelle
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted">Paiement mensuel</label>
                        <div>
                            @if($tarif->paiement_par_tranches)
                                <span class="badge bg-success">
                                    <i class="fas fa-check-circle me-1"></i>Activé
                                </span>
                            @else
                                <span class="badge bg-secondary">
                                    <i class="fas fa-times-circle me-1"></i>Désactivé
                                </span>
                            @endif
                        </div>
                    </div>

                    @if($tarif->paiement_par_tranches)
                    <div class="mb-3">
                        <label class="form-label text-muted">Nombre des mois</label>
                        <div class="fw-bold">{{ $tarif->nombre_tranches }} mois</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">Fréquence</label>
                        <div class="fw-bold text-capitalize">{{ $tarif->periode_tranche }}</div>
                    </div>

                    @if($tarif->nombre_tranches > 1)
                    <div class="alert alert-light">
                        <small>
                            <i class="fas fa-calculator me-1"></i>
                            Montant par mois: <strong>{{ number_format($totalMensuel / $tarif->nombre_tranches, 0, ',', ' ') }} GNF</strong>
                        </small>
                    </div>
                    @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
